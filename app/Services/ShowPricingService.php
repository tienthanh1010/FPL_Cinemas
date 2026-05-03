<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\ShowPrice;
use App\Models\TicketType;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class ShowPricingService
{
    public function syncShowPrices(Show $show): void
    {
        $profile = $show->pricingProfile;
        if (! $profile) {
            $show->prices()->delete();
            return;
        }

        $seatTypes = SeatType::query()->orderBy('id')->get();
        $ticketTypes = TicketType::query()->orderBy('id')->get();

        foreach ($seatTypes as $seatType) {
            foreach ($ticketTypes as $ticketType) {
                $amount = $this->resolvePriceForShow($show, (int) $seatType->id, (int) $ticketType->id);

                ShowPrice::updateOrCreate(
                    [
                        'show_id' => $show->id,
                        'seat_type_id' => $seatType->id,
                        'ticket_type_id' => $ticketType->id,
                    ],
                    [
                        'price_amount' => $amount,
                        'currency' => 'VND',
                        'is_active' => 1,
                    ]
                );
            }
        }
    }

    public function resolvePriceForShow(Show $show, int $seatTypeId, int $ticketTypeId): int
    {
        $profile = $show->pricingProfile;
        if (! $profile) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Suất chiếu chưa gắn hồ sơ giá.',
            ]);
        }

        $start = $show->start_time instanceof Carbon ? $show->start_time->copy() : Carbon::parse($show->start_time);

        $rules = PricingRule::query()
            ->where('pricing_profile_id', $profile->id)
            ->where('seat_type_id', $seatTypeId)
            ->where('ticket_type_id', $ticketTypeId)
            ->where('is_active', 1)
            ->orderBy('priority')
            ->orderBy('id')
            ->get()
            ->filter(fn (PricingRule $rule) => $this->matchesShow($rule, $start))
            ->values();

        $base = $rules->first(fn (PricingRule $rule) => strtoupper((string) ($rule->rule_type ?? 'BASE')) === 'BASE');

        if (! $base) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Hồ sơ giá chưa có giá gốc BASE cho một số loại ghế/vé.',
            ]);
        }

        $price = (int) ($base->price_amount ?? 0);

        foreach ($rules as $rule) {
            if ((int) $base->id === (int) $rule->id) {
                continue;
            }

            $mode = strtoupper((string) ($rule->price_mode ?? 'FIXED'));
            $ruleType = strtoupper((string) ($rule->rule_type ?? 'SURCHARGE'));
            $value = (int) ($rule->adjustment_value ?? $rule->price_amount ?? 0);

            if ($mode === 'FIXED') {
                $price = (int) ($rule->price_amount ?? $price);
                continue;
            }

            if ($mode === 'AMOUNT_DELTA') {
                $price += $ruleType === 'DISCOUNT' ? -abs($value) : $value;
                continue;
            }

            if ($mode === 'PERCENT_DELTA') {
                $delta = (int) round($price * (abs($value) / 100));
                $price += $ruleType === 'DISCOUNT' ? -$delta : $delta;
            }
        }

        return max(0, $price);
    }

    private function matchesShow(PricingRule $rule, Carbon $start): bool
    {
        if ($rule->valid_from && $start->toDateString() < $rule->valid_from->toDateString()) {
            return false;
        }

        if ($rule->valid_to && $start->toDateString() > $rule->valid_to->toDateString()) {
            return false;
        }

        if ($rule->day_of_week !== null && (int) $rule->day_of_week !== (int) $start->dayOfWeekIso) {
            return false;
        }

        $time = $start->format('H:i:s');
        if ($rule->start_time && $time < $rule->start_time) {
            return false;
        }

        if ($rule->end_time && $time > $rule->end_time) {
            return false;
        }

        return true;
    }
}
