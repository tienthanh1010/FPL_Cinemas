<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Cinema;
use App\Models\PricingProfile;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\TicketType;
use App\Services\ShowPricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PricingProfileController extends Controller
{
    private const ROOM_RULE_PREFIX = 'Điều chỉnh phòng:';

    private const WEEKEND_RULE_PREFIX = 'Phụ thu cuối tuần';

    private const DEFAULT_SEAT_PRICES = [
        'REGULAR' => 50000,
        'VIP' => 70000,
        'COUPLE' => 90000,
        'SWEETBOX' => 90000,
    ];

    public function __construct(private readonly ShowPricingService $pricingService)
    {
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $profiles = PricingProfile::query()
            ->withCount('rules')
            ->with('cinema')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pricing_profiles.index', compact('profiles', 'q'));
    }

    public function create(): View
    {
        return view('admin.pricing_profiles.create', $this->formData(new PricingProfile()));
    }

    public function store(Request $request): RedirectResponse
    {
        [$profileData, $ruleRows] = $this->validatedPayload($request);

        $profile = DB::transaction(function () use ($profileData, $ruleRows) {
            $profile = PricingProfile::create($profileData);
            $this->replaceRules($profile, $ruleRows);

            return $profile;
        });

        return redirect()->route('admin.pricing_profiles.show', $profile)->with('success', 'Đã tạo giá vé.');
    }

    public function show(PricingProfile $pricingProfile): View
    {
        $pricingProfile->load(['cinema', 'rules.seatType', 'rules.ticketType']);

        return view('admin.pricing_profiles.show', [
            'profile' => $pricingProfile,
            'weekdays' => $this->weekdayLabels(),
        ]);
    }

    public function edit(PricingProfile $pricingProfile): View
    {
        $pricingProfile->load('rules');

        return view('admin.pricing_profiles.edit', $this->formData($pricingProfile));
    }

    public function update(Request $request, PricingProfile $pricingProfile): RedirectResponse
    {
        [$profileData, $ruleRows] = $this->validatedPayload($request, $pricingProfile);

        DB::transaction(function () use ($pricingProfile, $profileData, $ruleRows) {
            $pricingProfile->update($profileData);
            $this->replaceRules($pricingProfile, $ruleRows);
            $this->syncFutureShowPrices($pricingProfile);
        });

        return redirect()->route('admin.pricing_profiles.show', $pricingProfile)->with('success', 'Đã cập nhật giá vé.');
    }

    public function destroy(PricingProfile $pricingProfile): RedirectResponse
    {
        $pricingProfile->rules()->delete();
        $pricingProfile->delete();

        return redirect()->route('admin.pricing_profiles.index')->with('success', 'Đã xoá giá vé.');
    }

    private function formData(PricingProfile $profile): array
    {
        $profile->loadMissing('rules');

        $seatTypes = SeatType::query()->orderBy('id')->get();
        $ticketTypes = TicketType::query()->orderBy('id')->get();
        $auditoriums = Auditorium::query()->orderBy('id')->get();

        return [
            'profile' => $profile,
            'cinemas' => Cinema::query()->orderBy('name')->get(),
            'seatTypes' => $seatTypes,
            'ticketTypes' => $ticketTypes,
            'auditoriums' => $auditoriums,
            'roomTypes' => $this->roomTypes($auditoriums),
            'weekdays' => $this->weekdayLabels(),
            'baseSeatPrices' => old('base_prices', $this->baseSeatPrices($profile, $seatTypes)),
            'roomAdjustments' => old('room_adjustments', $this->roomAdjustments($profile, $auditoriums)),
            'weekendSurchargeAmount' => old('weekend_surcharge_amount', $this->weekendSurchargeAmount($profile)),
        ];
    }

    private function validatedPayload(Request $request, ?PricingProfile $profile = null): array
    {
        $validated = $request->validate([
            'cinema_id' => ['nullable', 'integer', 'exists:cinemas,id'],
            'code' => ['nullable', 'string', 'max:64', Rule::unique('pricing_profiles', 'code')->ignore($profile?->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'base_prices' => ['required', 'array'],
            'base_prices.*' => ['required', 'integer', 'min:0'],
            'room_adjustments' => ['nullable', 'array'],
            'room_adjustments.*.mode' => ['nullable', Rule::in(['NONE', 'SURCHARGE', 'DISCOUNT'])],
            'room_adjustments.*.amount' => ['nullable', 'integer', 'min:0'],
            'weekend_surcharge_amount' => ['nullable', 'integer', 'min:0'],
        ]) + [
            'code' => $request->input('code') ?: 'PRICE-' . strtoupper(Str::random(6)),
            'is_active' => $request->boolean('is_active', true),
        ];

        $profileData = [
            'cinema_id' => $validated['cinema_id'] ?? null,
            'code' => $validated['code'] ?: 'PRICE-' . strtoupper(Str::random(6)),
            'name' => $validated['name'],
            'is_active' => $request->boolean('is_active', true),
        ];


        $basePrices = collect($request->input('base_prices', []))
            ->mapWithKeys(fn ($value, $seatTypeId) => [(int) $seatTypeId => max(0, (int) $value)])
            ->all();

        $roomAdjustments = collect($request->input('room_adjustments', []))
            ->mapWithKeys(function ($row, $screenType) {
                $screenType = strtoupper(trim((string) $screenType));
                $mode = strtoupper(trim((string) ($row['mode'] ?? 'NONE')));
                $amount = max(0, (int) ($row['amount'] ?? 0));

                return [$screenType => [
                    'mode' => in_array($mode, ['SURCHARGE', 'DISCOUNT'], true) ? $mode : 'NONE',
                    'amount' => $amount,
                ]];
            })
            ->all();

        $weekendSurchargeAmount = max(0, (int) $request->input('weekend_surcharge_amount', 0));

        $ruleRows = $this->buildRuleRows($basePrices, $roomAdjustments, $weekendSurchargeAmount);

        return [$profileData, $ruleRows];
    }

    private function buildRuleRows(array $basePrices, array $roomAdjustments, int $weekendSurchargeAmount = 0): array
    {
        $seatTypes = SeatType::query()->orderBy('id')->get();
        $ticketTypes = $this->seatBasedTicketTypes();
        $rows = [];

        foreach ($seatTypes as $seatType) {
            $base = (int) ($basePrices[(int) $seatType->id] ?? $this->defaultBasePriceForSeatType($seatType));

            foreach ($ticketTypes as $ticketType) {
                $rows[] = [
                    'rule_name' => 'Giá vé ' . $seatType->name,
                    'rule_type' => 'BASE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'seat_type_id' => (int) $seatType->id,
                    'ticket_type_id' => (int) $ticketType->id,
                    'price_amount' => $base,
                    'price_mode' => 'FIXED',
                    'adjustment_value' => null,
                    'priority' => 100,
                    'is_active' => 1,
                ];
            }
        }

        foreach ($roomAdjustments as $screenType => $adjustment) {
            $screenType = strtoupper(trim((string) $screenType));
            $mode = strtoupper((string) ($adjustment['mode'] ?? 'NONE'));
            $amount = max(0, (int) ($adjustment['amount'] ?? 0));

            if ($screenType === '' || $screenType === 'STANDARD' || $mode === 'NONE' || $amount <= 0) {
                continue;
            }

            foreach ($seatTypes as $seatType) {
                foreach ($ticketTypes as $ticketType) {
                    $rows[] = [
                        'rule_name' => self::ROOM_RULE_PREFIX . $screenType,
                        'rule_type' => $mode,
                        'valid_from' => null,
                        'valid_to' => null,
                        'day_of_week' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'seat_type_id' => (int) $seatType->id,
                        'ticket_type_id' => (int) $ticketType->id,
                        'price_amount' => 0,
                        'price_mode' => 'AMOUNT_DELTA',
                        'adjustment_value' => $amount,
                        'priority' => 150,
                        'is_active' => 1,
                    ];
                }
            }
        }

        if ($weekendSurchargeAmount > 0) {
            foreach ([6, 7] as $dayOfWeek) {
                foreach ($seatTypes as $seatType) {
                    foreach ($ticketTypes as $ticketType) {
                        $rows[] = [
                            'rule_name' => self::WEEKEND_RULE_PREFIX,
                            'rule_type' => 'SURCHARGE',
                            'valid_from' => null,
                            'valid_to' => null,
                            'day_of_week' => $dayOfWeek,
                            'start_time' => null,
                            'end_time' => null,
                            'seat_type_id' => (int) $seatType->id,
                            'ticket_type_id' => (int) $ticketType->id,
                            'price_amount' => 0,
                            'price_mode' => 'AMOUNT_DELTA',
                            'adjustment_value' => $weekendSurchargeAmount,
                            'priority' => 200,
                            'is_active' => 1,
                        ];
                    }
                }
            }
        }

        return $rows;
    }

    private function baseSeatPrices(PricingProfile $profile, $seatTypes): array
    {
        $prices = [];

        foreach ($seatTypes as $seatType) {
            $rule = $profile->rules
                ->where('rule_type', 'BASE')
                ->where('seat_type_id', (int) $seatType->id)
                ->first();

            $prices[(int) $seatType->id] = (int) ($rule?->price_amount ?? $this->defaultBasePriceForSeatType($seatType));
        }

        return $prices;
    }

    private function weekendSurchargeAmount(PricingProfile $profile): int
    {
        $rule = $profile->rules->first(function ($rule) {
            return (string) $rule->rule_name === self::WEEKEND_RULE_PREFIX
                && strtoupper((string) ($rule->rule_type ?? '')) === 'SURCHARGE'
                && in_array((int) $rule->day_of_week, [6, 7], true);
        });

        return (int) ($rule?->adjustment_value ?? 0);
    }

    private function roomAdjustments(PricingProfile $profile, $auditoriums): array
    {
        $adjustments = [];

        foreach ($this->roomTypes($auditoriums) as $screenType => $label) {
            $rule = $profile->rules->first(function ($rule) use ($screenType) {
                return str_starts_with((string) $rule->rule_name, self::ROOM_RULE_PREFIX)
                    && strtoupper(trim(str_replace(self::ROOM_RULE_PREFIX, '', (string) $rule->rule_name))) === $screenType;
            });

            $adjustments[$screenType] = [
                'mode' => $screenType === 'STANDARD' ? 'NONE' : strtoupper((string) ($rule?->rule_type ?? 'NONE')),
                'amount' => (int) ($rule?->adjustment_value ?? 0),
            ];
        }

        return $adjustments;
    }

    private function roomTypes($auditoriums): array
    {
        return $auditoriums
            ->mapWithKeys(function ($auditorium) {
                $screenType = strtoupper((string) ($auditorium->screen_type ?: 'STANDARD'));
                return [$screenType => $screenType];
            })
            ->sortKeys()
            ->all();
    }

    private function defaultBasePriceForSeatType(SeatType $seatType): int
    {
        $code = strtoupper((string) $seatType->code);

        if (isset(self::DEFAULT_SEAT_PRICES[$code])) {
            return self::DEFAULT_SEAT_PRICES[$code];
        }

        return match ((int) $seatType->id) {
            1 => 50000,
            2 => 70000,
            3 => 90000,
            default => 50000,
        };
    }

    private function seatBasedTicketTypes()
    {
        return TicketType::query()
            ->orderBy('id')
            ->limit(1)
            ->get();
    }

    private function replaceRules(PricingProfile $profile, array $rows): void
    {
        $profile->rules()->delete();
        foreach ($rows as $row) {
            $profile->rules()->create($row);
        }
    }

    private function syncFutureShowPrices(PricingProfile $profile): void
    {
        Show::query()
            ->with(['pricingProfile', 'auditorium'])
            ->where('pricing_profile_id', $profile->id)
            ->where('start_time', '>=', now()->subMinutes(5))
            ->chunkById(50, function ($shows) {
                foreach ($shows as $show) {
                    $this->pricingService->syncShowPrices($show);
                }
            });
    }

    private function weekdayLabels(): array
    {
        return [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
    }
}
