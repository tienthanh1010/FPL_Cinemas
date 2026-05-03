<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTicket;
use App\Models\Customer;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\SeatHold;
use App\Models\Show;
use App\Models\ShowPrice;
use App\Models\TicketType;
use App\Services\BookingGuardService;
use App\Services\BookingLifecycleService;
use App\Services\CustomerAccountService;
use App\Services\PromotionService;
use App\Services\SeatHoldService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly PromotionService $promotionService,
        private readonly CustomerAccountService $customerAccountService,
        private readonly SeatHoldService $seatHoldService,
        private readonly BookingGuardService $bookingGuardService,
        private readonly BookingLifecycleService $bookingLifecycleService,
    ) {
    }

    public function create(Show $show): View|RedirectResponse
    {
        $show->loadMissing(['auditorium.cinema', 'movieVersion.movie.genres', 'movieVersion.movie.contentRating']);

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $show->auditorium?->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        $movie = $show->movieVersion?->movie;
        if (! $movie || $movie->status !== 'ACTIVE') {
            abort(404);
        }

        if (! $show->isOnSaleNow()) {
            return redirect()
                ->route('movies.showtimes', $movie)
                ->with('error', 'Suất chiếu này hiện chưa mở bán để đặt vé trực tiếp.');
        }

        $ownerToken = $this->seatHoldService->ownerToken(session('seat_hold_owner_token'));
        $editableBookingCode = (string) (request('booking_code') ?: session('editable_booking_code'));
        if ($editableBookingCode !== '') {
            $editableBooking = Booking::query()
                ->where('booking_code', $editableBookingCode)
                ->where('show_id', $show->id)
                ->where('cinema_id', $show->auditorium?->cinema_id)
                ->where('status', 'PENDING')
                ->with(['show', 'tickets'])
                ->first();

            if ($editableBooking && $editableBooking->expires_at && now()->lt($editableBooking->expires_at)) {
                $this->seatHoldService->restorePendingBookingForEditing($editableBooking, $ownerToken);
                session(['editable_booking_code' => $editableBooking->booking_code]);
            }
        }

        $ticketTypes = $this->availableTicketTypes($movie);
        $bookingConfig = $this->buildBookingConfig($show);

        $relatedShows = Show::query()
            ->frontendVisible()
            ->whereHas('movieVersion', fn ($query) => $query->where('movie_id', $movie->id))
            ->whereHas('auditorium', fn ($query) => $query->where('is_active', 1)->whereHas('cinema', fn ($cinemaQuery) => $cinemaQuery->where('status', 'ACTIVE')))
            ->where('id', '!=', $show->id)
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->limit(8)
            ->get();

        return view('frontend.booking', compact('movie', 'show', 'ticketTypes', 'bookingConfig', 'relatedShows'));
    }

    private function buildBookingConfig(Show $show): array
    {
        $movie = $show->movieVersion?->movie;
        $seatPayload = $this->seatHoldService->seatPayload($show, session('seat_hold_owner_token'));

        $prices = ShowPrice::query()
            ->where('show_id', $show->id)
            ->where('is_active', 1)
            ->get(['seat_type_id', 'ticket_type_id', 'price_amount']);

        $priceMap = [];
        foreach ($prices as $price) {
            $priceMap[(int) $price->seat_type_id][(string) $price->ticket_type_id] = (int) $price->price_amount;
        }

        return [
            'id' => (int) $show->id,
            'showtime' => $show->start_time?->format('d/m/Y H:i') ?: '—',
            'show_date' => $show->start_time?->translatedFormat('l, d/m/Y') ?: '—',
            'start_time' => $show->start_time?->format('H:i') ?: '—',
            'end_time' => $show->end_time?->format('H:i') ?: '—',
            'auditorium' => $show->auditorium?->name ?: 'Phòng chiếu',
            'cinema' => $show->auditorium?->cinema?->name ?: config('app.name', 'FPL Cinemas'),
            'format' => $show->movieVersion?->format ?: '2D',
            'status_label' => $show->frontendStatusLabel(),
            'status' => $show->status,
            'on_sale' => $show->isOnSaleNow(),
            'hold_minutes' => booking_hold_minutes(),
            'seat_poll_seconds' => max(3, (int) config('cinema_booking.seat_poll_seconds', 5)),
            'max_seats_per_booking' => max(1, (int) config('cinema_booking.max_seats_per_booking', 10)),
            'child_ticket_blocked' => movie_blocks_child_tickets($movie),
            'seats' => $seatPayload,
            'prices' => $priceMap,
            'products' => [],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'show_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:' . max(1, (int) config('cinema_booking.max_seats_per_booking', 10))],
            'seat_ids' => ['required', 'array', 'min:1'],
            'seat_ids.*' => ['integer', 'exists:seats,id'],
            'seat_ticket_types' => ['nullable', 'array'],
            'seat_ticket_types.*' => ['nullable', 'integer', 'exists:ticket_types,id'],
            'ticket_type_id' => ['nullable', 'integer', 'exists:ticket_types,id'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $bookingCode = null;
        $memberUser = auth()->user();
        $holdOwnerToken = $this->seatHoldService->ownerToken($request->session()->get('seat_hold_owner_token'));

        if ($memberUser && empty($data['contact_email'])) {
            $data['contact_email'] = $memberUser->email;
        }

        try {
            DB::transaction(function () use ($request, $data, $memberUser, $holdOwnerToken, &$bookingCode) {
                $show = Show::query()
                    ->with(['auditorium.cinema', 'movieVersion.movie.contentRating'])
                    ->lockForUpdate()
                    ->findOrFail($data['show_id']);

                $currentCinemaId = current_cinema_id();
                if ($currentCinemaId && (int) $show->auditorium?->cinema_id !== (int) $currentCinemaId) {
                    abort(404);
                }

                if (! $show->isOnSaleNow()) {
                    abort(422, 'Suất chiếu hiện chưa mở bán hoặc đã đóng bán.');
                }
                if ($show->start_time && now()->gte($show->start_time)) {
                    abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc.');
                }

                $movie = $show->movieVersion?->movie;
                $availableTicketTypes = $this->availableTicketTypes($movie)->keyBy('id');
                if ($availableTicketTypes->isEmpty()) {
                    abort(422, 'Suất chiếu này hiện chưa cấu hình loại vé hợp lệ.');
                }

                $auditoriumId = (int) $show->auditorium_id;
                $cinemaId = (int) $show->auditorium?->cinema_id;
                $requestedSeatIds = collect($data['seat_ids'] ?? [])->filter()->map(fn ($value) => (int) $value)->unique()->values();

                if ($requestedSeatIds->isEmpty()) {
                    abort(422, 'Bạn cần chọn ít nhất 1 ghế để tiếp tục.');
                }

                $defaultTicketTypeId = (int) $availableTicketTypes->keys()->first();

                $seatTicketTypes = collect($requestedSeatIds)
                    ->mapWithKeys(fn ($seatId) => [(int) $seatId => $defaultTicketTypeId]);

                $customer = null;
                if ($memberUser) {
                    $customer = $this->customerAccountService->syncCustomerForUser($memberUser, [
                        'full_name' => $data['contact_name'],
                        'phone' => $data['contact_phone'],
                        'email' => $data['contact_email'] ?? null,
                    ]);
                } else {
                    $customer = Customer::query()
                        ->where(function ($query) use ($data) {
                            $query->where('phone', $data['contact_phone']);
                            if (! empty($data['contact_email'])) {
                                $query->orWhereRaw('LOWER(email) = ?', [mb_strtolower((string) $data['contact_email'])]);
                            }
                        })
                        ->orderByDesc('id')
                        ->first();

                    if ($customer) {
                        $customer->update([
                            'full_name' => $data['contact_name'],
                            'phone' => $data['contact_phone'],
                            'email' => $data['contact_email'] ?? $customer->email,
                            'account_status' => $customer->account_status ?: 'ACTIVE',
                        ]);
                    } else {
                        $customer = Customer::create([
                            'public_id' => (string) Str::ulid(),
                            'full_name' => $data['contact_name'],
                            'phone' => $data['contact_phone'],
                            'email' => $data['contact_email'] ?? null,
                            'account_status' => 'ACTIVE',
                        ]);
                    }
                }

                $this->bookingGuardService->assertCanCreateBooking($request, $show, [
                    'customer_id' => $customer?->id,
                    'contact_phone' => $data['contact_phone'],
                    'contact_email' => $data['contact_email'] ?? null,
                ]);

                $seats = Seat::query()
                    ->with('seatType:id,code,name')
                    ->where('auditorium_id', $auditoriumId)
                    ->where('is_active', 1)
                    ->whereIn('id', $requestedSeatIds)
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() !== $requestedSeatIds->count()) {
                    abort(422, 'Có ghế không thuộc phòng chiếu của suất này hoặc đang bảo trì.');
                }

                $blockedSeatIds = SeatBlock::query()
                    ->where('auditorium_id', $auditoriumId)
                    ->where('start_at', '<', $show->end_time)
                    ->where('end_at', '>', $show->start_time)
                    ->pluck('seat_id')
                    ->map(fn ($seatId) => (int) $seatId)
                    ->values();

                $reservedSeatIds = BookingTicket::query()
                    ->where('show_id', $show->id)
                    ->whereIn('status', ['RESERVED', 'ISSUED'])
                    ->pluck('seat_id')
                    ->map(fn ($seatId) => (int) $seatId)
                    ->values();

                $otherHeldSeatIds = SeatHold::query()
                    ->where('show_id', $show->id)
                    ->whereIn('status', ['HELD', 'CONFIRMED'])
                    ->where('expires_at', '>', now())
                    ->where('owner_token', '!=', $holdOwnerToken)
                    ->pluck('seat_id')
                    ->map(fn ($seatId) => (int) $seatId)
                    ->values();

                if ($blockedSeatIds->intersect($requestedSeatIds)->isNotEmpty()) {
                    abort(422, 'Một hoặc nhiều ghế đang bị khóa thủ công / bảo trì.');
                }
                if ($reservedSeatIds->merge($otherHeldSeatIds)->intersect($requestedSeatIds)->isNotEmpty()) {
                    abort(422, 'Một hoặc nhiều ghế đã được giữ/đặt, vui lòng chọn ghế khác.');
                }

                $this->validateSeatSelectionRules($show, $seats, array_unique(array_merge(
                    $reservedSeatIds->all(),
                    $otherHeldSeatIds->all(),
                    $blockedSeatIds->all(),
                )));

                $selectedTicketTypeIds = [];
                foreach ($requestedSeatIds as $seatId) {
                    $ticketTypeId = (int) $seatTicketTypes->get($seatId);
                    $ticketType = $availableTicketTypes->get($ticketTypeId);
                    if (! $ticketType) {
                        abort(422, 'Có loại vé không hợp lệ cho suất chiếu này.');
                    }

                    if (movie_blocks_child_tickets($movie) && strtoupper((string) $ticketType->code) === 'CHILD') {
                        abort(422, 'Vé trẻ em chỉ áp dụng cho phim có nhãn P / không giới hạn độ tuổi.');
                    }

                    $selectedTicketTypeIds[] = $ticketTypeId;
                }

                $priceRows = ShowPrice::query()
                    ->where('show_id', $show->id)
                    ->whereIn('ticket_type_id', array_values(array_unique($selectedTicketTypeIds)))
                    ->where('is_active', 1)
                    ->get(['seat_type_id', 'ticket_type_id', 'price_amount']);

                $priceMatrix = [];
                foreach ($priceRows as $price) {
                    $priceMatrix[(int) $price->seat_type_id][(int) $price->ticket_type_id] = (int) $price->price_amount;
                }

                $ticketRows = [];
                $ticketSubtotal = 0;
                foreach ($seats->sortBy(fn (Seat $seat) => $seat->row_label . '-' . str_pad((string) $seat->col_number, 3, '0', STR_PAD_LEFT)) as $seat) {
                    $ticketTypeId = (int) $seatTicketTypes->get((int) $seat->id, $defaultTicketTypeId);
                    $unitPrice = (int) ($priceMatrix[(int) $seat->seat_type_id][$ticketTypeId] ?? 120000);
                    $ticketRows[] = [
                        'seat' => $seat,
                        'ticket_type_id' => $ticketTypeId,
                        'unit_price_amount' => $unitPrice,
                    ];
                    $ticketSubtotal += $unitPrice;
                }

                $productSubtotal = 0;

                $bookingCode = 'BK' . now()->format('Ymd') . strtoupper(Str::random(6));
                $subtotal = $ticketSubtotal;
                $bookingExpiresAt = $this->seatHoldService->currentHoldDeadline($show, $holdOwnerToken) ?? now()->addMinutes(booking_hold_minutes());

                $editableBookingCode = (string) $request->session()->get('editable_booking_code');
                $booking = null;
                if ($editableBookingCode !== '') {
                    $booking = Booking::query()
                        ->where('booking_code', $editableBookingCode)
                        ->where('show_id', $show->id)
                        ->where('cinema_id', $cinemaId)
                        ->where('status', 'PENDING')
                        ->lockForUpdate()
                        ->first();
                }

                if ($booking) {
                    $bookingCode = $booking->booking_code;
                    $booking->tickets()->delete();
                    $booking->discounts()->delete();
                    $booking->update([
                        'customer_id' => $customer->id,
                        'contact_name' => $data['contact_name'],
                        'contact_phone' => $data['contact_phone'],
                        'contact_email' => $data['contact_email'] ?? null,
                        'subtotal_amount' => $subtotal,
                        'discount_amount' => 0,
                        'total_amount' => $subtotal,
                        'paid_amount' => 0,
                        'currency' => 'VND',
                        'expires_at' => $bookingExpiresAt,
                    ]);
                } else {
                    $booking = Booking::create([
                        'public_id' => (string) Str::ulid(),
                        'booking_code' => $bookingCode,
                        'show_id' => $show->id,
                        'cinema_id' => $cinemaId,
                        'customer_id' => $customer->id,
                        'sales_channel_id' => 1,
                        'status' => 'PENDING',
                        'contact_name' => $data['contact_name'],
                        'contact_phone' => $data['contact_phone'],
                        'contact_email' => $data['contact_email'] ?? null,
                        'subtotal_amount' => $subtotal,
                        'discount_amount' => 0,
                        'total_amount' => $subtotal,
                        'paid_amount' => 0,
                        'currency' => 'VND',
                        'expires_at' => $bookingExpiresAt,
                    ]);
                }

                foreach ($ticketRows as $row) {
                    BookingTicket::create([
                        'booking_id' => $booking->id,
                        'show_id' => $show->id,
                        'seat_id' => $row['seat']->id,
                        'ticket_type_id' => $row['ticket_type_id'],
                        'seat_type_id' => $row['seat']->seat_type_id,
                        'unit_price_amount' => $row['unit_price_amount'],
                        'discount_amount' => 0,
                        'final_price_amount' => $row['unit_price_amount'],
                        'status' => 'RESERVED',
                    ]);
                }

                $discountTotal = 0;
                $autoPromotions = $this->promotionService->eligiblePromotions($show, $booking, ['subtotal' => $subtotal]);
                foreach ($autoPromotions as $promotion) {
                    $base = $promotion->applies_to === 'PRODUCT'
                        ? $productSubtotal
                        : ($promotion->applies_to === 'TICKET' ? $ticketSubtotal : ($subtotal - $discountTotal));
                    $amount = $this->promotionService->discountAmount($promotion, $base);
                    $discountTotal += $amount;
                    $this->promotionService->persistDiscount($booking, $promotion, $amount, null, ['mode' => 'AUTO']);
                    if (! $promotion->is_stackable) {
                        break;
                    }
                }

                if (! empty($data['coupon_code'])) {
                    $couponResult = $this->promotionService->couponPromotion($data['coupon_code'], $show, $booking, $subtotal - $discountTotal);
                    if (! empty($couponResult['error'])) {
                        abort(422, $couponResult['error']);
                    }
                    $promotion = $couponResult['promotion'];
                    $coupon = $couponResult['coupon'];
                    $base = $promotion->applies_to === 'PRODUCT'
                        ? $productSubtotal
                        : ($promotion->applies_to === 'TICKET' ? $ticketSubtotal : ($subtotal - $discountTotal));
                    $amount = $this->promotionService->discountAmount($promotion, $base);
                    $discountTotal += $amount;
                    $this->promotionService->persistDiscount($booking, $promotion, $amount, $coupon, ['mode' => 'COUPON', 'code' => $coupon->code]);
                }

                $booking->update([
                    'discount_amount' => $discountTotal,
                    'total_amount' => max(0, $subtotal - $discountTotal),
                ]);

                $this->seatHoldService->releaseOwnerSeats($show, $holdOwnerToken);
                $this->bookingLifecycleService->refreshShowSaleStatus($show);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        session(['editable_booking_code' => $bookingCode]);

        return redirect()->route('booking.payment', ['booking_code' => $bookingCode])
            ->with('success', 'Đặt vé thành công. Vui lòng hoàn tất thanh toán trong ' . booking_hold_minutes() . ' phút để giữ chỗ.');
    }

    public function success(string $booking_code): View
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with([
                'customer.loyaltyAccount.tier',
                'tickets.seat',
                'tickets.ticket',
                'tickets.ticketType',
                'tickets.seatType',
                'show.movieVersion.movie.contentRating',
                'show.auditorium.cinema',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
                'feedback',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        $earnedPoints = in_array((string) $booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true)
            ? loyalty_preview_points(min((int) $booking->paid_amount, (int) $booking->total_amount))
            : 0;

        return view('frontend.booking_success', compact('booking', 'earnedPoints'));
    }

    private function availableTicketTypes($movie): Collection
    {
        return TicketType::query()
            ->orderBy('id')
            ->limit(1)
            ->get(['id', 'code', 'name', 'description']);
    }

    private function validateSeatSelectionRules(Show $show, Collection $selectedSeats, array $busySeatIds): void
    {
        $this->validateNoSingleGap(
            $show,
            $selectedSeats->pluck('id')->map(fn ($id) => (int) $id)->all(),
            $busySeatIds
        );
    }

    private function validateNoSingleGap(Show $show, array $selectedSeatIds, array $busySeatIds): void
    {
        $unavailableMap = array_fill_keys(array_unique(array_merge($selectedSeatIds, $busySeatIds)), true);

        $allSeats = Seat::query()
            ->where('auditorium_id', $show->auditorium_id)
            ->where('is_active', 1)
            ->orderBy('row_label')
            ->orderBy('col_number')
            ->get(['id', 'row_label', 'col_number']);

        foreach ($allSeats->groupBy('row_label') as $rowLabel => $rowSeats) {
            $segments = [];
            $current = [];
            $previousCol = null;

            foreach ($rowSeats as $seat) {
                if ($previousCol !== null && ((int) $seat->col_number - (int) $previousCol) > 1) {
                    $segments[] = collect($current);
                    $current = [];
                }

                $current[] = $seat;
                $previousCol = (int) $seat->col_number;
            }

            if ($current !== []) {
                $segments[] = collect($current);
            }

            foreach ($segments as $segment) {
                $availableRun = 0;
                foreach ($segment as $seat) {
                    if (isset($unavailableMap[(int) $seat->id])) {
                        if ($availableRun === 1) {
                            abort(422, 'Lựa chọn ghế hiện tại để lại 1 ghế lẻ ở hàng ' . $rowLabel . '. Vui lòng chọn lại để không chừa ghế đơn.');
                        }
                        $availableRun = 0;
                        continue;
                    }

                    $availableRun++;
                }

                if ($availableRun === 1) {
                    abort(422, 'Lựa chọn ghế hiện tại để lại 1 ghế lẻ ở hàng ' . $rowLabel . '. Vui lòng chọn lại để không chừa ghế đơn.');
                }
            }
        }
    }

    public function print(string $booking_code): View
    {
        abort(403, 'Chỉ quầy check-in/admin mới có quyền in vé cứng.');
    }
}

