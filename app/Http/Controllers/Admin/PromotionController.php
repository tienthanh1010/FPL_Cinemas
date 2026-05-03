<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $promotions = Promotion::query()
            ->withCount(['coupons', 'bookingDiscounts'])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('name', 'like', "%{$q}%")
                        ->orWhere('code', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'active' => Promotion::query()->where('status', 'ACTIVE')->count(),
            'voucher_count' => Promotion::query()->where('coupon_required', 1)->count(),
            'discount_total' => (int) DB::table('booking_discounts')->sum('discount_amount'),
            'auto_apply_count' => Promotion::query()->where('auto_apply', 1)->count(),
        ];

        return view('admin.promotions.index', compact('promotions', 'q', 'summary'));
    }

    public function create(): View
    {
        return view('admin.promotions.create', $this->formData(new Promotion()));
    }

    public function store(Request $request): RedirectResponse
    {
        $payload = $this->validatePromotion($request);

        $promotion = DB::transaction(function () use ($payload, $request) {
            $promotion = Promotion::create($payload);
            $promotion->movies()->sync($request->input('movie_ids', []));
            $promotion->cinemas()->sync($request->input('cinema_ids', []));

            return $promotion;
        });

        return redirect()->route('admin.promotions.show', $promotion)->with('success', 'Đã tạo khuyến mãi.');
    }

    public function show(Promotion $promotion): View
    {
        $promotion->load(['movies', 'cinemas', 'coupons', 'bookingDiscounts']);
        $effectiveness = [
            'used_count' => $promotion->bookingDiscounts->count(),
            'discount_total' => (int) $promotion->bookingDiscounts->sum('discount_amount'),
            'coupon_issued' => $promotion->coupons->count(),
            'coupon_redeemed' => $promotion->coupons->where('status', 'REDEEMED')->count(),
        ];

        return view('admin.promotions.show', compact('promotion', 'effectiveness'));
    }

    public function edit(Promotion $promotion): View
    {
        $promotion->load(['movies:id', 'cinemas:id']);

        return view('admin.promotions.edit', $this->formData($promotion));
    }

    public function update(Request $request, Promotion $promotion): RedirectResponse
    {
        $payload = $this->validatePromotion($request, $promotion);

        DB::transaction(function () use ($promotion, $payload, $request) {
            $promotion->update($payload);
            $promotion->movies()->sync($request->input('movie_ids', []));
            $promotion->cinemas()->sync($request->input('cinema_ids', []));
        });

        return redirect()->route('admin.promotions.show', $promotion)->with('success', 'Đã cập nhật khuyến mãi.');
    }

    public function destroy(Promotion $promotion): RedirectResponse
    {
        $promotion->movies()->detach();
        $promotion->cinemas()->detach();
        $promotion->delete();

        return redirect()->route('admin.promotions.index')->with('success', 'Đã xoá khuyến mãi.');
    }

    private function formData(Promotion $promotion): array
    {
        return [
            'promotion' => $promotion,
            'movies' => Movie::query()->orderBy('title')->get(),
            'cinemas' => Cinema::query()->orderBy('name')->get(),
            'scopes' => ['ALL' => 'Tất cả', 'NEW' => 'Khách mới', 'MEMBER' => 'Khách thành viên'],
            'weekdays' => [1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7', 7 => 'CN'],
        ];
    }

    private function validatePromotion(Request $request, ?Promotion $promotion = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64', Rule::unique('promotions', 'code')->ignore($promotion?->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'promo_type' => ['required', 'in:AMOUNT,PERCENT'],
            'discount_value' => ['required', 'integer', 'min:1'],
            'max_discount_amount' => ['nullable', 'integer', 'min:0'],
            'min_order_amount' => ['nullable', 'integer', 'min:0'],
            'applies_to' => ['required', 'in:ORDER,TICKET,PRODUCT'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'usage_limit_total' => ['nullable', 'integer', 'min:1'],
            'usage_limit_per_customer' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'in:ACTIVE,INACTIVE'],
            'day_of_week' => ['nullable', 'integer', 'between:1,7'],
            'show_start_from' => ['nullable', 'date_format:H:i'],
            'show_start_to' => ['nullable', 'date_format:H:i', 'after:show_start_from'],
            'customer_scope' => ['nullable', 'in:ALL,NEW,MEMBER'],
            'movie_ids' => ['nullable', 'array'],
            'movie_ids.*' => ['integer', 'exists:movies,id'],
            'cinema_ids' => ['nullable', 'array'],
            'cinema_ids.*' => ['integer', 'exists:cinemas,id'],
        ]) + [
            'is_stackable' => $request->boolean('is_stackable'),
            'auto_apply' => $request->boolean('auto_apply'),
            'coupon_required' => $request->boolean('coupon_required'),
            'customer_scope' => $request->input('customer_scope') ?: 'ALL',
            'show_start_from' => $this->normalizeTimeValue($request->input('show_start_from')),
            'show_start_to' => $this->normalizeTimeValue($request->input('show_start_to')),
            'day_of_week' => $request->filled('day_of_week') ? (int) $request->input('day_of_week') : null,
        ];

        if ($data['promo_type'] === 'PERCENT' && $data['discount_value'] > 100) {
            throw ValidationException::withMessages([
                'discount_value' => 'Khuyến mãi phần trăm không được vượt quá 100%.',
            ]);
        }

        if ($data['coupon_required'] && $data['auto_apply']) {
            throw ValidationException::withMessages([
                'coupon_required' => 'Khuyến mãi yêu cầu voucher thì không nên bật tự áp dụng.',
            ]);
        }

        return $data;
    }

    private function normalizeTimeValue(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : $value;
        if ($value === '' || $value === null) {
            return null;
        }

        return strlen($value) === 5 ? $value . ':00' : $value;
    }
}
