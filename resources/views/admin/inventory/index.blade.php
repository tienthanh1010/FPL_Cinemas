@extends('admin.layout')
@section('title', 'Tồn kho F&B')
@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Inventory</p>
        <h2>Quản lý tồn kho bắp nước</h2>
        <p>Theo dõi số lượng tại quầy/kho, giá nhập gần nhất và cảnh báo sắp hết hàng.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.inventory.movements') }}" class="btn btn-light-soft">Lịch sử nhập/xuất</a>
        <a href="{{ route('admin.purchase_orders.index') }}" class="btn btn-primary">Nhập hàng</a>
    </div>
</section>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">SKU</div><div class="metric-value">{{ $summary['total_sku'] }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Vị trí kho</div><div class="metric-value">{{ $summary['locations'] }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Sắp hết hàng</div><div class="metric-value">{{ $summary['low_stock'] }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Giá trị tồn trang hiện tại</div><div class="metric-value">{{ number_format($summary['stock_value']) }}đ</div></div></div>
</div>

<div class="card toolbar-card mb-3"><div class="card-body">
<form class="row g-3" method="GET">
<div class="col-md-3"><label class="form-label">Tìm sản phẩm</label><input class="form-control" name="q" value="{{ $q }}" placeholder="Tên hoặc SKU"></div>
<div class="col-md-2"><label class="form-label">Rạp</label><select class="form-select" name="cinema_id"><option value="">Tất cả rạp</option>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected($cinemaId === (int) $cinema->id)>{{ $cinema->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Vị trí</label><select class="form-select" name="location_id"><option value="">Tất cả vị trí</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected($locationId === $location->id)>{{ $location->name }} · {{ $location->cinema?->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Sản phẩm</label><select class="form-select" name="product_id"><option value="">Tất cả sản phẩm</option>@foreach($products as $p)<option value="{{ $p->id }}" @selected($productId === $p->id)>{{ $p->name }} · {{ $p->sku }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Cảnh báo</label><select class="form-select" name="alert"><option value="">Tất cả</option><option value="low" @selected($alert === 'low')>Sắp hết</option><option value="ok" @selected($alert === 'ok')>Ổn định</option></select></div>
<div class="col-md-1"><label class="form-label">&nbsp;</label><button class="btn btn-primary w-100">Lọc</button></div>
</form>
<hr>
<form class="row g-3" method="POST" action="{{ route('admin.inventory.adjust') }}">@csrf
<div class="col-md-3"><label class="form-label">Vị trí</label><select class="form-select" name="stock_location_id">@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }} · {{ $location->cinema?->name }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Sản phẩm</label><select class="form-select" name="product_id">@foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="form-label">Loại</label><select class="form-select" name="movement_type"><option value="IN">Nhập</option><option value="OUT">Xuất</option><option value="ADJUST">Điều chỉnh tồn =</option></select></div>
<div class="col-md-2"><label class="form-label">Số lượng</label><input class="form-control" type="number" name="qty_delta" value="1"></div>
<div class="col-md-2"><label class="form-label">Đơn giá nhập</label><input class="form-control" type="number" min="0" name="unit_cost_amount" placeholder="Không bắt buộc"></div>
<div class="col-12"><input class="form-control" name="note" placeholder="Ghi chú nhập/xuất hàng"></div>
<div class="col-12"><button class="btn btn-primary">Cập nhật tồn kho</button></div>
</form></div></div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Rạp</th>
                    <th>Vị trí</th>
                    <th>Tồn hiện tại</th>
                    <th>Mức đặt lại</th>
                    <th>Giá nhập gần nhất</th>
                    <th>Giá trị tồn</th>
                    <th>Cảnh báo</th>
                </tr>
            </thead>
            <tbody>
            @forelse($balances as $balance)
                <tr>
                    <td>
                        <a class="fw-semibold text-decoration-none" href="{{ route('admin.products.show', $balance->product_id) }}">{{ $balance->product?->name }}</a>
                        <div class="list-secondary">SKU: {{ $balance->product?->sku }}</div>
                    </td>
                    <td>{{ $balance->stockLocation?->cinema?->name ?: '—' }}</td>
                    <td>{{ $balance->stockLocation?->name }} <div class="list-secondary">{{ $balance->stockLocation?->code }}</div></td>
                    <td>{{ $balance->qty_on_hand }}</td>
                    <td>{{ $balance->reorder_level }}</td>
                    <td>
                        {{ $balance->latest_unit_cost_amount !== null ? number_format($balance->latest_unit_cost_amount).'đ' : '—' }}
                        <div class="list-secondary">TB: {{ $balance->avg_unit_cost_amount !== null ? number_format($balance->avg_unit_cost_amount).'đ' : '—' }}</div>
                    </td>
                    <td>{{ $balance->stock_value_amount !== null ? number_format($balance->stock_value_amount).'đ' : '—' }}</td>
                    <td>
                        @if($balance->qty_on_hand <= $balance->reorder_level)
                            <span class="badge badge-soft-warning">Sắp hết</span>
                        @else
                            <span class="badge badge-soft-success">Ổn định</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty-state">Chưa có dữ liệu tồn kho.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $balances->links() }}</div>
</div>
@endsection
