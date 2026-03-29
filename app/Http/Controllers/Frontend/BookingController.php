<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\BookingTicket;
use App\Models\Customer;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\Show;
use App\Models\ShowPrice;
use App\Models\StockLocation;
use App\Models\StockMovement;
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
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'show_id' => ['required', 'integer'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:10'],
            'seat_ids' => ['nullable', 'array'],
            'seat_ids.*' => ['integer'],
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'product_qty' => ['nullable', 'array'],
            'product_qty.*' => ['nullable', 'integer', 'min:0', 'max:20'],
            'coupon_code' => ['nullable', 'string', 'max:64'],
            'payment_method' => ['required', 'string', 'in:COUNTER,BANK_TRANSFER,CARD,CASH'],
            'payment_note' => ['nullable', 'string', 'max:255'],
        ]);

        $bookingCode = null;

        try {
            DB::transaction(function () use ($data, &$bookingCode) {
                $show = Show::query()
                    ->with(['auditorium.cinema', 'movieVersion.movie'])
                    ->lockForUpdate()
                    ->findOrFail($data['show_id']);

                if ($show->status !== 'ON_SALE') {
                    abort(422, 'Suất chiếu hiện không mở bán.');
                }
                if (($show->on_sale_from && now()->lt($show->on_sale_from)) || ($show->on_sale_until && now()->gt($show->on_sale_until))) {
                    abort(422, 'Suất chiếu chưa tới thời gian mở bán hoặc đã đóng bán.');
                }
                if ($show->start_time && now()->gte($show->start_time)) {
                    abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc.');
                }

                $auditoriumId = $show->auditorium_id;
                $cinemaId = $show->auditorium->cinema_id;
                $ticketTypeId = (int) $data['ticket_type_id'];
                $requestedSeatIds = collect($data['seat_ids'] ?? [])->filter()->map(fn ($value) => (int) $value)->unique()->values();
                $requestedQty = $requestedSeatIds->isEmpty() ? max(1, (int) ($data['qty'] ?? 1)) : $requestedSeatIds->count();

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

                $paymentStatus = in_array($data['payment_method'], ['BANK_TRANSFER', 'CARD', 'CASH'], true) ? 'CAPTURED' : 'INITIATED';
                Payment::create([
                    'booking_id' => $booking->id,
                    'provider' => in_array($data['payment_method'], ['BANK_TRANSFER', 'CARD'], true) ? 'SANDBOX_GATEWAY' : 'BOX_OFFICE',
                    'method' => $data['payment_method'],
                    'status' => $paymentStatus,
                    'amount' => (int) $booking->total_amount,
                    'currency' => 'VND',
                    'external_txn_ref' => 'PAY' . now()->format('YmdHis') . strtoupper(Str::random(4)),
                    'request_payload' => [
                        'booking_code' => $booking->booking_code,
                        'payment_note' => $data['payment_note'] ?? null,
                    ],
                    'response_payload' => [
                        'message' => $paymentStatus === 'CAPTURED' ? 'Thanh toán mô phỏng thành công' : 'Booking giữ chỗ, chờ thanh toán tại quầy',
                    ],
                    'paid_at' => $paymentStatus === 'CAPTURED' ? now() : null,
                ]);

                if ($paymentStatus === 'CAPTURED') {
                    $booking->update([
                        'status' => 'CONFIRMED',
                        'paid_amount' => (int) $booking->total_amount,
                    ]);

                    BookingTicket::query()
                        ->where('booking_id', $booking->id)
                        ->update(['status' => 'ISSUED']);
                }

                $totalSeats = Seat::query()->where('auditorium_id', $show->auditorium_id)->where('is_active', 1)->count();
                $sold = BookingTicket::query()->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED'])->count();
                if ($totalSeats > 0 && $sold >= $totalSeats) {
                    $show->update(['status' => 'SOLD_OUT']);
                }
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.success', ['booking_code' => $bookingCode]);
    }

    public function success(string $booking_code)
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with(['tickets.seat', 'show.movieVersion.movie', 'show.auditorium', 'bookingProducts.product', 'discounts.promotion', 'payments'])
            ->firstOrFail();

        return view('frontend.booking_success', compact('booking'));
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
