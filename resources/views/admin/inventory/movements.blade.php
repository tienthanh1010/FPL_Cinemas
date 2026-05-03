@extends('admin.layout')
@section('title', 'Lịch sử nhập xuất F&B')
@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Stock history</p>
        <h2>Lịch sử nhập / xuất kho F&B</h2>
        <p>Theo dõi toàn bộ biến động kho, giá nhập theo đợt và nguồn phát sinh.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-light-soft">Quay lại tồn kho</a>
    </div>
</section>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Lượt biến động</div><div class="metric-value">{{ number_format($summary['movement_count']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng nhập</div><div class="metric-value">{{ number_format($summary['total_in']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng xuất</div><div class="metric-value">{{ number_format($summary['total_out']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Từ phiếu nhập</div><div class="metric-value">{{ number_format($summary['purchase_receipts']) }}</div></div></div>
</div>
<div class="card toolbar-card mb-3"><div class="card-body">
    <form class="row g-3" method="GET">
        <div class="col-md-3"><label class="form-label">Tìm kiếm</label><input class="form-control" name="q" value="{{ $q }}" placeholder="Sản phẩm hoặc ghi chú"></div>
        <div class="col-md-2"><label class="form-label">Rạp</label><select class="form-select" name="cinema_id"><option value="">Tất cả rạp</option>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected($cinemaId === (int) $cinema->id)>{{ $cinema->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Vị trí</label><select class="form-select" name="location_id"><option value="">Tất cả vị trí</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($locationId === $location->id)>{{ $location->name }} · {{ $location->cinema?->name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Sản phẩm</label><select class="form-select" name="product_id"><option value="">Tất cả</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected($productId === $product->id)>{{ $product->name }}</option>@endforeach</select></div>
        <div class="col-md-1"><label class="form-label">Loại</label><select class="form-select" name="movement_type"><option value="">Tất cả</option>@foreach(['IN' => 'Nhập', 'OUT' => 'Xuất', 'ADJUST' => 'Điều chỉnh'] as $key => $label)<option value="{{ $key }}" @selected($movementType === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-1"><label class="form-label">Nguồn</label><select class="form-select" name="reference_type"><option value="">Tất cả</option>@foreach(['PURCHASE_ORDER' => 'Phiếu nhập', 'BOOKING' => 'Đặt vé', 'ADJUSTMENT' => 'Điều chỉnh'] as $key => $label)<option value="{{ $key }}" @selected($referenceType === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-md-1"><label class="form-label">&nbsp;</label><button class="btn btn-primary w-100">Lọc</button></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0">
<thead><tr><th>Thời gian</th><th>Sản phẩm</th><th>Rạp / vị trí</th><th>Biến động</th><th>Đơn giá</th><th>Nguồn</th><th>Ghi chú</th></tr></thead>
<tbody>
@forelse($movements as $movement)
<tr>
    <td>{{ optional($movement->created_at)->format('d/m/Y H:i') }}</td>
    <td>{{ $movement->product?->name }}<div class="list-secondary">{{ $movement->product?->sku }}</div></td>
    <td>{{ $movement->stockLocation?->cinema?->name }}<div class="list-secondary">{{ $movement->stockLocation?->name }}</div></td>
    <td>
        @if($movement->qty_delta > 0)
            <span class="badge badge-soft-success">+{{ number_format($movement->qty_delta) }}</span>
        @elseif($movement->qty_delta < 0)
            <span class="badge badge-soft-danger">{{ number_format($movement->qty_delta) }}</span>
        @else
            <span class="badge badge-soft-secondary">0</span>
        @endif
        <div class="list-secondary">{{ $movement->movement_type }}</div>
    </td>
    <td>{{ $movement->unit_cost_amount !== null ? number_format($movement->unit_cost_amount).'đ' : '—' }}</td>
    <td>
        {{ $movement->reference_type ?: '—' }}
        @if($movement->reference_type === 'PURCHASE_ORDER' && $movement->reference_id)
            <div class="list-secondary"><a href="{{ route('admin.purchase_orders.show', $movement->reference_id) }}">PO #{{ $movement->reference_id }}</a></div>
        @endif
    </td>
    <td>{{ $movement->note ?: '—' }}</td>
</tr>
@empty
<tr><td colspan="7" class="empty-state">Chưa có biến động kho nào.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top">{{ $movements->links() }}</div></div>
@endsection
