<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function index(Request $request): View
    {
        $promotionId = $request->integer('promotion_id');
        $coupons = Coupon::query()
            ->with('promotion')
            ->when($promotionId, fn ($q) => $q->where('promotion_id', $promotionId))
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'promotions' => Promotion::query()->orderBy('name')->get(),
            'promotionId' => $promotionId,
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create', [
            'promotions' => Promotion::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'promotion_id' => ['required', 'integer', 'exists:promotions,id'],
            'code' => ['nullable', 'string', 'max:64', Rule::unique('coupons', 'code')],
            'expires_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:ISSUED,ACTIVE,REDEEMED,EXPIRED'],
        ]);

        Coupon::create([
            'promotion_id' => $data['promotion_id'],
            'code' => $data['code'] ?: 'VC' . strtoupper(Str::random(8)),
            'status' => $data['status'] ?? 'ISSUED',
            'issued_at' => now(),
            'expires_at' => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.coupons.index')->with('success', 'Đã tạo voucher.');
    }
}
