@extends('admin.layout')

@section('title', 'Chi tiết thanh toán')

@section('content')
@php
    $badgeClass = match($payment->status) {
        'CAPTURED' => 'badge-soft-success',
        'REFUNDED' => 'badge-soft-primary',
        'FAILED', 'CANCELLED' => 'badge-soft-danger',
        'AUTHORIZED' => 'badge-soft-info',
        default => 'badge-soft-warning',
    };
    $requestPayload = $payment->request_payload ? json_encode($payment->request_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    $responsePayload = $payment->response_payload ? json_encode($payment->response_payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    $refundStatusOptions = [
        'PENDING' => 'Chờ xử lý',
        'SUCCESS' => 'Thành công',
        'FAILED' => 'Thất bại',
        'REJECTED' => 'Từ chối',
        'CANCELLED' => 'Đã huỷ',
    ];
    $refundSuccessAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');
    $refundCommittedAmount = (int) $payment->refunds->whereIn('status', ['PENDING', 'SUCCESS'])->sum('amount');
    $refundPendingAmount = (int) $payment->refunds->where('status', 'PENDING')->sum('amount');
    $remainingRefundableAmount = max(0, (int) $payment->amount - $refundCommittedAmount);
@endphp

<section class="page-header">
    <div>
        <p class="eyebrow">Payment detail</p>
        <h2>{{ $payment->external_txn_ref ?: ('PAY-'.$payment->id) }}</h2>
        <p>{{ $payment->booking?->booking_code ?: 'Không có booking' }} · {{ $payment->provider ?: 'N/A' }} / {{ $payment->method ?: 'N/A' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-light-soft">Quay lại</a>
        <a href="{{ route('admin.refunds.index') }}?q={{ urlencode($payment->external_txn_ref ?: ('PAY-'.$payment->id)) }}" class="btn btn-outline-secondary">Danh sách refund</a>
        @if($payment->booking)
            <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="btn btn-outline-secondary">Mở booking</a>
        @endif
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Số tiền giao dịch</div><div class="metric-value">{{ number_format($payment->amount) }}{{ $payment->currency }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Refund thành công</div><div class="metric-value">{{ number_format($metrics['refund_success_amount']) }}đ</div><div class="metric-caption">{{ number_format($metrics['refund_count']) }} yêu cầu · Chờ xử lý {{ number_format($refundPendingAmount) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Giá trị thuần</div><div class="metric-value">{{ number_format($metrics['net_amount']) }}đ</div><div class="metric-caption">Đồng bộ vào paid_amount booking</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái</div><div class="metric-value fs-5"><span class="badge {{ $badgeClass }}">{{ $statusOptions[$payment->status] ?? $payment->status }}</span></div><div class="metric-caption">Paid at: {{ optional($payment->paid_at)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Thông tin giao dịch</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>ID:</strong> #{{ $payment->id }}</div>
                    <div class="col-md-6"><strong>Booking:</strong> {{ $payment->booking?->booking_code ?: 'Không có' }}</div>
                    <div class="col-md-6"><strong>Provider:</strong> {{ $payment->provider ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Method:</strong> {{ $payment->method ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Mã giao dịch ngoài:</strong> {{ $payment->external_txn_ref ?: 'Chưa có' }}</div>
                    <div class="col-md-6"><strong>Tạo lúc:</strong> {{ optional($payment->created_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="col-md-6"><strong>Thanh toán lúc:</strong> {{ optional($payment->paid_at)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div>
                    <div class="col-md-6"><strong>Booking status hiện tại:</strong> {{ $payment->booking?->status ?: '—' }}</div>
                    <div class="col-md-8"><strong>Khách hàng:</strong> {{ $payment->booking?->contact_name ?: ($payment->booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                    <div class="col-md-4"><strong>SĐT:</strong> {{ $payment->booking?->contact_phone ?: 'Chưa có' }}</div>
                    <div class="col-12"><strong>Phim / suất:</strong> {{ $payment->booking?->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($payment->booking?->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }} · {{ $payment->booking?->show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Cập nhật trạng thái giao dịch</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.payments.update', $payment) }}" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($payment->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Mã giao dịch ngoài</label>
                        <input type="text" class="form-control" name="external_txn_ref" value="{{ old('external_txn_ref', $payment->external_txn_ref) }}" placeholder="Ví dụ: VNP-202604040001">
                    </div>
                    <div>
                        <label class="form-label">Paid at</label>
                        <input type="datetime-local" class="form-control" name="paid_at" value="{{ old('paid_at', optional($payment->paid_at)->format('Y-m-d\TH:i')) }}">
                    </div>
                    <button class="btn btn-primary">Lưu giao dịch</button>
                </form>

                <hr>

                <div class="small text-secondary mb-2">Ghi chú đồng bộ</div>
                <ul class="small text-secondary mb-0 ps-3">
                    <li>CAPTURED sẽ đồng bộ booking sang đã thanh toán nếu booking chưa kết thúc.</li>
                    <li>FAILED / CANCELLED / INITIATED sẽ đưa booking về chờ thanh toán nếu chưa có giao dịch thu tiền khác.</li>
                    <li>REFUNDED sẽ đưa giá trị thuần của giao dịch này về 0.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Ghế thuộc booking</div>
            <div class="card-body">
                @if($payment->booking && $payment->booking->tickets->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($payment->booking->tickets as $ticket)
                            <span class="badge badge-soft-secondary px-3 py-2">{{ $ticket->seat?->seat_code ?: ('#'.$ticket->seat_id) }}</span>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">Không có ghế nào gắn với booking này.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold d-flex justify-content-between align-items-center">
                <span>Tạo yêu cầu hoàn tiền</span>
                <span class="small text-secondary">Còn có thể tạo refund: {{ number_format($remainingRefundableAmount) }}đ</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.payments.refunds.store', $payment) }}" class="d-grid gap-3">
                    @csrf
                    <div>
                        <label class="form-label">Số tiền hoàn</label>
                        <input type="number" min="1" max="{{ max(1, $remainingRefundableAmount) }}" class="form-control" name="amount" value="{{ old('amount', $remainingRefundableAmount > 0 ? $remainingRefundableAmount : '') }}" @disabled($remainingRefundableAmount <= 0)>
                    </div>
                    <div>
                        <label class="form-label">Trạng thái ban đầu</label>
                        <select class="form-select" name="status" @disabled($remainingRefundableAmount <= 0)>
                            @foreach($refundStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', 'PENDING') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">External ref</label>
                        <input type="text" class="form-control" name="external_ref" value="{{ old('external_ref') }}" placeholder="Ví dụ: RF-20260404001" @disabled($remainingRefundableAmount <= 0)>
                    </div>
                    <div>
                        <label class="form-label">Lý do</label>
                        <textarea class="form-control" rows="3" name="reason" placeholder="Ghi chú lý do hoàn tiền" @disabled($remainingRefundableAmount <= 0)>{{ old('reason') }}</textarea>
                    </div>
                    <div class="small text-secondary">Hệ thống giữ chỗ số tiền refund ở trạng thái <strong>PENDING</strong> và <strong>SUCCESS</strong> để tránh tạo vượt quá giá trị giao dịch.</div>
                    <button class="btn btn-primary" @disabled($remainingRefundableAmount <= 0)>Tạo refund</button>
                </form>

                @if($remainingRefundableAmount <= 0)
                    <div class="alert alert-light border mt-3 mb-0">Giao dịch này đã được hoàn hết hoặc toàn bộ phần còn lại đã nằm trong các refund đang chờ xử lý.</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card h-100">
            <div class="card-header fw-semibold">Hoàn tiền liên quan</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>Lý do</th>
                            <th>External ref</th>
                            <th class="text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($payment->refunds as $refund)
                        <tr>
                            <td>#{{ $refund->id }}</td>
                            <td>{{ number_format($refund->amount) }}đ</td>
                            <td>{{ $refundStatusOptions[$refund->status] ?? $refund->status }}</td>
                            <td>{{ $refund->reason ?: '—' }}</td>
                            <td>{{ $refund->external_ref ?: '—' }}</td>
                            <td class="text-end"><a href="{{ route('admin.refunds.show', $refund) }}" class="btn btn-sm btn-outline-secondary">Xem refund</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">Chưa có refund nào cho giao dịch này.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Request payload</div>
            <div class="card-body">
                @if($requestPayload)
                    <pre class="small mb-0" style="white-space: pre-wrap;">{{ $requestPayload }}</pre>
                @else
                    <div class="text-muted">Không có request payload.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Response payload</div>
            <div class="card-body">
                @if($responsePayload)
                    <pre class="small mb-0" style="white-space: pre-wrap;">{{ $responsePayload }}</pre>
                @else
                    <div class="text-muted">Không có response payload.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
