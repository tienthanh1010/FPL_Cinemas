@extends('admin.layout')
@section('title', 'Tồn kho F&B')
@section('content')
<section class="page-header"><div><p class="eyebrow">Inventory</p><h2>Quản lý tồn kho bắp nước</h2><p>Theo dõi số lượng tại quầy/kho, nhập xuất và cảnh báo sắp hết hàng.</p></div></section>
<div class="row g-3 mb-3">
<div class="col-md-3"><div class="metric-card"><div class="metric-label">SKU</div><div class="metric-value">{{ $summary['total_sku'] }}</div></div></div>
<div class="col-md-3"><div class="metric-card"><div class="metric-label">Vị trí kho</div><div class="metric-value">{{ $summary['locations'] }}</div></div></div>
<div class="col-md-3"><div class="metric-card"><div class="metric-label">Sắp hết hàng</div><div class="metric-value">{{ $summary['low_stock'] }}</div></div></div>
<div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng tồn (qty)</div><div class="metric-value">{{ number_format($summary['total_qty']) }}</div></div></div>
</div>
<div class="card toolbar-card mb-3"><div class="card-body">
<form class="row g-3" method="POST" action="{{ route('admin.inventory.adjust') }}">@csrf
<div class="col-md-3"><label class="form-label">Vị trí</label><select class="form-select" name="stock_location_id">@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Sản phẩm</label><select class="form-select" name="product_id">@foreach(\App\Models\Product::orderBy('name')->get() as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Loại</label><select class="form-select" name="movement_type"><option value="IN">Nhập</option><option value="OUT">Xuất</option><option value="ADJUST">Điều chỉnh</option></select></div>
<div class="col-md-2"><label class="form-label">Số lượng</label><input class="form-control" type="number" name="qty_delta" value="1"></div>
<div class="col-md-2"><label class="form-label">&nbsp;</label><button class="btn btn-primary w-100">Cập nhật</button></div>
<div class="col-12"><input class="form-control" name="note" placeholder="Ghi chú nhập/xuất hàng"></div>
</form></div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Sản phẩm</th><th>Vị trí</th><th>Tồn hiện tại</th><th>Mức đặt lại</th><th>Cảnh báo</th></tr></thead><tbody>
@forelse($balances as $balance)
<tr><td>{{ $balance->product?->name }}</td><td>{{ $balance->stockLocation?->name }}</td><td>{{ $balance->qty_on_hand }}</td><td>{{ $balance->reorder_level }}</td><td>@if($balance->qty_on_hand <= $balance->reorder_level)<span class="badge badge-soft-warning">Sắp hết</span>@else <span class="badge badge-soft-success">Ổn định</span>@endif</td></tr>
@empty <tr><td colspan="5" class="empty-state">Chưa có dữ liệu tồn kho.</td></tr>@endforelse
</tbody></table></div><div class="card-body border-top">{{ $balances->links() }}</div></div>
@endsection
