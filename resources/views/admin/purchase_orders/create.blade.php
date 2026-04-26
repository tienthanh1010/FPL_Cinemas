@extends('admin.layout')
@section('title', 'Tạo phiếu nhập')
@section('content')
<section class="page-header"><div><p class="eyebrow">Create purchase order</p><h2>Tạo phiếu nhập F&B</h2><p>Tạo nhiều dòng sản phẩm, số lượng và đơn giá nhập vào cùng một phiếu.</p></div></section>
<form method="POST" action="{{ route('admin.purchase_orders.store') }}">@csrf
<div class="card mb-3"><div class="card-body row g-3">
    <div class="col-md-4"><label class="form-label">Nhà cung cấp</label><select class="form-select" name="supplier_id" required>@foreach($suppliers as $supplier)<option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>@endforeach</select></div>
    <div class="col-md-4"><label class="form-label">Rạp nhận hàng</label><select class="form-select" name="cinema_id" required>@foreach($cinemas as $cinema)<option value="{{ $cinema->id }}" @selected(old('cinema_id') == $cinema->id)>{{ $cinema->name }}</option>@endforeach</select></div>
    <div class="col-md-2"><label class="form-label">Trạng thái</label><select class="form-select" name="status"><option value="DRAFT">DRAFT</option><option value="ORDERED" @selected(old('status')==='ORDERED')>ORDERED</option><option value="CANCELLED" @selected(old('status')==='CANCELLED')>CANCELLED</option></select></div>
    <div class="col-md-2"><label class="form-label">Ngày đặt</label><input type="datetime-local" class="form-control" name="ordered_at" value="{{ old('ordered_at', now()->format('Y-m-d\TH:i')) }}"></div>
    <div class="col-12"><label class="form-label">Ghi chú</label><input class="form-control" name="note" value="{{ old('note') }}" placeholder="Ví dụ: nhập đợt cuối tuần / nhà cung cấp giao 2 đợt"></div>
</div></div>

<div class="card"><div class="card-body">
    <div class="d-flex justify-content-between align-items-center mb-3"><div><div class="fw-semibold">Dòng sản phẩm</div><div class="text-muted small">Ít nhất 1 dòng hợp lệ để lưu phiếu nhập.</div></div><button type="button" class="btn btn-light-soft" id="add-line">+ Thêm dòng</button></div>
    <div class="table-responsive"><table class="table align-middle" id="po-lines-table"><thead><tr><th style="min-width:280px">Sản phẩm</th><th style="width:140px">Số lượng</th><th style="width:180px">Đơn giá nhập</th><th style="width:180px">Thành tiền</th><th style="width:80px"></th></tr></thead><tbody>
        @for($i = 0; $i < max(3, count(old('lines', []))); $i++)
        <tr>
            <td><select class="form-select po-product" name="lines[{{ $i }}][product_id]"><option value="">-- Chọn sản phẩm --</option>@foreach($products as $product)<option value="{{ $product->id }}" @selected(old("lines.$i.product_id") == $product->id)>{{ $product->name }}</option>@endforeach</select></td>
            <td><input type="number" min="1" class="form-control po-qty" name="lines[{{ $i }}][qty_ordered]" value="{{ old("lines.$i.qty_ordered") }}"></td>
            <td><input type="number" min="0" class="form-control po-cost" name="lines[{{ $i }}][unit_cost_amount]" value="{{ old("lines.$i.unit_cost_amount") }}"></td>
            <td><input type="text" class="form-control po-total" readonly></td>
            <td><button type="button" class="btn btn-sm btn-outline-danger remove-line">X</button></td>
        </tr>
        @endfor
    </tbody></table></div>
</div></div>
<div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Lưu phiếu nhập</button><a href="{{ route('admin.purchase_orders.index') }}" class="btn btn-light-soft">Quay lại</a></div>
</form>

<template id="po-line-template">
<tr>
    <td><select class="form-select po-product" name="__NAME__[product_id]"><option value="">-- Chọn sản phẩm --</option>@foreach($products as $product)<option value="{{ $product->id }}">{{ $product->name }}</option>@endforeach</select></td>
    <td><input type="number" min="1" class="form-control po-qty" name="__NAME__[qty_ordered]"></td>
    <td><input type="number" min="0" class="form-control po-cost" name="__NAME__[unit_cost_amount]"></td>
    <td><input type="text" class="form-control po-total" readonly></td>
    <td><button type="button" class="btn btn-sm btn-outline-danger remove-line">X</button></td>
</tr>
</template>
<script>
(() => {
    const tbody = document.querySelector('#po-lines-table tbody');
    const template = document.querySelector('#po-line-template');
    const addBtn = document.querySelector('#add-line');
    let nextIndex = tbody.querySelectorAll('tr').length;

    function recalc(row) {
        const qty = parseInt(row.querySelector('.po-qty')?.value || '0', 10);
        const cost = parseInt(row.querySelector('.po-cost')?.value || '0', 10);
        const total = Math.max(0, qty) * Math.max(0, cost);
        row.querySelector('.po-total').value = total ? total.toLocaleString('vi-VN') + 'đ' : '';
    }

    function bindRow(row) {
        row.querySelectorAll('.po-qty, .po-cost').forEach((input) => input.addEventListener('input', () => recalc(row)));
        row.querySelector('.remove-line')?.addEventListener('click', () => {
            if (tbody.querySelectorAll('tr').length > 1) row.remove();
        });
        recalc(row);
    }

    tbody.querySelectorAll('tr').forEach(bindRow);
    addBtn.addEventListener('click', () => {
        const fragment = template.content.cloneNode(true);
        const row = fragment.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('__NAME__', `lines[${nextIndex}]`);
        tbody.appendChild(row);
        bindRow(tbody.lastElementChild);
        nextIndex += 1;
    });
})();
</script>
@endsection
