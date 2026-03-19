@extends('admin.layout')
@section('title', 'Chi tiết sản phẩm F&B')
@section('content')
<section class="page-header"><div><p class="eyebrow">F&B detail</p><h2>{{ $product->name }}</h2><p>{{ $product->sku }} · {{ $product->category?->name }}</p></div><div class="d-flex gap-2"><a href="{{ route('admin.products.edit', $product) }}" class="btn btn-primary">Sửa</a><a href="{{ route('admin.products.index') }}" class="btn btn-light-soft">Quay lại</a></div></section>
<div class="row g-3">
<div class="col-lg-4"><div class="card h-100"><div class="card-body"><div class="fw-semibold mb-2">Thông tin nhanh</div><div><strong>Loại:</strong> {{ $product->is_combo ? 'Combo' : 'Sản phẩm lẻ' }}</div><div><strong>Giá hiện hành:</strong> {{ $currentPrice ? number_format($currentPrice->price_amount).'đ' : 'Chưa có' }}</div><div><strong>Đã bán:</strong> {{ number_format($soldQty) }}</div><div><strong>Doanh thu:</strong> {{ number_format($revenue) }}đ</div><div class="mt-3">{{ $product->attributes['description'] ?? 'Chưa có mô tả' }}</div></div></div></div>
<div class="col-lg-4"><div class="card h-100"><div class="card-body"><div class="fw-semibold mb-2">Tồn kho theo quầy/kho</div>@forelse($product->inventoryBalances as $balance)<div class="d-flex justify-content-between border-bottom py-2"><span>{{ $balance->stockLocation?->name }}</span><span>{{ $balance->qty_on_hand }} / reorder {{ $balance->reorder_level }}</span></div>@empty<div class="text-muted">Chưa có tồn kho.</div>@endforelse</div></div></div>
<div class="col-lg-4"><div class="card h-100"><div class="card-body"><div class="fw-semibold mb-2">Lịch sử giá</div>@forelse($product->prices as $price)<div class="border-bottom py-2"><div>{{ number_format($price->price_amount) }}đ</div><div class="list-secondary">{{ $price->cinema?->name ?? 'Mặc định' }} · từ {{ optional($price->effective_from)->format('d/m/Y H:i') }}</div></div>@empty<div class="text-muted">Chưa có giá.</div>@endforelse</div></div></div>
</div>
@endsection
