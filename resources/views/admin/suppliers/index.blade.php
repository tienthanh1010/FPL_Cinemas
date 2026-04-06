@extends('admin.layout')
@section('title', 'Nhà cung cấp F&B')
@section('content')
<section class="page-header">
    <div><p class="eyebrow">F&B suppliers</p><h2>Nhà cung cấp</h2><p>Quản lý nhà cung cấp nguyên vật liệu, combo và đồ uống cho rạp.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.suppliers.create') }}" class="btn btn-primary">Thêm nhà cung cấp</a></div>
</section>
<div class="card toolbar-card mb-3"><div class="card-body">
    <form class="row g-3" method="GET">
        <div class="col-lg-10"><input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên, số điện thoại, email, mã số thuế"></div>
        <div class="col-lg-2"><button class="btn btn-primary w-100">Tìm</button></div>
    </form>
</div></div>
<div class="card"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th>Nhà cung cấp</th><th>Liên hệ</th><th>Khu vực</th><th>Phiếu nhập</th><th>Trạng thái</th><th class="text-end">Thao tác</th></tr></thead><tbody>
@forelse($suppliers as $supplier)
<tr>
    <td><div class="list-primary">{{ $supplier->name }}</div><div class="list-secondary">{{ $supplier->tax_code ?: 'Chưa có MST' }}</div></td>
    <td><div>{{ $supplier->phone ?: '—' }}</div><div class="list-secondary">{{ $supplier->email ?: '—' }}</div></td>
    <td>{{ collect([$supplier->district, $supplier->province])->filter()->implode(', ') ?: '—' }}</td>
    <td>{{ number_format((int) $supplier->purchase_orders_count) }}</td>
    <td><span class="badge {{ $supplier->status === 'ACTIVE' ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $supplier->status === 'ACTIVE' ? 'Đang hợp tác' : 'Ngừng hợp tác' }}</span></td>
    <td class="text-end"><div class="d-inline-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.suppliers.show', $supplier) }}">Xem</a><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.suppliers.edit', $supplier) }}">Sửa</a></div></td>
</tr>
@empty
<tr><td colspan="6" class="empty-state">Chưa có nhà cung cấp nào.</td></tr>
@endforelse
</tbody></table></div><div class="card-body border-top">{{ $suppliers->links() }}</div></div>
@endsection
