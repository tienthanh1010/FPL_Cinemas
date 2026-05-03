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
        $ticketTypes = TicketType::query()->orderBy('id')->limit(1)->get();

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
                'pricing_profile_id' => 'Suất chiếu chưa gắn giá vé.',
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
            ->filter(fn (PricingRule $rule) => $this->matchesShow($rule, $start, $show))
            ->values();

        $base = $rules->first(fn (PricingRule $rule) => strtoupper((string) ($rule->rule_type ?? 'BASE')) === 'BASE');

        if (! $base) {
            throw ValidationException::withMessages([
                'pricing_profile_id' => 'Giá vé chưa có giá gốc BASE cho một số loại ghế/vé.',
            ]);
        }

        $price = (int) ($base->price_amount ?? 0);

        foreach ($rules as $rule) {
            if ((int) $base->id === (int) $rule->id) {
                continue;
            }

            $price = $this->applyAdjustment(
                $price,
                strtoupper((string) ($rule->price_mode ?? 'FIXED')),
                strtoupper((string) ($rule->rule_type ?? 'SURCHARGE')),
                (int) ($rule->adjustment_value ?? $rule->price_amount ?? 0),
                (int) ($rule->price_amount ?? $price)
            );
        }

        $price = $this->applyAutoAdjustments($price, $start);

        return max(0, $price);
    }

    private function applyAutoAdjustments(int $price, Carbon $start): int
    {
        foreach ((array) config('cinema_pricing.time_windows', []) as $window) {
            $days = array_map('intval', (array) ($window['days'] ?? []));
            if ($days !== [] && ! in_array((int) $start->dayOfWeekIso, $days, true)) {
                continue;
            }

            $time = $start->format('H:i:s');
            $startTime = (string) ($window['start'] ?? '00:00:00');
            $endTime = (string) ($window['end'] ?? '23:59:59');
            if ($time < $startTime || $time > $endTime) {
                continue;
            }

            $price = $this->applyAdjustment(
                $price,
                strtoupper((string) ($window['mode'] ?? 'AMOUNT_DELTA')),
                'SURCHARGE',
                (int) ($window['value'] ?? 0),
                (int) ($window['price_amount'] ?? $price)
            );
        }

        foreach ((array) config('cinema_pricing.holidays', []) as $holiday) {
            $from = Carbon::parse((string) ($holiday['from'] ?? $start->toDateString()))->toDateString();
            $to = Carbon::parse((string) ($holiday['to'] ?? $from))->toDateString();
            $date = $start->toDateString();
            if ($date < $from || $date > $to) {
                continue;
            }

            $price = $this->applyAdjustment(
                $price,
                strtoupper((string) ($holiday['mode'] ?? 'AMOUNT_DELTA')),
                'SURCHARGE',
                (int) ($holiday['value'] ?? 0),
                (int) ($holiday['price_amount'] ?? $price)
            );
        }

        return $price;
    }

    private function applyAdjustment(int $price, string $mode, string $ruleType, int $value, int $fixedPrice): int
    {
        if ($mode === 'FIXED') {
            return max(0, $fixedPrice);
        }

        if ($mode === 'AMOUNT_DELTA') {
            return max(0, $price + ($ruleType === 'DISCOUNT' ? -abs($value) : $value));
        }

        if ($mode === 'PERCENT_DELTA') {
            $delta = (int) round($price * (abs($value) / 100));
            return max(0, $price + ($ruleType === 'DISCOUNT' ? -$delta : $delta));
        }

        return max(0, $price);
    }

    private function matchesShow(PricingRule $rule, Carbon $start, Show $show): bool
    {
        $ruleName = (string) ($rule->rule_name ?? '');
        $roomPrefix = 'Điều chỉnh phòng:';
        if (str_starts_with($ruleName, $roomPrefix)) {
            $expectedScreenType = strtoupper(trim(str_replace($roomPrefix, '', $ruleName)));
            $actualScreenType = strtoupper((string) ($show->auditorium?->screen_type ?: 'STANDARD'));

            if ($expectedScreenType !== $actualScreenType) {
                return false;
            }
        }

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
