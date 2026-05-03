<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\SeatHold;
use App\Models\Show;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeatHoldService
{
    private const ACTIVE_HOLD_STATUSES = ['HELD', 'CONFIRMED'];
    private const RESERVED_STATUSES = ['RESERVED', 'ISSUED'];

    public function ownerToken(?string $sessionToken = null): string
    {
        if ($sessionToken) {
            return $sessionToken;
        }

        if (session()->has('seat_hold_owner_token')) {
            return (string) session('seat_hold_owner_token');
        }

        $token = Str::random(64);
        session(['seat_hold_owner_token' => $token]);

        return $token;
    }

    public function releaseExpiredHolds(?Show $show = null): void
    {
        SeatHold::query()
            ->when($show, fn ($query) => $query->where('show_id', $show->id))
            ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
            ->where('expires_at', '<=', now())
            ->update(['status' => 'EXPIRED']);
    }

    public function seatPayload(Show $show, ?string $ownerToken = null): array
    {
        $this->releaseExpiredHolds($show);

        $ownerToken = $this->ownerToken($ownerToken);
        $auditoriumId = (int) $show->auditorium_id;

        $seats = Seat::query()
            ->with('seatType:id,code,name')
            ->where('auditorium_id', $auditoriumId)
            ->where('is_active', 1)
            ->orderBy('row_label')
            ->orderBy('col_number')
            ->get(['id', 'seat_type_id', 'seat_code', 'row_label', 'col_number']);

        $ticketStates = DB::table('booking_tickets')
            ->select(['seat_id', 'status'])
            ->where('show_id', $show->id)
            ->whereIn('status', self::RESERVED_STATUSES)
            ->get()
            ->keyBy('seat_id');

        $holdStates = SeatHold::query()
            ->select(['seat_id', 'owner_token', 'status', 'expires_at'])
            ->where('show_id', $show->id)
            ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
            ->where('expires_at', '>', now())
            ->get()
            ->keyBy('seat_id');

        $blockedSeatIds = SeatBlock::query()
            ->where('auditorium_id', $auditoriumId)
            ->where('start_at', '<', $show->end_time)
            ->where('end_at', '>', $show->start_time)
            ->pluck('seat_id')
            ->map(fn ($seatId) => (int) $seatId)
            ->all();

        $blockedMap = array_fill_keys($blockedSeatIds, true);

        return $seats->map(function (Seat $seat) use ($ticketStates, $holdStates, $blockedMap, $ownerToken) {
            $ticketState = $ticketStates->get($seat->id);
            $holdState = $holdStates->get($seat->id);
            $state = 'AVAILABLE';
            $stateLabel = 'Ghế trống';

            if (isset($blockedMap[(int) $seat->id])) {
                $state = 'BLOCKED';
                $stateLabel = 'Ghế đang khóa / bảo trì';
            } elseif ($ticketState && $ticketState->status === 'ISSUED') {
                $state = 'BOOKED';
                $stateLabel = 'Ghế đã thanh toán';
            } elseif ($ticketState && $ticketState->status === 'RESERVED') {
                $state = 'RESERVED';
                $stateLabel = 'Ghế đang chờ thanh toán';
            } elseif ($holdState) {
                if ((string) $holdState->owner_token === $ownerToken) {
                    $state = 'HOLD_SELF';
                    $stateLabel = 'Bạn đang giữ ghế này';
                } else {
                    $state = 'HOLD_OTHER';
                    $stateLabel = 'Ghế đang có người chọn';
                }
            }

            return [
                'id' => (int) $seat->id,
                'seat_type_id' => (int) $seat->seat_type_id,
                'seat_code' => $seat->seat_code,
                'row_label' => $seat->row_label,
                'col_number' => (int) $seat->col_number,
                'seat_type_code' => strtoupper((string) ($seat->seatType?->code ?? 'REGULAR')),
                'seat_type_name' => $seat->seatType?->name ?? 'Ghế thường',
                'state' => $state,
                'state_label' => $stateLabel,
                'available' => in_array($state, ['AVAILABLE', 'HOLD_SELF'], true),
                'selected_by_self' => $state === 'HOLD_SELF',
            ];
        })->values()->all();
    }

    public function syncSelectedSeats(Show $show, array $seatIds, ?Customer $customer, string $ownerToken): array
    {
        $ownerToken = $this->ownerToken($ownerToken);
        $seatIds = collect($seatIds)
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values();

        $maxSeats = max(1, (int) config('cinema_booking.max_seats_per_booking', 10));
        if ($seatIds->count() > $maxSeats) {
            abort(422, 'Bạn chỉ có thể giữ tối đa ' . $maxSeats . ' ghế cùng lúc.');
        }

        DB::transaction(function () use ($show, $seatIds, $customer, $ownerToken) {
            $this->releaseExpiredHolds($show);

            $fixedDeadline = $this->currentHoldDeadline($show, $ownerToken);
            if ($fixedDeadline && now()->gte($fixedDeadline)) {
                $this->releaseOwnerSeats($show, $ownerToken, true);
                $fixedDeadline = null;
            }

            if (! $fixedDeadline && $seatIds->isNotEmpty()) {
                $fixedDeadline = now()->addMinutes(max(1, (int) config('cinema_booking.seat_hold_minutes', 2)));
                $this->setOwnerHoldExpiresAt($show, $ownerToken, $fixedDeadline);
            }

            $validSeatIds = Seat::query()
                ->where('auditorium_id', $show->auditorium_id)
                ->where('is_active', 1)
                ->pluck('id')
                ->map(fn ($seatId) => (int) $seatId)
                ->all();
            $validSeatIdMap = array_fill_keys($validSeatIds, true);

            foreach ($seatIds as $seatId) {
                if (! isset($validSeatIdMap[$seatId])) {
                    abort(422, 'Có ghế không hợp lệ hoặc không thuộc phòng chiếu này.');
                }
            }

            $blockedSeatIds = SeatBlock::query()
                ->where('auditorium_id', $show->auditorium_id)
                ->where('start_at', '<', $show->end_time)
                ->where('end_at', '>', $show->start_time)
                ->pluck('seat_id')
                ->map(fn ($seatId) => (int) $seatId)
                ->all();
            $blockedMap = array_fill_keys($blockedSeatIds, true);

            foreach ($seatIds as $seatId) {
                if (isset($blockedMap[$seatId])) {
                    abort(422, 'Ghế bạn chọn đang bị khóa hoặc bảo trì.');
                }
            }

            $takenSeatIds = DB::table('booking_tickets')
                ->where('show_id', $show->id)
                ->whereIn('status', self::RESERVED_STATUSES)
                ->pluck('seat_id')
                ->map(fn ($seatId) => (int) $seatId)
                ->all();
            $takenMap = array_fill_keys($takenSeatIds, true);

            foreach ($seatIds as $seatId) {
                if (isset($takenMap[$seatId])) {
                    abort(422, 'Một hoặc nhiều ghế bạn chọn đang nằm ở bước thanh toán. Vui lòng chọn ghế khác hoặc quay lại sửa booking hiện tại.');
                }
            }

            $activeHolds = SeatHold::query()
                ->where('show_id', $show->id)
                ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
                ->where('expires_at', '>', now())
                ->lockForUpdate()
                ->get();

            $conflictSeatIds = $activeHolds
                ->filter(fn (SeatHold $hold) => $hold->owner_token !== $ownerToken)
                ->pluck('seat_id')
                ->map(fn ($seatId) => (int) $seatId)
                ->all();
            $conflictMap = array_fill_keys($conflictSeatIds, true);

            foreach ($seatIds as $seatId) {
                if (isset($conflictMap[$seatId])) {
                    abort(422, 'Có ghế vừa được người khác chọn trước bạn. Danh sách ghế đã được làm mới.');
                }
            }

            $currentOwnerHolds = $activeHolds
                ->filter(fn (SeatHold $hold) => $hold->owner_token === $ownerToken)
                ->keyBy('seat_id');

            $seatIdsToRelease = $currentOwnerHolds->keys()->diff($seatIds);
            if ($seatIdsToRelease->isNotEmpty()) {
                SeatHold::query()
                    ->where('show_id', $show->id)
                    ->where('owner_token', $ownerToken)
                    ->whereIn('seat_id', $seatIdsToRelease->all())
                    ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
                    ->update(['status' => 'CANCELLED']);
            }

            $holdExpiresAt = $fixedDeadline ?? now()->addMinutes(max(1, (int) config('cinema_booking.seat_hold_minutes', 2)));

            foreach ($seatIds as $seatId) {
                $existing = $currentOwnerHolds->get($seatId);
                if ($existing) {
                    $existing->update([
                        'status' => 'HELD',
                        'expires_at' => $holdExpiresAt,
                        'customer_id' => $customer?->id,
                    ]);
                    continue;
                }

                try {
                    SeatHold::create([
                        'show_id' => $show->id,
                        'seat_id' => $seatId,
                        'customer_id' => $customer?->id,
                        'hold_token' => Str::random(64),
                        'owner_token' => $ownerToken,
                        'status' => 'HELD',
                        'expires_at' => $holdExpiresAt,
                    ]);
                } catch (QueryException $exception) {
                    abort(422, 'Một hoặc nhiều ghế bạn chọn vừa được giữ bởi khách khác. Vui lòng thử lại.');
                }
            }
        }, 3);

        return $this->seatPayload($show, $ownerToken);
    }

    public function ownerHoldExpiresAt(Show $show, string $ownerToken): ?string
    {
        $deadline = $this->currentHoldDeadline($show, $ownerToken);

        return $deadline?->toIso8601String();
    }

    public function currentHoldDeadline(Show $show, string $ownerToken): ?Carbon
    {
        if ($ownerToken === '') {
            return null;
        }

        $cached = Cache::get($this->holdWindowCacheKey($show, $ownerToken));
        if ($cached) {
            $deadline = $cached instanceof Carbon ? $cached : Carbon::parse($cached);
            if ($deadline->isFuture()) {
                return $deadline;
            }

            Cache::forget($this->holdWindowCacheKey($show, $ownerToken));
        }

        $expiresAt = SeatHold::query()
            ->where('show_id', $show->id)
            ->where('owner_token', $ownerToken)
            ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
            ->where('expires_at', '>', now())
            ->orderBy('expires_at')
            ->value('expires_at');

        if (! $expiresAt) {
            return null;
        }

        $deadline = Carbon::parse($expiresAt);
        $this->setOwnerHoldExpiresAt($show, $ownerToken, $deadline);

        return $deadline;
    }

    public function setOwnerHoldExpiresAt(Show $show, string $ownerToken, Carbon|string|null $expiresAt): ?Carbon
    {
        if ($ownerToken === '' || ! $expiresAt) {
            return null;
        }

        $deadline = $expiresAt instanceof Carbon ? $expiresAt->copy() : Carbon::parse($expiresAt);
        if ($deadline->isPast()) {
            Cache::forget($this->holdWindowCacheKey($show, $ownerToken));

            return null;
        }

        Cache::put(
            $this->holdWindowCacheKey($show, $ownerToken),
            $deadline->toIso8601String(),
            $deadline
        );

        return $deadline;
    }

    public function restorePendingBookingForEditing(Booking $booking, string $ownerToken): Booking
    {
        if ((string) $booking->status !== 'PENDING') {
            return $booking;
        }

        $booking->loadMissing(['show', 'tickets']);
        if (! $booking->show || ! $booking->expires_at || now()->gte($booking->expires_at)) {
            return $booking;
        }

        DB::transaction(function () use ($booking, $ownerToken) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['show', 'tickets'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((string) $lockedBooking->status !== 'PENDING' || ! $lockedBooking->show || ! $lockedBooking->expires_at || now()->gte($lockedBooking->expires_at)) {
                return;
            }

            $deadline = $this->setOwnerHoldExpiresAt($lockedBooking->show, $ownerToken, $lockedBooking->expires_at)
                ?? now()->addMinutes(max(1, (int) config('cinema_booking.seat_hold_minutes', 2)));

            foreach ($lockedBooking->tickets->where('status', 'RESERVED') as $ticket) {
                SeatHold::query()->updateOrCreate(
                    [
                        'show_id' => $lockedBooking->show_id,
                        'seat_id' => $ticket->seat_id,
                        'owner_token' => $ownerToken,
                    ],
                    [
                        'customer_id' => $lockedBooking->customer_id,
                        'hold_token' => Str::random(64),
                        'status' => 'HELD',
                        'expires_at' => $deadline,
                    ]
                );
            }

            $lockedBooking->tickets()->where('status', 'RESERVED')->delete();
            $lockedBooking->discounts()->delete();
            $lockedBooking->update([
                'subtotal_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => 0,
                'paid_amount' => 0,
                'expires_at' => $deadline,
            ]);
        }, 3);

        return $booking->fresh(['show', 'tickets']);
    }

    public function releaseOwnerSeats(Show $show, string $ownerToken, bool $clearWindow = false): void
    {
        if ($ownerToken === '') {
            return;
        }

        SeatHold::query()
            ->where('show_id', $show->id)
            ->where('owner_token', $ownerToken)
            ->whereIn('status', self::ACTIVE_HOLD_STATUSES)
            ->update(['status' => 'CANCELLED']);

        if ($clearWindow) {
            Cache::forget($this->holdWindowCacheKey($show, $ownerToken));
        }
    }

    private function holdWindowCacheKey(Show $show, string $ownerToken): string
    {
        return 'booking:hold-window:' . $show->id . ':' . sha1($ownerToken);
    }
}
