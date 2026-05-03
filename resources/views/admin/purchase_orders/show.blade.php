@extends('admin.layout')
@section('title', $purchaseOrder->po_code)
@section('content')
<section class="page-header">
    <div><p class="eyebrow">Purchase order detail</p><h2>{{ $purchaseOrder->po_code }}</h2><p>{{ $purchaseOrder->supplier?->name }} · {{ $purchaseOrder->cinema?->name }}</p></div>
    <div class="d-flex gap-2"><a href="{{ route('admin.purchase_orders.index') }}" class="btn btn-light-soft">Danh sách phiếu nhập</a></div>
</section>
<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái</div><div class="metric-value" style="font-size:1.2rem">{{ $purchaseOrder->status }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng tiền</div><div class="metric-value">{{ number_format($purchaseOrder->total_amount) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Số dòng</div><div class="metric-value">{{ $purchaseOrder->lines->count() }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tiến độ nhận</div><div class="metric-value">{{ number_format((int) $purchaseOrder->lines->sum('qty_received')) }}/{{ number_format((int) $purchaseOrder->lines->sum('qty_ordered')) }}</div></div></div>
</div>
<div class="row g-3">
    <div class="col-lg-8 d-grid gap-3">
        <div class="card"><div class="card-body">
            <div class="fw-semibold mb-3">Chi tiết hàng nhập</div>
            <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Sản phẩm</th><th class="text-center">SL đặt</th><th class="text-center">SL đã nhận</th><th class="text-end">Đơn giá nhập</th><th class="text-end">Thành tiền</th></tr></thead><tbody>
                @foreach($purchaseOrder->lines as $line)
                <tr>
                    <td>{{ $line->product?->name }}<div class="list-secondary">{{ $line->product?->category?->name }}</div></td>
                    <td class="text-center">{{ number_format($line->qty_ordered) }}</td>
                    <td class="text-center">{{ number_format($line->qty_received) }}</td>
                    <td class="text-end">{{ number_format($line->unit_cost_amount) }}đ</td>
                    <td class="text-end">{{ number_format($line->line_amount) }}đ</td>
                </tr>
                @endforeach
            </tbody></table></div>
        </div></div>

        <div class="card"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="fw-semibold">Lịch sử nhận hàng của phiếu này</div>
                <a href="{{ route('admin.inventory.movements', ['reference_type' => 'PURCHASE_ORDER']) }}" class="btn btn-sm btn-light-soft">Xem toàn bộ lịch sử</a>
            </div>
            <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Thời gian</th><th>Sản phẩm</th><th>Vị trí</th><th>SL nhập</th><th>Đơn giá</th><th>Ghi chú</th></tr></thead><tbody>
            @forelse($movements as $movement)
                <tr>
                    <td>{{ optional($movement->created_at)->format('d/m/Y H:i') }}</td>
                    <td>{{ $movement->product?->name }}</td>
                    <td>{{ $movement->stockLocation?->name }} · {{ $movement->stockLocation?->cinema?->name }}</td>
                    <td><span class="badge badge-soft-success">+{{ number_format($movement->qty_delta) }}</span></td>
                    <td>{{ $movement->unit_cost_amount !== null ? number_format($movement->unit_cost_amount).'đ' : '—' }}</td>
                    <td>{{ $movement->note ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="empty-state">Phiếu này chưa phát sinh lần nhập kho nào.</td></tr>
            @endforelse
            </tbody></table></div>
        </div></div>
    </div>
    <div class="col-lg-4 d-grid gap-3">
        <div class="card"><div class="card-body">
            <div class="fw-semibold mb-3">Cập nhật trạng thái</div>
            <form method="POST" action="{{ route('admin.purchase_orders.update', $purchaseOrder) }}" class="row g-3">@csrf @method('PUT')
                <div class="col-12"><label class="form-label">Trạng thái</label><select class="form-select" name="status">@foreach(['DRAFT','ORDERED','RECEIVED','CANCELLED'] as $status)<option value="{{ $status }}" @selected($purchaseOrder->status === $status)>{{ $status }}</option>@endforeach</select></div>
                <div class="col-12"><label class="form-label">Ngày đặt</label><input type="datetime-local" class="form-control" name="ordered_at" value="{{ optional($purchaseOrder->ordered_at)->format('Y-m-d\TH:i') }}"></div>
                <div class="col-12"><label class="form-label">Ghi chú</label><textarea class="form-control" rows="3" name="note">{{ old('note', $purchaseOrder->note) }}</textarea></div>
                <div class="col-12"><button class="btn btn-primary w-100">Lưu cập nhật</button></div>
            </form>
        </div></div>

        <div class="card"><div class="card-body">
            <div class="fw-semibold mb-3">Nhập hàng vào kho/quầy</div>
            <form method="POST" action="{{ route('admin.purchase_orders.receive', $purchaseOrder) }}" class="row g-3">@csrf
                <div class="col-12"><label class="form-label">Vị trí nhập</label><select class="form-select" name="stock_location_id" required>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }} · {{ $location->cinema?->name }}</option>@endforeach</select></div>
                @foreach($purchaseOrder->lines as $line)
                    @php($remaining = max(0, (int) $line->qty_ordered - (int) $line->qty_received))
                    <div class="col-12">
                        <label class="form-label d-flex justify-content-between"><span>{{ $line->product?->name }}</span><span class="text-muted">Còn lại {{ $remaining }} · đơn giá {{ number_format($line->unit_cost_amount) }}đ</span></label>
                        <input type="number" min="0" max="{{ $remaining }}" class="form-control" name="receive_qty[{{ $line->id }}]" value="0" @disabled($remaining === 0)>
                    </div>
                @endforeach
                <div class="col-12"><input class="form-control" name="note" placeholder="Ghi chú nhập kho"></div>
                <div class="col-12"><button class="btn btn-success w-100">Xác nhận nhập hàng</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection
