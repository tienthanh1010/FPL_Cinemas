<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\BookingTicket;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\Show;
use App\Models\ShowPrice;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\TicketType;
use Illuminate\View\View;
use App\Services\CustomerAccountService;
use App\Services\ProductPricingService;
use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $productPricingService,
        private readonly PromotionService $promotionService,
        private readonly CustomerAccountService $customerAccountService,
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

        $ticketTypes = TicketType::query()->orderBy('id')->get(['id', 'code', 'name', 'description']);
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
        $auditoriumId = (int) $show->auditorium_id;
        $cinemaId = (int) $show->auditorium?->cinema_id;

        $seats = Seat::query()
            ->with('seatType:id,code,name')
            ->where('auditorium_id', $auditoriumId)
            ->where('is_active', 1)
            ->orderBy('row_label')
            ->orderBy('col_number')
            ->get(['id', 'seat_type_id', 'seat_code', 'row_label', 'col_number']);

        $reservedSeatIds = BookingTicket::query()
            ->where('show_id', $show->id)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->pluck('seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $heldSeatIds = DB::table('seat_holds')
            ->where('show_id', $show->id)
            ->whereIn('status', ['HELD', 'CONFIRMED'])
            ->where('expires_at', '>', now())
            ->pluck('seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $blockedSeatIds = SeatBlock::query()
            ->where('auditorium_id', $auditoriumId)
            ->where('start_at', '<', $show->end_time)
            ->where('end_at', '>', $show->start_time)
            ->pluck('seat_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $unavailableSeatIds = array_fill_keys(array_unique(array_merge($reservedSeatIds, $heldSeatIds, $blockedSeatIds)), true);

        $prices = ShowPrice::query()
            ->where('show_id', $show->id)
            ->where('is_active', 1)
            ->get(['seat_type_id', 'ticket_type_id', 'price_amount']);

        $priceMap = [];
        foreach ($prices as $price) {
            $priceMap[(int) $price->seat_type_id][(string) $price->ticket_type_id] = (int) $price->price_amount;
        }

        $products = Product::query()
            ->where('is_active', 1)
            ->with('category:id,name')
            ->orderByDesc('is_combo')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'sku', 'unit', 'is_combo', 'attributes']);

        $inventoryRows = InventoryBalance::query()
            ->selectRaw('inventory_balances.product_id, stock_locations.cinema_id, SUM(inventory_balances.qty_on_hand) as qty_on_hand')
            ->join('stock_locations', 'stock_locations.id', '=', 'inventory_balances.stock_location_id')
            ->where('stock_locations.cinema_id', $cinemaId)
            ->where('stock_locations.is_active', 1)
            ->groupBy('inventory_balances.product_id', 'stock_locations.cinema_id')
            ->get();

        $inventoryByProduct = [];
        foreach ($inventoryRows as $row) {
            $inventoryByProduct[(int) $row->product_id] = (int) $row->qty_on_hand;
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
            'seats' => $seats->map(fn ($seat) => [
                'id' => (int) $seat->id,
                'seat_type_id' => (int) $seat->seat_type_id,
                'seat_code' => $seat->seat_code,
                'row_label' => $seat->row_label,
                'col_number' => (int) $seat->col_number,
                'seat_type_code' => strtoupper((string) ($seat->seatType?->code ?? 'REG')),
                'seat_type_name' => $seat->seatType?->name ?? 'Ghế thường',
                'available' => ! isset($unavailableSeatIds[(int) $seat->id]),
            ])->values()->all(),
            'prices' => $priceMap,
            'products' => $products->map(function ($product) use ($cinemaId, $inventoryByProduct) {
                $price = $this->productPricingService->currentPrice($product, $cinemaId);
                $qtyOnHand = max(0, (int) ($inventoryByProduct[(int) $product->id] ?? 0));

                return [
                    'id' => (int) $product->id,
                    'name' => $product->name,
                    'category' => $product->category?->name ?? 'F&B',
                    'unit' => $product->unit,
                    'is_combo' => (bool) $product->is_combo,
                    'description' => data_get($product->attributes ?? [], 'description')
                        ?: data_get($product->attributes ?? [], 'summary')
                        ?: null,
                    'price_amount' => (int) ($price?->price_amount ?? 0),
                    'qty_on_hand' => $qtyOnHand,
                    'available' => $price !== null && $qtyOnHand > 0,
                ];
            })->values()->all(),
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'show_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:10'],
            'seat_ids' => ['nullable', 'array'],
            'seat_ids.*' => ['integer', 'exists:seats,id'],
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'product_qty' => ['nullable', 'array'],
            'product_qty.*' => ['nullable', 'integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
        ]);

        $bookingCode = null;
        $memberUser = auth()->user();

        if ($memberUser && empty($data['contact_email'])) {
            $data['contact_email'] = $memberUser->email;
        }

        try {
            DB::transaction(function () use ($data, $memberUser, &$bookingCode) {
                $show = Show::query()
                    ->with(['auditorium.cinema', 'movieVersion.movie'])
                    ->lockForUpdate()
                    ->findOrFail($data['show_id']);

                $currentCinemaId = current_cinema_id();
                if ($currentCinemaId && (int) $show->auditorium->cinema_id !== (int) $currentCinemaId) {
                    abort(404);
                }

                if (! $show->isOnSaleNow()) {
                    abort(422, 'Suất chiếu hiện chưa mở bán hoặc đã đóng bán.');
                }
                if ($show->start_time && now()->gte($show->start_time)) {
                    abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc.');
                }

                $auditoriumId = $show->auditorium_id;
                $cinemaId = $show->auditorium->cinema_id;
                $ticketTypeId = (int) $data['ticket_type_id'];
                $requestedSeatIds = collect($data['seat_ids'] ?? [])->filter()->map(fn ($value) => (int) $value)->unique()->values();
                $requestedQty = $requestedSeatIds->isEmpty() ? max(1, (int) ($data['qty'] ?? 1)) : $requestedSeatIds->count();

                if ($requestedQty > 10) {
                    abort(422, 'Bạn chỉ có thể chọn tối đa 10 ghế trong một lần đặt vé.');
                }

                $reservedSeatIds = BookingTicket::query()->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED'])->pluck('seat_id');
                $heldSeatIds = DB::table('seat_holds')->where('show_id', $show->id)->whereIn('status', ['HELD', 'CONFIRMED'])->where('expires_at', '>', now())->pluck('seat_id');
                $blockedSeatIds = SeatBlock::query()->where('auditorium_id', $auditoriumId)->where('start_at', '<', $show->end_time)->where('end_at', '>', $show->start_time)->pluck('seat_id');

                if ($requestedSeatIds->isEmpty()) {
                    $requestedSeatIds = Seat::query()
                        ->where('auditorium_id', $auditoriumId)
                        ->where('is_active', 1)
                        ->whereNotIn('id', $reservedSeatIds)
                        ->whereNotIn('id', $heldSeatIds)
                        ->whereNotIn('id', $blockedSeatIds)
                        ->orderBy('row_label')
                        ->orderBy('col_number')
                        ->limit($requestedQty)
                        ->lockForUpdate()
                        ->pluck('id');

                    if ($requestedSeatIds->count() !== $requestedQty) {
                        abort(422, 'Không còn đủ ghế trống cho số lượng bạn chọn.');
                    }
                }

                $seats = Seat::query()
                    ->where('auditorium_id', $auditoriumId)
                    ->where('is_active', 1)
                    ->whereIn('id', $requestedSeatIds)
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() !== $requestedSeatIds->count()) {
                    abort(422, 'Có ghế không thuộc phòng chiếu của suất này hoặc đang bảo trì.');
                }
                if ($blockedSeatIds->intersect($requestedSeatIds)->isNotEmpty()) {
                    abort(422, 'Một hoặc nhiều ghế đang bị khoá thủ công / bảo trì.');
                }
                if ($reservedSeatIds->merge($heldSeatIds)->intersect($requestedSeatIds)->isNotEmpty()) {
                    abort(422, 'Một hoặc nhiều ghế đã được giữ/đặt, vui lòng chọn ghế khác.');
                }

                $priceBySeatType = ShowPrice::query()
                    ->where('show_id', $show->id)
                    ->where('ticket_type_id', $ticketTypeId)
                    ->where('is_active', 1)
                    ->get()
                    ->keyBy('seat_type_id');

                $ticketSubtotal = 0;
                foreach ($seats as $seat) {
                    $ticketSubtotal += (int) (($priceBySeatType[$seat->seat_type_id]->price_amount ?? null) ?? 120000);
                }

                $productRows = [];
                $productSubtotal = 0;
                foreach (collect($data['product_qty'] ?? [])->filter(fn ($qty) => (int) $qty > 0) as $productId => $qty) {
                    $product = Product::query()->where('is_active', 1)->find($productId);
                    if (! $product) {
                        continue;
                    }
                    $price = $this->productPricingService->currentPrice($product, $cinemaId);
                    if (! $price) {
                        continue;
                    }

                    $qty = (int) $qty;
                    $lineAmount = $qty * (int) $price->price_amount;
                    $productRows[] = compact('product', 'qty', 'lineAmount') + ['unitPrice' => (int) $price->price_amount];
                    $productSubtotal += $lineAmount;
                }

                $bookingCode = 'BK' . now()->format('Ymd') . strtoupper(Str::random(6));
                $subtotal = $ticketSubtotal + $productSubtotal;

                if ($memberUser) {
                    $customer = $this->customerAccountService->syncCustomerForUser($memberUser, [
                        'full_name' => $data['contact_name'],
                        'phone' => $data['contact_phone'],
                        'email' => $data['contact_email'] ?? $memberUser->email,
                    ]);
                } else {
                    $customer = Customer::query()
                        ->when(! empty($data['contact_email']), function ($query) use ($data) {
                            $query->where('email', $data['contact_email'])
                                ->orWhere('phone', $data['contact_phone']);
                        }, function ($query) use ($data) {
                            $query->where('phone', $data['contact_phone']);
                        })
                        ->first();

                    if ($customer) {
                        $customer->update([
                            'full_name' => $data['contact_name'],
                            'email' => $data['contact_email'] ?? $customer->email,
                            'phone' => $data['contact_phone'],
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
                    'expires_at' => now()->addMinutes(15),
                ]);

                foreach ($seats as $seat) {
                    $unitPrice = (int) (($priceBySeatType[$seat->seat_type_id]->price_amount ?? null) ?? 120000);
                    BookingTicket::create([
                        'booking_id' => $booking->id,
                        'show_id' => $show->id,
                        'seat_id' => $seat->id,
                        'ticket_type_id' => $ticketTypeId,
                        'seat_type_id' => $seat->seat_type_id,
                        'unit_price_amount' => $unitPrice,
                        'discount_amount' => 0,
                        'final_price_amount' => $unitPrice,
                        'status' => 'RESERVED',
                    ]);
                }

                foreach ($productRows as $row) {
                    BookingProduct::create([
                        'booking_id' => $booking->id,
                        'product_id' => $row['product']->id,
                        'qty' => $row['qty'],
                        'unit_price_amount' => $row['unitPrice'],
                        'discount_amount' => 0,
                        'final_amount' => $row['lineAmount'],
                    ]);
                    $this->decreaseInventory($cinemaId, $row['product']->id, $row['qty'], $booking->id, $row['product']->name);
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

                $totalSeats = Seat::query()->where('auditorium_id', $show->auditorium_id)->where('is_active', 1)->count();
                $sold = BookingTicket::query()->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED'])->count();
                if ($totalSeats > 0 && $sold >= $totalSeats) {
                    $show->update(['status' => 'SOLD_OUT']);
                }
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.payment', ['booking_code' => $bookingCode])
            ->with('success', 'Đặt vé thành công. Vui lòng hoàn tất thanh toán trong 15 phút để giữ chỗ.');
    }

    public function success(string $booking_code)
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with(['customer.loyaltyAccount.tier', 'tickets.seat', 'tickets.ticket', 'tickets.ticketType', 'tickets.seatType', 'show.movieVersion.movie', 'show.auditorium.cinema', 'bookingProducts.product.category', 'discounts.promotion', 'discounts.coupon', 'payments.refunds'])
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

    private function decreaseInventory(int $cinemaId, int $productId, int $qty, int $bookingId, string $productName): void
    {
        $location = StockLocation::query()->firstOrCreate(
            ['cinema_id' => $cinemaId, 'code' => 'KIOSK1'],
            ['name' => 'Quầy F&B chính', 'location_type' => 'KIOSK', 'is_active' => 1]
        );

        $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
            ['stock_location_id' => $location->id, 'product_id' => $productId],
            ['qty_on_hand' => 0, 'reorder_level' => 5]
        );

        if ($balance->qty_on_hand < $qty) {
            abort(422, 'Sản phẩm F&B "' . $productName . '" không đủ tồn kho.');
        }

        $balance->update(['qty_on_hand' => $balance->qty_on_hand - $qty]);
        StockMovement::create([
            'stock_location_id' => $location->id,
            'product_id' => $productId,
            'movement_type' => 'OUT',
            'qty_delta' => -$qty,
            'reference_type' => 'BOOKING',
            'reference_id' => $bookingId,
            'note' => 'Bán kèm theo booking',
            'created_at' => now(),
        ]);
    }
}
