@extends('admin.layout')
@section('title', $supplier->name)
@section('content')
<section class="page-header">
    <div><p class="eyebrow">Supplier detail</p><h2>{{ $supplier->name }}</h2><p>Theo dõi thông tin liên hệ và lịch sử phiếu nhập gần đây.</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.suppliers.edit', $supplier) }}" class="btn btn-outline-primary">Sửa</a></div>
</section>
<div class="row g-3">
    <div class="col-lg-4"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-2">Thông tin liên hệ</div>
        <div class="mb-2"><span class="text-muted">Điện thoại:</span> {{ $supplier->phone ?: '—' }}</div>
        <div class="mb-2"><span class="text-muted">Email:</span> {{ $supplier->email ?: '—' }}</div>
        <div class="mb-2"><span class="text-muted">MST:</span> {{ $supplier->tax_code ?: '—' }}</div>
        <div><span class="text-muted">Địa chỉ:</span> {{ collect([$supplier->address_line, $supplier->ward, $supplier->district, $supplier->province])->filter()->implode(', ') ?: '—' }}</div>
    </div></div></div>
    <div class="col-lg-8"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-3">Phiếu nhập gần đây</div>
        <div class="table-responsive"><table class="table table-sm align-middle"><thead><tr><th>Mã phiếu</th><th>Rạp</th><th>Trạng thái</th><th>Tổng tiền</th></tr></thead><tbody>
            @forelse($supplier->purchaseOrders as $po)
                <tr><td><a href="{{ route('admin.purchase_orders.show', $po) }}">{{ $po->po_code }}</a></td><td>{{ $po->cinema?->name ?: '—' }}</td><td>{{ $po->status }}</td><td>{{ number_format($po->total_amount) }}đ</td></tr>
            @empty
                <tr><td colspan="4" class="text-muted">Chưa có phiếu nhập.</td></tr>
            @endforelse
        </tbody></table></div>
    </div></div></div>
</div>
@endsection
