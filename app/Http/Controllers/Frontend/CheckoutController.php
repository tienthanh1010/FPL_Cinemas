<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Coupon;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Show;
use App\Services\LoyaltyPointService;
use App\Services\TicketLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PROVIDER_OPTIONS = [
        'MOMO' => ['label' => 'Ví MoMo', 'method' => 'EWALLET', 'description' => 'Thanh toán nhanh bằng ví điện tử MoMo.'],
        'ZALOPAY' => ['label' => 'ZaloPay', 'method' => 'EWALLET', 'description' => 'Thanh toán ngay qua ví ZaloPay.'],
        'VNPAY' => ['label' => 'VNPay', 'method' => 'BANK_TRANSFER', 'description' => 'Thanh toán qua VNPay / ngân hàng nội địa.'],
        'CARD' => ['label' => 'Thẻ ngân hàng', 'method' => 'CARD', 'description' => 'Mô phỏng thanh toán thẻ nội địa / quốc tế.'],
    ];

    private const TERMINAL_BOOKING_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const PAID_LIKE_BOOKING_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function __construct(
        private readonly TicketLifecycleService $ticketLifecycleService,
        private readonly LoyaltyPointService $loyaltyPointService,
    ) {
    }

    public function show(string $booking_code): View|RedirectResponse
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with([
                'customer',
                'show.movieVersion.movie',
                'show.auditorium.cinema',
                'tickets.seat',
                'tickets.ticketType',
                'tickets.seatType',
                'bookingProducts.product.category',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        $booking = $this->expireIfNeeded($booking);

        if ((int) $booking->paid_amount >= (int) $booking->total_amount && in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                ->with('success', 'Booking đã được thanh toán trước đó.');
        }

        $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
        $estimatedPoints = $this->loyaltyPointService->previewPoints($amountDue);

        return view('frontend.payment', [
            'booking' => $booking,
            'amountDue' => $amountDue,
            'estimatedPoints' => $estimatedPoints,
            'providerOptions' => self::PROVIDER_OPTIONS,
        ]);
    }

    public function pay(Request $request, string $booking_code): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(self::PROVIDER_OPTIONS))],
        ]);

        try {
            DB::transaction(function () use ($booking_code, $data) {
                /** @var Booking $booking */
                $booking = Booking::query()
                    ->where('booking_code', $booking_code)
                    ->with([
                        'show.auditorium.cinema',
                        'tickets.ticket',
                        'bookingProducts.product',
                        'discounts.coupon',
                        'payments.refunds',
                    ])
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentCinemaId = current_cinema_id();
                if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
                    abort(404);
                }

                $booking = $this->expireIfNeeded($booking);
                $booking->refresh();
                $booking->loadMissing(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'payments.refunds']);

                if (in_array((string) $booking->status, self::TERMINAL_BOOKING_STATUSES, true)) {
                    abort(422, 'Booking đã hết hạn hoặc đã bị huỷ, không thể thanh toán tiếp.');
                }

                if ($booking->show && $booking->show->start_time && now()->gte($booking->show->start_time)) {
                    abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc, không thể thanh toán.');
                }

                $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
                if ($amountDue <= 0) {
                    abort(422, 'Booking này không còn số tiền nào cần thanh toán.');
                }

                $provider = (string) $data['provider'];
                $option = self::PROVIDER_OPTIONS[$provider];
                $externalRef = $provider . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

                Payment::create([
                    'booking_id' => $booking->id,
                    'provider' => $provider,
                    'method' => $option['method'],
                    'status' => 'CAPTURED',
                    'amount' => $amountDue,
                    'currency' => $booking->currency ?: 'VND',
                    'external_txn_ref' => $externalRef,
                    'request_payload' => [
                        'mode' => 'FRONTEND_SIMULATED',
                        'provider' => $provider,
                        'method' => $option['method'],
                        'booking_code' => $booking->booking_code,
                        'customer_name' => $booking->contact_name,
                        'customer_phone' => $booking->contact_phone,
                        'amount_due' => $amountDue,
                    ],
                    'response_payload' => [
                        'status' => 'SUCCESS',
                        'message' => 'Thanh toán mô phỏng thành công từ giao diện khách hàng.',
                        'external_txn_ref' => $externalRef,
                    ],
                    'paid_at' => now(),
                ]);

                $this->syncBookingFromPayments($booking->fresh(['payments.refunds', 'tickets.ticket']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.success', ['booking_code' => $booking_code])
            ->with('success', 'Thanh toán thành công. Vé của bạn đã được phát hành.');
    }

    private function syncBookingFromPayments(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }

        $paidAmount = (int) $booking->payments->sum(fn (Payment $payment) => $this->netCapturedAmount($payment));
        $paidAmount = max(0, min((int) $booking->total_amount, $paidAmount));

        $payload = ['paid_amount' => $paidAmount];
        $currentStatus = (string) $booking->status;

        if (! in_array($currentStatus, self::TERMINAL_BOOKING_STATUSES, true)) {
            $payload['status'] = $paidAmount > 0 ? 'PAID' : 'PENDING';
        }

        $booking->update($payload);

        $nextStatus = (string) ($payload['status'] ?? $currentStatus);
        if (in_array($nextStatus, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'ISSUED']);
        } else {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'RESERVED']);
        }

        $freshBooking = $booking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
        $this->ticketLifecycleService->syncForBooking($freshBooking);
        $this->loyaltyPointService->syncForBooking($freshBooking);
    }

    private function expireIfNeeded(Booking $booking): Booking
    {
        if ((string) $booking->status !== 'PENDING') {
            return $booking;
        }

        if (! $booking->expires_at || now()->lt($booking->expires_at)) {
            return $booking;
        }

        DB::transaction(function () use ($booking) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'payments.refunds'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((string) $lockedBooking->status !== 'PENDING') {
                return;
            }

            $lockedBooking->update([
                'status' => 'EXPIRED',
                'paid_amount' => 0,
            ]);

            $lockedBooking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => 'EXPIRED']);

            foreach ($lockedBooking->bookingProducts as $item) {
                $this->restoreInventory($lockedBooking, $item);
            }

            foreach ($lockedBooking->discounts as $discount) {
                if ($discount->coupon instanceof Coupon && $discount->coupon->status === 'REDEEMED') {
                    $discount->coupon->update([
                        'status' => $discount->coupon->expires_at && $discount->coupon->expires_at->isPast() ? 'EXPIRED' : 'ACTIVE',
                        'redeemed_at' => null,
                    ]);
                }
            }

            $expiredBooking = $lockedBooking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
            $this->ticketLifecycleService->syncForBooking($expiredBooking);
            $this->loyaltyPointService->syncForBooking($expiredBooking);
            $this->refreshShowSaleStatus($lockedBooking->show);
        }, 3);

        return $booking->fresh([
            'customer',
            'show.movieVersion.movie',
            'show.auditorium.cinema',
            'tickets.seat',
            'tickets.ticketType',
            'tickets.seatType',
            'bookingProducts.product.category',
            'discounts.promotion',
            'discounts.coupon',
            'payments.refunds',
        ]);
    }

    private function restoreInventory(Booking $booking, BookingProduct $bookingProduct): void
    {
        $product = $bookingProduct->product;
        if (! $product || (int) $bookingProduct->qty <= 0) {
            return;
        }

        $locationId = StockMovement::query()
            ->where('reference_type', 'BOOKING')
            ->where('reference_id', $booking->id)
            ->where('product_id', $product->id)
            ->value('stock_location_id');

        $location = $locationId
            ? StockLocation::query()->find($locationId)
            : StockLocation::query()->firstOrCreate(
                ['cinema_id' => $booking->cinema_id, 'code' => 'KIOSK1'],
                ['name' => 'Quầy F&B chính', 'location_type' => 'KIOSK', 'is_active' => 1]
            );

        if (! $location) {
            return;
        }

        $alreadyRestored = StockMovement::query()
            ->where('reference_type', 'BOOKING_CANCEL')
            ->where('reference_id', $booking->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyRestored) {
            return;
        }

        $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
            ['stock_location_id' => $location->id, 'product_id' => $product->id],
            ['qty_on_hand' => 0, 'reorder_level' => 5]
        );

        $balance->update([
            'qty_on_hand' => (int) $balance->qty_on_hand + (int) $bookingProduct->qty,
        ]);

        StockMovement::create([
            'stock_location_id' => $location->id,
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'qty_delta' => (int) $bookingProduct->qty,
            'reference_type' => 'BOOKING_CANCEL',
            'reference_id' => $booking->id,
            'note' => 'Hoàn tồn do booking hết hạn từ giao diện khách hàng',
            'created_at' => now(),
        ]);
    }


    private function refreshShowSaleStatus(?Show $show): void
    {
        if (! $show || in_array((string) $show->status, ['CANCELLED', 'ENDED'], true)) {
            return;
        }

        $show = Show::query()->find($show->id);
        if (! $show) {
            return;
        }

        $totalSeats = DB::table('seats')
            ->where('auditorium_id', $show->auditorium_id)
            ->where('is_active', 1)
            ->count();

        $busySeats = DB::table('booking_tickets')
            ->where('show_id', $show->id)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->count();

        $nextStatus = ($totalSeats > 0 && $busySeats >= $totalSeats) ? 'SOLD_OUT' : 'ON_SALE';

        if ($show->start_time && now()->gte($show->start_time)) {
            return;
        }

        if ($show->status !== $nextStatus) {
            $show->update(['status' => $nextStatus]);
        }
    }

    private function netCapturedAmount(Payment $payment): int
    {
        $refundSuccessAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');

        return match ((string) $payment->status) {
            'CAPTURED' => max(0, (int) $payment->amount - $refundSuccessAmount),
            'REFUNDED' => 0,
            default => 0,
        };
    }
}
