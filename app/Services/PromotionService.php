<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingDiscount;
use App\Models\Coupon;
use App\Models\Promotion;
use App\Models\Show;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class PromotionService
{
    public function eligiblePromotions(Show $show, Booking $booking, array $context = []): Collection
    {
        $start = $show->start_time instanceof Carbon ? $show->start_time : Carbon::parse($show->start_time);
        $subtotal = (int) ($context['subtotal'] ?? $booking->subtotal_amount ?? 0);
        $customerId = $booking->customer_id;

        return Promotion::query()
            ->with(['movies:id', 'cinemas:id'])
            ->where('status', 'ACTIVE')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->where('auto_apply', 1)
            ->get()
            ->filter(function (Promotion $promotion) use ($show, $booking, $start, $subtotal, $customerId) {
                if ($promotion->coupon_required) {
                    return false;
                }
                if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
                    return false;
                }
                if (! $this->matchesShowWindow($promotion, $show, $start)) {
                    return false;
                }
                if (! $this->withinUsage($promotion, $customerId)) {
                    return false;
                }

                return $this->matchCustomerScope($promotion, $booking);
            })
            ->values();
    }

    public function couponPromotion(string $code, Show $show, Booking $booking, int $subtotal): array
    {
        $coupon = Coupon::query()
            ->with('promotion.movies:id', 'promotion.cinemas:id')
            ->whereRaw('upper(code) = ?', [mb_strtoupper(trim($code))])
            ->first();

        if (! $coupon) {
            return ['error' => 'Mã voucher không tồn tại.'];
        }
        if ($coupon->status !== 'ISSUED') {
            return ['error' => 'Voucher đã được sử dụng hoặc không còn hiệu lực.'];
        }
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return ['error' => 'Voucher đã hết hạn.'];
        }
        if ($coupon->customer_id && (int) $coupon->customer_id !== (int) $booking->customer_id) {
            return ['error' => 'Voucher này không thuộc khách hàng hiện tại.'];
        }

        $promotion = $coupon->promotion;
        if (! $promotion || $promotion->status !== 'ACTIVE' || now()->lt($promotion->start_at) || now()->gt($promotion->end_at)) {
            return ['error' => 'Khuyến mãi của voucher hiện không khả dụng.'];
        }
        if ($promotion->min_order_amount && $subtotal < $promotion->min_order_amount) {
            return ['error' => 'Đơn hàng chưa đạt mức tối thiểu để dùng voucher.'];
        }
        if (! $this->matchesShowWindow($promotion, $show)) {
            return ['error' => 'Voucher không áp dụng cho suất chiếu này.'];
        }
        if (! $this->withinUsage($promotion, $booking->customer_id, $coupon)) {
            return ['error' => 'Voucher đã hết lượt sử dụng.'];
        }
        if (! $this->matchCustomerScope($promotion, $booking)) {
            return ['error' => 'Voucher không áp dụng cho khách hàng hiện tại.'];
        }

        return ['coupon' => $coupon, 'promotion' => $promotion];
    }

    public function discountAmount(Promotion $promotion, int $baseAmount): int
    {
        $discount = $promotion->promo_type === 'PERCENT'
            ? (int) round($baseAmount * ($promotion->discount_value / 100))
            : (int) $promotion->discount_value;

        if ($promotion->max_discount_amount) {
            $discount = min($discount, (int) $promotion->max_discount_amount);
        }

        return max(0, min($discount, $baseAmount));
    }

    public function persistDiscount(Booking $booking, Promotion $promotion, int $amount, ?Coupon $coupon = null, array $meta = []): void
    {
        if ($amount <= 0) {
            return;
        }

        BookingDiscount::create([
            'booking_id' => $booking->id,
            'promotion_id' => $promotion->id,
            'coupon_id' => $coupon?->id,
            'applied_to' => $promotion->applies_to,
            'discount_amount' => $amount,
            'metadata' => $meta,
            'created_at' => now(),
        ]);

        if ($coupon) {
            $coupon->update([
                'status' => 'REDEEMED',
                'redeemed_at' => now(),
            ]);
        }
    }

    private function withinUsage(Promotion $promotion, ?int $customerId, ?Coupon $coupon = null): bool
    {
        if ($coupon && $coupon->status === 'REDEEMED') {
            return false;
        }
        if ($promotion->usage_limit_total) {
            $used = BookingDiscount::query()
                ->where('promotion_id', $promotion->id)
                ->whereHas('booking', fn ($query) => $query->whereNotIn('status', ['CANCELLED', 'EXPIRED']))
                ->count();
            if ($used >= $promotion->usage_limit_total) {
                return false;
            }
        }
        if ($promotion->usage_limit_per_customer && $customerId) {
            $used = BookingDiscount::query()
                ->where('promotion_id', $promotion->id)
                ->whereHas('booking', fn ($query) => $query
                    ->where('customer_id', $customerId)
                    ->whereNotIn('status', ['CANCELLED', 'EXPIRED']))
                ->count();
            if ($used >= $promotion->usage_limit_per_customer) {
                return false;
            }
        }

        return true;
    }

    private function matchCustomerScope(Promotion $promotion, Booking $booking): bool
    {
        return match (strtoupper((string) ($promotion->customer_scope ?? 'ALL'))) {
            'NEW' => $this->isNewCustomer($booking),
            'MEMBER' => ! empty($booking->customer_id),
            default => true,
        };
    }

    private function matchesShowWindow(Promotion $promotion, Show $show, ?Carbon $start = null): bool
    {
        $start ??= $show->start_time instanceof Carbon ? $show->start_time : Carbon::parse($show->start_time);

        if ($promotion->day_of_week !== null && (int) $promotion->day_of_week !== (int) $start->dayOfWeekIso) {
            return false;
        }

        $time = $start->format('H:i:s');
        if ($promotion->show_start_from && $time < $promotion->show_start_from) {
            return false;
        }
        if ($promotion->show_start_to && $time > $promotion->show_start_to) {
            return false;
        }
        if ($promotion->movies->isNotEmpty() && ! $promotion->movies->contains('id', $show->movieVersion?->movie_id)) {
            return false;
        }
        if ($promotion->cinemas->isNotEmpty() && ! $promotion->cinemas->contains('id', $show->auditorium?->cinema_id)) {
            return false;
        }

        return true;
    }

    private function isNewCustomer(Booking $booking): bool
    {
        if (! $booking->customer_id) {
            return true;
        }

        $priorPaidBookings = Booking::query()
            ->where('customer_id', $booking->customer_id)
            ->where('id', '!=', $booking->id)
            ->whereIn('status', ['PAID', 'COMPLETED'])
            ->exists();

        return ! $priorPaidBookings;
    }
}
