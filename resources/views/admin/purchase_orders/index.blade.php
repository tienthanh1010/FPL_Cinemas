@extends('admin.layout')
@section('title', 'Nhập hàng F&B')
@section('content')
<section class="page-header">
    <div><p class="eyebrow">F&B inbound</p><h2>Nhập hàng F&B</h2><p>Tạo phiếu nhập, theo dõi trạng thái và nhận hàng vào tồn kho.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.suppliers.index') }}" class="btn btn-light-soft">Nhà cung cấp</a><a href="{{ route('admin.purchase_orders.create') }}" class="btn btn-primary">Tạo phiếu nhập</a></div>
</section>
<div class="card toolbar-card mb-3"><div class="card-body">
    <form class="row g-3" method="GET">
        <div class="col-lg-5"><input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo mã phiếu hoặc nhà cung cấp"></div>
        <div class="col-lg-3"><select class="form-select" name="cinema_id"><option value="">Tất cả rạp</option>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected($cinemaId === $cinema->id)>{{ $cinema->name }}</option>@endforeach</select></div>
        <div class="col-lg-2"><select class="form-select" name="status"><option value="">Tất cả trạng thái</option>@foreach(['DRAFT' => 'Nháp', 'ORDERED' => 'Đã đặt', 'RECEIVED' => 'Đã nhận', 'CANCELLED' => 'Đã huỷ'] as $value => $label)<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>@endforeach</select></div>
        <div class="col-lg-2"><button class="btn btn-primary w-100">Lọc</button></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Mã phiếu</th><th>Nhà cung cấp</th><th>Rạp</th><th>Sản phẩm</th><th>Tiến độ nhận</th><th>Tổng tiền</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
@forelse($purchaseOrders as $purchaseOrder)
<tr>
    <td><div class="list-primary">{{ $purchaseOrder->po_code }}</div><div class="list-secondary">{{ optional($purchaseOrder->ordered_at)->format('d/m/Y H:i') ?: '—' }}</div></td>
    <td>{{ $purchaseOrder->supplier?->name ?: '—' }}</td>
    <td>{{ $purchaseOrder->cinema?->name ?: '—' }}</td>
    <td>{{ number_format((int) $purchaseOrder->lines_count) }} dòng</td>
    <td>{{ number_format((int) $purchaseOrder->qty_received_total) }} / {{ number_format((int) $purchaseOrder->qty_ordered_total) }}</td>
    <td>{{ number_format($purchaseOrder->total_amount) }}đ</td>
    <td><span class="badge {{ match($purchaseOrder->status){'RECEIVED' => 'badge-soft-success','CANCELLED' => 'badge-soft-secondary', default => 'badge-soft-warning'} }}">{{ $purchaseOrder->status }}</span></td>
    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.purchase_orders.show', $purchaseOrder) }}">Xem phiếu</a></td>
</tr>
@empty
<tr><td colspan="8" class="empty-state">Chưa có phiếu nhập hàng nào.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top">{{ $purchaseOrders->links() }}</div></div>
@endsection
