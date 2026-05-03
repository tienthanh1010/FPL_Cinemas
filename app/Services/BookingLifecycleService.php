<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Coupon;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\Show;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class BookingLifecycleService
{
    private const TERMINAL_BOOKING_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const PAID_LIKE_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function __construct(
        private readonly TicketLifecycleService $ticketLifecycleService,
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly BookingGuardService $bookingGuardService,
    ) {
    }

    public function expirePendingBooking(Booking $booking, string $reason = 'Booking hết hạn tự động'): Booking
    {
        if ((string) $booking->status !== 'PENDING') {
            return $booking;
        }

        if (! $booking->expires_at || now()->lt($booking->expires_at)) {
            return $booking;
        }

        DB::transaction(function () use ($booking, $reason) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'show', 'payments.refunds'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((string) $lockedBooking->status !== 'PENDING') {
                return;
            }

            $lockedBooking->update([
                'status' => 'EXPIRED',
                'paid_amount' => 0,
                'notes' => trim((string) ($lockedBooking->notes ? ($lockedBooking->notes . PHP_EOL) : '') . $reason),
            ]);

            $lockedBooking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => 'CANCELLED']);

            $lockedBooking->payments()
                ->whereIn('status', ['INITIATED', 'AUTHORIZED'])
                ->update([
                    'status' => 'CANCELLED',
                    'response_payload' => DB::raw("JSON_SET(COALESCE(response_payload, JSON_OBJECT()), '$.status', 'CANCELLED', '$.message', 'Booking hết hạn, giao dịch thanh toán bị huỷ và ghế đã được nhả.')"),
                    'updated_at' => now(),
                ]);

            foreach ($lockedBooking->bookingProducts as $item) {
                $this->restoreInventory($lockedBooking, $item);
            }

            foreach ($lockedBooking->discounts as $discount) {
                if ($discount->coupon instanceof Coupon && $discount->coupon->status === 'REDEEMED') {
                    $discount->coupon->update([
                        'status' => $discount->coupon->expires_at && $discount->coupon->expires_at->isPast() ? 'EXPIRED' : 'ISSUED',
                        'redeemed_at' => null,
                    ]);
                }
            }

            $expiredBooking = $lockedBooking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
            $this->ticketLifecycleService->syncForBooking($expiredBooking);
            $this->loyaltyPointService->syncForBooking($expiredBooking);
            $this->refreshShowSaleStatus($lockedBooking->show);
            $this->bookingGuardService->registerExpiredBooking($lockedBooking);
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

    public function syncBookingFromPayments(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }

        $booking->loadMissing(['payments.refunds', 'tickets.ticket']);

        $paidAmount = (int) $booking->payments->sum(fn (Payment $payment) => $this->netCapturedAmount($payment));
        $paidAmount = max(0, min((int) $booking->total_amount, $paidAmount));

        $successfulRefundAmount = (int) $booking->payments
            ->flatMap(fn (Payment $payment) => $payment->refunds)
            ->where('status', 'SUCCESS')
            ->sum('amount');

        $fullSuccessRefund = $successfulRefundAmount > 0 && $paidAmount <= 0;
        $currentStatus = (string) $booking->status;

        $payload = ['paid_amount' => $paidAmount];

        if (! in_array($currentStatus, self::TERMINAL_BOOKING_STATUSES, true)) {
            if ($fullSuccessRefund && $currentStatus !== 'COMPLETED') {
                $payload['status'] = 'CANCELLED';
            } else {
                $payload['status'] = $paidAmount > 0 ? 'PAID' : 'PENDING';
            }
        }

        $booking->update($payload);
        $nextStatus = (string) ($payload['status'] ?? $currentStatus);

        if ($nextStatus === 'CANCELLED') {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'CANCELLED']);
        } elseif ($nextStatus === 'EXPIRED') {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'CANCELLED']);
        } else {
            $booking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => $paidAmount > 0 ? 'ISSUED' : 'RESERVED']);
        }

        $freshBooking = $booking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
        $this->ticketLifecycleService->syncForBooking($freshBooking);
        $this->loyaltyPointService->syncForBooking($freshBooking);
    }

    public function refreshShowSaleStatus(?Show $show): void
    {
        if (! $show || in_array((string) $show->status, ['CANCELLED', 'ENDED'], true)) {
            return;
        }

        $show = Show::query()->find($show->id);
        if (! $show || ($show->start_time && now()->gte($show->start_time))) {
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

        if ($show->status !== $nextStatus) {
            $show->update(['status' => $nextStatus]);
        }
    }

    public function netCapturedAmount(Payment $payment): int
    {
        $refundSuccessAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');

        return match ((string) $payment->status) {
            'CAPTURED' => max(0, (int) $payment->amount - $refundSuccessAmount),
            'REFUNDED' => 0,
            default => 0,
        };
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
            'note' => 'Hoàn tồn do booking hết hạn / bị huỷ',
            'created_at' => now(),
        ]);
    }
}
