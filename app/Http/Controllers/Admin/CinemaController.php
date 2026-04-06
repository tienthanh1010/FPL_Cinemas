<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\CinemaChain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CinemaController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (single_cinema_mode() && ($primaryCinema = $this->primaryCinema())) {
            return redirect()->route('admin.cinemas.show', $primaryCinema);
        }

        $q = trim((string) $request->get('q', ''));

        $cinemas = Cinema::query()
            ->with('auditoriums')
            ->with('auditoriums')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('cinema_code', 'like', "%{$q}%")
                    ->orWhere('province', 'like', "%{$q}%")
                    ->orWhere('district', 'like', "%{$q}%");
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cinemas.index', compact('cinemas', 'q'));
    }

    public function create(): View|RedirectResponse
    {
<<<<<<< HEAD
        if (single_cinema_mode() && ($primaryCinema = $this->primaryCinema())) {
            return redirect()
                ->route('admin.cinemas.edit', $primaryCinema)
                ->with('success', 'Hệ thống đang vận hành theo mô hình một rạp tuyệt đối. Bạn hãy cập nhật trực tiếp thông tin FPL Cinema.');
        }

        $cinema = new Cinema([
            'name' => 'FPL Cinema',
            'status' => 'ACTIVE',
            'timezone' => 'Asia/Ho_Chi_Minh',
            'country_code' => 'VN',
        ]);

=======
        $cinema = new Cinema();

        return view('admin.cinemas.create', $this->formData($cinema));
    }

    public function store(Request $request): RedirectResponse
    {
        if (Cinema::query()->count() >= 1) {
            return redirect()->route('admin.cinemas.index')->with('error', 'Hệ thống này chỉ quản lý 1 rạp duy nhất. Hãy chỉnh sửa rạp hiện có.');
        }

        $data = $this->validateData($request);
        $data['public_id'] = (string) Str::ulid();
        $data['chain_id'] = $this->resolveDefaultChainId();

        $cinema = Cinema::create($data);

        return redirect()->route('admin.cinemas.show', $cinema)->with('success', 'Đã tạo rạp.');
    }

    public function show(Cinema $cinema): View
    {
        $cinema->load('auditoriums');

        return view('admin.cinemas.show', compact('cinema'));
    }

    public function show(Cinema $cinema): View
    {
        return view('admin.cinemas.edit', $this->formData($cinema));
    }

    public function update(Request $request, Cinema $cinema): RedirectResponse
    {
        if ($redirect = $this->redirectToPrimaryCinema($cinema)) {
            return $redirect;
        }

        $data = $this->validateData($request, $cinema);
        $data['chain_id'] = $cinema->chain_id ?: $this->resolveDefaultChainId();

        $cinema->update($data);

        return redirect()->route('admin.cinemas.show', $cinema)->with('success', 'Đã cập nhật rạp.');
        return redirect()->route('admin.cinemas.show', $cinema)->with('success', 'Đã cập nhật rạp.');
    }

    public function destroy(Cinema $cinema): RedirectResponse
    {
        if (single_cinema_mode()) {
            return back()->with('error', 'Chế độ một rạp tuyệt đối không cho phép xoá FPL Cinema khỏi hệ thống.');
        }

        try {
            DB::transaction(function () use ($cinema) {
                $cinema->delete();
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Không thể xoá rạp (có thể đang được tham chiếu bởi dữ liệu khác).');
        }

        return back()->with('success', 'Đã xoá rạp.');
    }

    private function formData(Cinema $cinema): array
    {
        return [
            'cinema' => $cinema,
            'timezones' => admin_timezone_options(),
            'openingHourRows' => $this->resolveOpeningHourRows($cinema),
        ];
    }

    private function formData(Cinema $cinema): array
    {
        return [
            'cinema' => $cinema,
            'timezones' => admin_timezone_options(),
            'openingHourRows' => $this->resolveOpeningHourRows($cinema),
        ];
    }

    private function validateData(Request $request, ?Cinema $cinema = null): array
    {
        $data = $request->validate([
        $data = $request->validate([
            'cinema_code' => [
                'required',
                'string',
                'max:32',
                $cinema ? Rule::unique('cinemas', 'cinema_code')->ignore($cinema->id) : Rule::unique('cinemas', 'cinema_code'),
                $cinema ? Rule::unique('cinemas', 'cinema_code')->ignore($cinema->id) : Rule::unique('cinemas', 'cinema_code'),
            ],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:255'],
            'timezone' => ['required', Rule::in(array_keys(admin_timezone_options()))],
            'timezone' => ['required', Rule::in(array_keys(admin_timezone_options()))],
            'address_line' => ['nullable', 'string', 'max:255'],
            'ward' => ['nullable', 'string', 'max:128'],
            'district' => ['nullable', 'string', 'max:128'],
            'province' => ['nullable', 'string', 'max:128'],
            'country_code' => ['required', 'string', 'size:2'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'opening_hours_days' => ['nullable', 'array'],
            'opening_hours_days.*.enabled' => ['nullable', 'boolean'],
            'opening_hours_days.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours_days.*.close' => ['nullable', 'date_format:H:i'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'status' => ['required', Rule::in(['ACTIVE', 'INACTIVE'])],
            'opening_hours_days' => ['nullable', 'array'],
            'opening_hours_days.*.enabled' => ['nullable', 'boolean'],
            'opening_hours_days.*.open' => ['nullable', 'date_format:H:i'],
            'opening_hours_days.*.close' => ['nullable', 'date_format:H:i'],
        ]);

        $openingHours = [];
        $days = admin_opening_hour_day_labels();

        foreach ($days as $dayKey => $dayLabel) {
            $dayData = Arr::get($data, "opening_hours_days.{$dayKey}", []);
            $enabled = filter_var($dayData['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $open = trim((string) ($dayData['open'] ?? ''));
            $close = trim((string) ($dayData['close'] ?? ''));

            if (! $enabled && $open === '' && $close === '') {
                continue;
            }

            if ($enabled && ($open === '' || $close === '')) {
                throw ValidationException::withMessages([
                    "opening_hours_days.{$dayKey}.open" => "Hãy nhập đầy đủ giờ mở và đóng cho {$dayLabel}.",
                ]);
            }

            if ($enabled && $open >= $close) {
                throw ValidationException::withMessages([
                    "opening_hours_days.{$dayKey}.close" => "Giờ đóng của {$dayLabel} phải lớn hơn giờ mở.",
                ]);
            }

            if ($enabled) {
                $openingHours[$dayKey] = "{$open}-{$close}";
            }
        }

        unset($data['opening_hours_days']);

        $data['opening_hours'] = empty($openingHours) ? null : $openingHours;
        $data['phone'] = $this->nullableString($data['phone'] ?? null);
        $data['address_line'] = $this->nullableString($data['address_line'] ?? null);
        $data['ward'] = $this->nullableString($data['ward'] ?? null);
        $data['district'] = $this->nullableString($data['district'] ?? null);
        $data['province'] = $this->nullableString($data['province'] ?? null);
        $data['country_code'] = strtoupper(trim((string) $data['country_code']));

        $openingHours = [];
        $days = admin_opening_hour_day_labels();

        foreach ($days as $dayKey => $dayLabel) {
            $dayData = Arr::get($data, "opening_hours_days.{$dayKey}", []);
            $enabled = filter_var($dayData['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $open = trim((string) ($dayData['open'] ?? ''));
            $close = trim((string) ($dayData['close'] ?? ''));

            if (! $enabled && $open === '' && $close === '') {
                continue;
            }

            if ($enabled && ($open === '' || $close === '')) {
                throw ValidationException::withMessages([
                    "opening_hours_days.{$dayKey}.open" => "Hãy nhập đầy đủ giờ mở và đóng cho {$dayLabel}.",
                ]);
            }

            if ($enabled && $open >= $close) {
                throw ValidationException::withMessages([
                    "opening_hours_days.{$dayKey}.close" => "Giờ đóng của {$dayLabel} phải lớn hơn giờ mở.",
                ]);
            }

            if ($enabled) {
                $openingHours[$dayKey] = "{$open}-{$close}";
            }
        }

        unset($data['opening_hours_days']);

        $data['opening_hours'] = empty($openingHours) ? null : $openingHours;
        $data['phone'] = $this->nullableString($data['phone'] ?? null);
        $data['address_line'] = $this->nullableString($data['address_line'] ?? null);
        $data['ward'] = $this->nullableString($data['ward'] ?? null);
        $data['district'] = $this->nullableString($data['district'] ?? null);
        $data['province'] = $this->nullableString($data['province'] ?? null);
        $data['country_code'] = strtoupper(trim((string) $data['country_code']));

        return $data;
    }

    private function resolveOpeningHourRows(Cinema $cinema): array
    {
        $existing = $cinema->opening_hours ?? [];
        $rows = [];

        foreach (admin_opening_hour_day_labels() as $key => $label) {
            $value = (string) ($existing[$key] ?? '');
            $open = '';
            $close = '';
            if ($value !== '' && str_contains($value, '-')) {
                [$open, $close] = explode('-', $value, 2);
            }

            $rows[$key] = [
                'label' => $label,
                'enabled' => old("opening_hours_days.{$key}.enabled", $value !== '' ? '1' : '0'),
                'open' => old("opening_hours_days.{$key}.open", $open),
                'close' => old("opening_hours_days.{$key}.close", $close),
            ];
        }

        return $rows;
    }

    private function resolveDefaultChainId(): int
    {
        $chain = CinemaChain::query()->first();

        if (! $chain) {
            $chain = CinemaChain::query()->create([
                'public_id' => (string) Str::ulid(),
                'chain_code' => 'single-cinema',
                'name' => 'Rạp duy nhất',
                'status' => 'ACTIVE',
            ]);
        }

        return (int) $chain->id;
    }

    private function nullableString(mixed $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;

        return $value === '' || $value === null ? null : (string) $value;
    }
}
