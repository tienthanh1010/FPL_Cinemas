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
@endphp

<section class="page-header">
    <div>
        <p class="eyebrow">Payment detail</p>
        <h2>{{ $payment->external_txn_ref ?: ('PAY-'.$payment->id) }}</h2>
        <p>{{ $payment->booking?->booking_code ?: 'Không có booking' }} · {{ $payment->provider ?: 'N/A' }} / {{ $payment->method ?: 'N/A' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.payments.index') }}" class="btn btn-light-soft">Quay lại</a>
        @if($payment->booking)
            <a href="{{ route('admin.bookings.show', $payment->booking) }}" class="btn btn-outline-secondary">Mở booking</a>
        @endif
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Số tiền giao dịch</div><div class="metric-value">{{ number_format($payment->amount) }}{{ $payment->currency }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đã ghi nhận booking</div><div class="metric-value">{{ number_format((int) ($payment->booking?->paid_amount ?? 0)) }}đ</div><div class="metric-caption">Đồng bộ vào paid_amount booking</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái booking</div><div class="metric-value fs-5">{{ $payment->booking?->status ?: '—' }}</div><div class="metric-caption">{{ $payment->booking?->booking_code ?: 'Không có booking' }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái giao dịch</div><div class="metric-value fs-5"><span class="badge {{ $badgeClass }}">{{ $statusOptions[$payment->status] ?? $payment->status }}</span></div><div class="metric-caption">Paid at: {{ optional($payment->paid_at)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div></div></div>
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
                        <input type="text" class="form-control" name="external_txn_ref" value="{{ old('external_txn_ref', $payment->external_txn_ref) }}" placeholder="Ví dụ: MB-202604040001">
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
                    <li>Không còn hỗ trợ hoàn tiền trong giao diện quản trị này.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
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
