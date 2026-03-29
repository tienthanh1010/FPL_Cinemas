<?php

namespace Database\Seeders;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Promotion;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $now = now();

        $promotions = [
            [
                'code' => 'VIP50',
                'name' => 'Giảm 50% cho thành viên',
                'description' => 'Giảm 50% khi mua vé, áp dụng cho khách VIP Aurora',
                'promo_type' => 'PERCENT',
                'discount_value' => 50,
                'max_discount_amount' => 50000,
                'min_order_amount' => 100000,
                'applies_to' => 'TICKET',
                'is_stackable' => true,
                'usage_limit_total' => 1000,
                'usage_limit_per_customer' => 2,
                'status' => 'ACTIVE',
                'customer_scope' => 'VIP',
                'auto_apply' => true,
                'coupon_required' => false,
            ],
            [
                'code' => 'VIP100K',
                'name' => 'Giảm 100K cho đơn VIP',
                'description' => 'Giảm thẳng 100.000đ cho đơn hàng trên 300.000đ',
                'promo_type' => 'FIXED',
                'discount_value' => 100000,
                'max_discount_amount' => 100000,
                'min_order_amount' => 300000,
                'applies_to' => 'ORDER',
                'is_stackable' => false,
                'usage_limit_total' => 500,
                'usage_limit_per_customer' => 1,
                'status' => 'ACTIVE',
                'customer_scope' => 'VIP',
                'auto_apply' => true,
                'coupon_required' => false,
            ],
            [
                'code' => 'COMBO20',
                'name' => 'Giảm 20% combo suất chiếu',
                'description' => 'Giảm 20% khi mua combo vé + đồ ăn',
                'promo_type' => 'PERCENT',
                'discount_value' => 20,
                'max_discount_amount' => 80000,
                'min_order_amount' => 150000,
                'applies_to' => 'PRODUCT',
                'is_stackable' => true,
                'usage_limit_total' => 800,
                'usage_limit_per_customer' => 2,
                'status' => 'ACTIVE',
                'customer_scope' => 'VIP',
                'auto_apply' => false,
                'coupon_required' => true,
            ],
            [
                'code' => 'COUPONVIP20',
                'name' => 'Mã coupon VIP20',
                'description' => 'Nhập COUPONVIP20 để được giảm 20% (max 70k)',
                'promo_type' => 'PERCENT',
                'discount_value' => 20,
                'max_discount_amount' => 70000,
                'min_order_amount' => 120000,
                'applies_to' => 'TICKET',
                'is_stackable' => false,
                'usage_limit_total' => 1000,
                'usage_limit_per_customer' => 1,
                'status' => 'ACTIVE',
                'customer_scope' => 'VIP',
                'auto_apply' => false,
                'coupon_required' => true,
            ],
        ];

        $cinemaIds = Cinema::query()->pluck('id')->toArray();
        $movieIds = Movie::query()->limit(5)->pluck('id')->toArray();

        foreach ($promotions as $promoData) {
            $promotion = Promotion::updateOrCreate(
                ['code' => $promoData['code']],
                array_merge($promoData, [
                    'start_at' => $now->subDay()->toDateTimeString(),
                    'end_at' => $now->addDays(14)->toDateTimeString(),
                ])
            );

            if (count($cinemaIds)) {
                $promotion->cinemas()->sync($cinemaIds);
            }

            if (count($movieIds)) {
                $promotion->movies()->sync($movieIds);
            }

            $this->command->info('Promotion ' . $promotion->code . ' seeded successfully.');
        }
    }
}
