<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\PricingProfile;
use App\Models\SeatType;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PricingProfileController extends Controller
{
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

        return redirect()->route('admin.pricing_profiles.show', $profile)->with('success', 'Đã tạo hồ sơ giá.');
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
        });

        return redirect()->route('admin.pricing_profiles.show', $pricingProfile)->with('success', 'Đã cập nhật hồ sơ giá.');
    }

    public function destroy(PricingProfile $pricingProfile): RedirectResponse
    {
        $pricingProfile->rules()->delete();
        $pricingProfile->delete();

        return redirect()->route('admin.pricing_profiles.index')->with('success', 'Đã xoá hồ sơ giá.');
    }

    private function formData(PricingProfile $profile): array
    {
        $profile->loadMissing('rules');

        return [
            'profile' => $profile,
            'cinemas' => Cinema::query()->orderBy('name')->get(),
            'seatTypes' => SeatType::query()->orderBy('id')->get(),
            'ticketTypes' => TicketType::query()->orderBy('id')->get(),
            'weekdays' => $this->weekdayLabels(),
            'ruleRows' => old('rules', $profile->rules->map(function ($rule) {
                return [
                    'rule_name' => $rule->rule_name,
                    'rule_type' => strtoupper((string) ($rule->rule_type ?? 'BASE')),
                    'valid_from' => optional($rule->valid_from)->format('Y-m-d'),
                    'valid_to' => optional($rule->valid_to)->format('Y-m-d'),
                    'day_of_week' => $rule->day_of_week,
                    'start_time' => $rule->start_time,
                    'end_time' => $rule->end_time,
                    'seat_type_id' => $rule->seat_type_id,
                    'ticket_type_id' => $rule->ticket_type_id,
                    'price_amount' => $rule->price_amount,
                    'price_mode' => strtoupper((string) ($rule->price_mode ?? 'FIXED')),
                    'adjustment_value' => $rule->adjustment_value,
                    'priority' => $rule->priority,
                    'is_active' => $rule->is_active ? 1 : 0,
                ];
            })->values()->all() ?: $this->defaultRules()),
        ];
    }

    private function validatedPayload(Request $request, ?PricingProfile $profile = null): array
    {
        $profileData = $request->validate([
            'cinema_id' => ['nullable', 'integer', 'exists:cinemas,id'],
            'code' => ['nullable', 'string', 'max:64', Rule::unique('pricing_profiles', 'code')->ignore($profile?->id)],
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'code' => $request->input('code') ?: 'PROFILE-' . strtoupper(Str::random(6)),
            'is_active' => $request->boolean('is_active', true),
        ];

        $seatTypeIds = SeatType::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $ticketTypeIds = TicketType::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $allowedRuleTypes = ['BASE', 'SURCHARGE', 'DISCOUNT'];
        $allowedPriceModes = ['FIXED', 'AMOUNT_DELTA', 'PERCENT_DELTA'];

        $rows = collect($request->input('rules', []))
            ->map(fn ($row) => is_array($row) ? $row : null)
            ->filter()
            ->values()
            ->map(function (array $row) {
                return [
                    'rule_name' => $this->nullableString($row['rule_name'] ?? null),
                    'rule_type' => strtoupper(trim((string) ($row['rule_type'] ?? 'BASE'))),
                    'valid_from' => $this->nullableString($row['valid_from'] ?? null),
                    'valid_to' => $this->nullableString($row['valid_to'] ?? null),
                    'day_of_week' => $this->normalizeIsoWeekday($row['day_of_week'] ?? null),
                    'start_time' => $this->normalizeTimeValue($row['start_time'] ?? null),
                    'end_time' => $this->normalizeTimeValue($row['end_time'] ?? null),
                    'seat_type_id' => blank($row['seat_type_id'] ?? null) ? null : (int) $row['seat_type_id'],
                    'ticket_type_id' => blank($row['ticket_type_id'] ?? null) ? null : (int) $row['ticket_type_id'],
                    'price_amount' => blank($row['price_amount'] ?? null) ? null : max(0, (int) $row['price_amount']),
                    'price_mode' => strtoupper(trim((string) ($row['price_mode'] ?? 'FIXED'))),
                    'adjustment_value' => blank($row['adjustment_value'] ?? null) ? null : (int) $row['adjustment_value'],
                    'priority' => blank($row['priority'] ?? null) ? 100 : max(1, (int) $row['priority']),
                    'is_active' => ! empty($row['is_active']) ? 1 : 0,
                ];
            })
            ->filter(fn ($row) => $row['seat_type_id'] !== null || $row['ticket_type_id'] !== null || $row['price_amount'] !== null || $row['adjustment_value'] !== null)
            ->values();

        if ($rows->isEmpty()) {
            throw ValidationException::withMessages([
                'rules' => 'Hồ sơ giá phải có ít nhất 1 rule.',
            ]);
        }

        $errors = [];
        $baseSeen = [];
        $rows = $rows->map(function ($row, $index) use (&$errors, &$baseSeen, $seatTypeIds, $ticketTypeIds, $allowedRuleTypes, $allowedPriceModes) {
            $humanIndex = $index + 1;

            if ($row['seat_type_id'] === null || ! in_array($row['seat_type_id'], $seatTypeIds, true)) {
                $errors["rules.$index.seat_type_id"] = "Rule #{$humanIndex}: loại ghế không hợp lệ.";
            }
            if ($row['ticket_type_id'] === null || ! in_array($row['ticket_type_id'], $ticketTypeIds, true)) {
                $errors["rules.$index.ticket_type_id"] = "Rule #{$humanIndex}: đối tượng vé không hợp lệ.";
            }
            if (! in_array($row['rule_type'], $allowedRuleTypes, true)) {
                $errors["rules.$index.rule_type"] = "Rule #{$humanIndex}: loại rule không hợp lệ.";
            }
            if (! in_array($row['price_mode'], $allowedPriceModes, true)) {
                $errors["rules.$index.price_mode"] = "Rule #{$humanIndex}: kiểu giá không hợp lệ.";
            }
            if ($row['day_of_week'] !== null && ($row['day_of_week'] < 1 || $row['day_of_week'] > 7)) {
                $errors["rules.$index.day_of_week"] = "Rule #{$humanIndex}: thứ trong tuần phải từ Thứ 2 (1) đến Chủ nhật (7).";
            }
            if ($row['valid_from'] && $row['valid_to'] && $row['valid_from'] > $row['valid_to']) {
                $errors["rules.$index.valid_to"] = "Rule #{$humanIndex}: ngày kết thúc phải sau hoặc bằng ngày bắt đầu.";
            }
            if ($row['start_time'] && $row['end_time'] && $row['start_time'] >= $row['end_time']) {
                $errors["rules.$index.end_time"] = "Rule #{$humanIndex}: giờ kết thúc phải sau giờ bắt đầu.";
            }

            if ($row['price_mode'] === 'FIXED') {
                if ($row['price_amount'] === null) {
                    $errors["rules.$index.price_amount"] = "Rule #{$humanIndex}: giá cố định là bắt buộc.";
                }
                $row['adjustment_value'] = null;
            } else {
                if ($row['adjustment_value'] === null) {
                    $errors["rules.$index.adjustment_value"] = "Rule #{$humanIndex}: phải nhập giá trị điều chỉnh cho rule cộng/trừ.";
                }
                if ($row['price_amount'] === null) {
                    $row['price_amount'] = 0;
                }
            }

            if ($row['rule_type'] === 'BASE') {
                $baseKey = $row['seat_type_id'] . ':' . $row['ticket_type_id'];
                if (isset($baseSeen[$baseKey])) {
                    $errors["rules.$index.rule_type"] = "Rule #{$humanIndex}: mỗi cặp ghế/vé chỉ nên có 1 giá gốc BASE.";
                }
                $baseSeen[$baseKey] = true;
                $row['price_mode'] = 'FIXED';
                $row['adjustment_value'] = null;
                if (($row['price_amount'] ?? 0) <= 0) {
                    $errors["rules.$index.price_amount"] = "Rule #{$humanIndex}: giá gốc BASE phải lớn hơn 0.";
                }
            }

            return $row;
        });

<<<<<<< HEAD
        $rows = $this->appendMissingBaseRules($rows, $baseSeen, $seatTypeIds, $ticketTypeIds);
=======
<<<<<<< HEAD
        $rows = $this->appendMissingBaseRules($rows, $baseSeen, $seatTypeIds, $ticketTypeIds);
=======
        $missingBase = [];
        foreach ($seatTypeIds as $seatTypeId) {
            foreach ($ticketTypeIds as $ticketTypeId) {
                $key = $seatTypeId . ':' . $ticketTypeId;
                if (! isset($baseSeen[$key])) {
                    $missingBase[] = "ghế {$seatTypeId} / vé {$ticketTypeId}";
                }
            }
        }
        if ($missingBase !== []) {
            $errors['rules'] = 'Thiếu rule BASE cho: ' . implode(', ', $missingBase) . '.';
        }
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        return [$profileData, $rows->values()->all()];
    }


    private function appendMissingBaseRules(\Illuminate\Support\Collection $rows, array $baseSeen, array $seatTypeIds, array $ticketTypeIds): \Illuminate\Support\Collection
    {
        foreach ($seatTypeIds as $seatTypeId) {
            foreach ($ticketTypeIds as $ticketTypeId) {
                $key = $seatTypeId . ':' . $ticketTypeId;
                if (isset($baseSeen[$key])) {
                    continue;
                }

                $rows->push([
                    'rule_name' => 'Giá gốc (tự bổ sung)',
                    'rule_type' => 'BASE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'seat_type_id' => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount' => $this->defaultBasePrice($seatTypeId, $ticketTypeId),
                    'price_mode' => 'FIXED',
                    'adjustment_value' => null,
                    'priority' => 100,
                    'is_active' => 1,
                ]);
            }
        }

        return $rows;
    }

    private function defaultBasePrice(int $seatTypeId, int $ticketTypeId): int
    {
        return match ([$seatTypeId, $ticketTypeId]) {
            [1, 1] => 75000,
            [1, 2] => 65000,
            [1, 3] => 55000,
            [2, 1] => 95000,
            [2, 2] => 85000,
            [2, 3] => 75000,
            default => 150000,
        };
<<<<<<< HEAD
=======
=======
        return [$profileData, $rows->all()];
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    }

    private function replaceRules(PricingProfile $profile, array $rows): void
    {
        $profile->rules()->delete();
        foreach ($rows as $row) {
            $profile->rules()->create($row);
        }
    }

    private function defaultRules(): array
    {
        $rows = [];
        $seatTypeIds = SeatType::query()->pluck('id')->map(fn ($id) => (int) $id)->all();
        $ticketTypeIds = TicketType::query()->pluck('id')->map(fn ($id) => (int) $id)->all();

        foreach ($seatTypeIds as $seatTypeId) {
            foreach ($ticketTypeIds as $ticketTypeId) {
                $base = match ([$seatTypeId, $ticketTypeId]) {
                    [1, 1] => 75000,
                    [1, 2] => 65000,
                    [1, 3] => 55000,
                    [2, 1] => 95000,
                    [2, 2] => 85000,
                    [2, 3] => 75000,
                    default => 150000,
                };

                $rows[] = [
                    'rule_name' => 'Giá gốc',
                    'rule_type' => 'BASE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => null,
                    'start_time' => null,
                    'end_time' => null,
                    'seat_type_id' => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount' => $base,
                    'price_mode' => 'FIXED',
                    'adjustment_value' => null,
                    'priority' => 100,
                    'is_active' => 1,
                ];
                $rows[] = [
                    'rule_name' => 'Phụ thu cuối tuần',
                    'rule_type' => 'SURCHARGE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => 6,
                    'start_time' => null,
                    'end_time' => null,
                    'seat_type_id' => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount' => 0,
                    'price_mode' => 'AMOUNT_DELTA',
                    'adjustment_value' => 15000,
                    'priority' => 200,
                    'is_active' => 1,
                ];
                $rows[] = [
                    'rule_name' => 'Phụ thu Chủ nhật',
                    'rule_type' => 'SURCHARGE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => 7,
                    'start_time' => null,
                    'end_time' => null,
                    'seat_type_id' => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount' => 0,
                    'price_mode' => 'AMOUNT_DELTA',
                    'adjustment_value' => 15000,
                    'priority' => 201,
                    'is_active' => 1,
                ];
                $rows[] = [
                    'rule_name' => 'Suất tối',
                    'rule_type' => 'SURCHARGE',
                    'valid_from' => null,
                    'valid_to' => null,
                    'day_of_week' => null,
                    'start_time' => '18:00:00',
                    'end_time' => '23:59:59',
                    'seat_type_id' => $seatTypeId,
                    'ticket_type_id' => $ticketTypeId,
                    'price_amount' => 0,
                    'price_mode' => 'AMOUNT_DELTA',
                    'adjustment_value' => 10000,
                    'priority' => 210,
                    'is_active' => 1,
                ];
            }
        }

        return $rows;
    }

    private function weekdayLabels(): array
    {
        return [1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7', 7 => 'Chủ nhật'];
    }

    private function normalizeIsoWeekday(mixed $value): ?int
    {
        if ($value === '' || $value === null) {
            return null;
        }

        $value = (int) $value;

        return $value === 0 ? 7 : $value;
    }

    private function normalizeTimeValue(mixed $value): ?string
    {
        $value = $this->nullableString($value);
        if ($value === null) {
            return null;
        }

        return strlen($value) === 5 ? $value . ':00' : $value;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }
}
