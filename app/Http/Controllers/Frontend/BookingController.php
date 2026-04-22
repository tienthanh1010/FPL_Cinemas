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

                $requestedSeatIds = collect($data['seat_ids'] ?? [])->filter()->map(fn ($value) => (int) $value)->unique()->values();
                $requestedQty = $requestedSeatIds->count();

                foreach ($requestedSeatIds as $seatId) {
                    if (! $seatTicketTypes->has($seatId)) {
                        $seatTicketTypes->put($seatId, $defaultTicketTypeId);
                    }
                }

                if ($seatTicketTypes->keys()->diff($requestedSeatIds)->isNotEmpty()) {
                    $seatTicketTypes = $seatTicketTypes->only($requestedSeatIds->all());
                }

                if ($seatTicketTypes->count() !== $requestedSeatIds->count()) {
                    abort(422, 'Bạn cần chọn loại vé cho từng ghế.');
                }

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
                        abort(422, 'Phim T18 không cho phép áp dụng vé trẻ em.');
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
                    'expires_at' => now()->addMinutes(booking_hold_minutes()),
                ]);

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
            ->when(movie_blocks_child_tickets($movie), fn ($query) => $query->whereRaw('UPPER(code) <> ?', ['CHILD']))
            ->orderBy('id')
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
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with([
                'tickets.seat',
                'tickets.ticket',
                'tickets.ticketType',
                'tickets.seatType',
                'payments',
                'show.movieVersion.movie.contentRating',
                'show.auditorium.cinema',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        if (! in_array((string) $booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true)) {
            abort(403, 'Booking chưa đủ điều kiện để in vé cứng.');
        }

        $issuedTickets = $booking->tickets->filter(function ($bookingTicket) {
            return strtoupper((string) $bookingTicket->status) === 'ISSUED'
                && $bookingTicket->ticket
                && $bookingTicket->ticket->ticket_code;
        })->values();

        if ($issuedTickets->isEmpty()) {
            abort(404, 'Booking này chưa có vé điện tử để in.');
        }

        $booking->setRelation('tickets', $issuedTickets);

        $lastSuccessfulPayment = $booking->payments
            ->filter(fn ($payment) => in_array(strtoupper((string) $payment->status), ['CAPTURED', 'REFUNDED'], true))
            ->sortByDesc(fn ($payment) => optional($payment->paid_at)->getTimestamp() ?: 0)
            ->first();

        return view('frontend.print_ticket', [
            'booking' => $booking,
            'printedAt' => now(),
            'lastSuccessfulPayment' => $lastSuccessfulPayment,
        ]);
    }
}

