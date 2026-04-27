@extends('admin.layout')
@section('title', 'Combo bắp nước')
@section('content')
<section class="page-header">
    <div><p class="eyebrow">F&B</p><h2>Sản phẩm / combo bắp nước</h2><p>CRUD sản phẩm, giá bán theo rạp, thống kê bán kèm và cảnh báo tồn kho.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.inventory.index') }}" class="btn btn-light-soft">Tồn kho</a><a href="{{ route('admin.products.create') }}" class="btn btn-primary">Thêm sản phẩm</a></div>
</section>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">SKU</div><div class="metric-value">{{ $report['product_count'] }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Combo</div><div class="metric-value">{{ $report['combo_count'] }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Doanh thu F&B</div><div class="metric-value">{{ number_format($report['fb_revenue']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Sắp hết hàng</div><div class="metric-value">{{ $report['low_stock'] }}</div></div></div>
</div>
<div class="card toolbar-card mb-3"><div class="card-body">
<form class="row g-3" method="GET"><div class="col-lg-9"><input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên hoặc SKU"></div><div class="col-lg-3"><button class="btn btn-primary w-100">Tìm</button></div></form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Sản phẩm</th><th>Loại</th><th>Giá hiện hành</th><th>Đã bán</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
@forelse($products as $product)
@php $price = app(\App\Services\ProductPricingService::class)->currentPrice($product, $cinemaId); @endphp
<tr>
<td>
    <div class="d-flex align-items-center gap-3">
        @if($product->attributes['image_url'] ?? null)
            <img src="{{ $product->attributes['image_url'] }}" alt="{{ $product->name }}" class="rounded-3 border" style="width:64px;height:64px;object-fit:cover;">
        @else
            <div class="rounded-3 border d-flex align-items-center justify-content-center text-muted" style="width:64px;height:64px;"><i class="bi bi-cup-straw"></i></div>
        @endif
        <div>
            <div class="list-primary">{{ $product->name }}</div>
            <div class="list-secondary">SKU: {{ $product->sku }}</div>
            @if($product->attributes['description'] ?? null)
                <div class="list-secondary">{{ \Illuminate\Support\Str::limit($product->attributes['description'], 70) }}</div>
            @endif
        </div>
    </div>
</td>
<td>{{ $product->is_combo ? 'Combo' : 'Sản phẩm lẻ' }}<div class="list-secondary">{{ $product->category?->name }}</div></td>
<td>{{ $price ? number_format($price->price_amount).'đ' : 'Chưa có giá' }}</td>
<td>{{ number_format((int) $product->sold_qty) }}</td>
<td><span class="badge {{ $product->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $product->is_active ? 'Đang bán' : 'Ngừng bán' }}</span></td>
<td class="text-end"><div class="d-inline-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.products.show', $product) }}">Xem</a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.products.edit', $product) }}">Sửa</a><form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Xoá sản phẩm này?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Xoá</button></form></div></td>
</tr>
@empty <tr><td colspan="6" class="empty-state">Chưa có sản phẩm F&B.</td></tr>@endforelse
</tbody></table></div><div class="card-body border-top">{{ $products->links() }}</div></div>
@endsection
