<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;

class LoyaltyPointService
{
    private const BOOKING_PAID_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function enabled(): bool
    {
        return (bool) config('loyalty.enabled', true);
    }

    public function previewPoints(int $amount): int
    {
        if (! $this->enabled()) {
            return 0;
        }

        $amountPerPoint = max(1, (int) config('loyalty.amount_per_point', 10000));

        return max(0, (int) floor(max(0, $amount) / $amountPerPoint));
    }

    public function ensureAccount(Customer $customer): LoyaltyAccount
    {
        $account = LoyaltyAccount::query()->firstOrCreate(
            ['customer_id' => $customer->id],
            [
                'tier_id' => null,
                'points_balance' => 0,
                'lifetime_points' => 0,
            ]
        );

        $this->ensureDefaultTiers();
        $this->recalculateAccount($account->fresh());

        return $account->fresh();
    }

    public function syncForBooking(?Booking $booking): void
    {
        if (! $this->enabled() || ! $booking || ! $booking->customer_id) {
            return;
        }

        $booking->loadMissing(['customer.loyaltyAccount']);

        $customer = $booking->customer;
        if (! $customer) {
            return;
        }

        $account = $this->ensureAccount($customer);
        $targetPoints = $this->targetPointsForBooking($booking);

        if ($targetPoints <= 0) {
            LoyaltyTransaction::query()
                ->where('loyalty_account_id', $account->id)
                ->where('reference_type', 'BOOKING')
                ->where('reference_id', $booking->id)
                ->delete();

            $this->recalculateAccount($account->fresh());
            return;
        }

        LoyaltyTransaction::query()->updateOrCreate(
            [
                'loyalty_account_id' => $account->id,
                'reference_type' => 'BOOKING',
                'reference_id' => $booking->id,
            ],
            [
                'txn_type' => 'EARN',
                'points' => $targetPoints,
                'note' => 'Tích điểm từ booking ' . $booking->booking_code,
                'created_at' => now(),
            ]
        );

        $this->recalculateAccount($account->fresh());
    }

    public function targetPointsForBooking(Booking $booking): int
    {
        if (! in_array((string) $booking->status, self::BOOKING_PAID_STATUSES, true)) {
            return 0;
        }

        $eligibleAmount = min((int) $booking->paid_amount, (int) $booking->total_amount);

        if ($eligibleAmount <= 0) {
            return 0;
        }

        return $this->previewPoints($eligibleAmount);
    }

    public function ensureDefaultTiers(): void
    {
        if (! class_exists(LoyaltyTier::class) || ! \Schema::hasTable('loyalty_tiers')) {
            return;
        }

        $tiers = [
            ['code' => 'MEMBER', 'name' => 'Member', 'min_points' => 0],
            ['code' => 'SILVER', 'name' => 'Silver', 'min_points' => 100],
            ['code' => 'GOLD', 'name' => 'Gold', 'min_points' => 300],
            ['code' => 'PLATINUM', 'name' => 'Platinum', 'min_points' => 700],
        ];

        foreach ($tiers as $tier) {
            LoyaltyTier::query()->updateOrCreate(
                ['code' => $tier['code']],
                $tier + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function recalculateAccount(LoyaltyAccount $account): void
    {
        $transactions = LoyaltyTransaction::query()
            ->where('loyalty_account_id', $account->id)
            ->get();

        $pointsBalance = (int) $transactions->sum('points');
        $lifetimePoints = (int) $transactions->where('points', '>', 0)->sum('points');

        $tierId = LoyaltyTier::query()
            ->where('min_points', '<=', max($pointsBalance, $lifetimePoints))
            ->orderByDesc('min_points')
            ->value('id');

        $account->update([
            'points_balance' => $pointsBalance,
            'lifetime_points' => $lifetimePoints,
            'tier_id' => $tierId,
        ]);
    }
}
