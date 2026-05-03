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

    $requestRows = array_values(array_filter([
        ['label' => 'Chế độ thanh toán', 'value' => data_get($payment->request_payload, 'mode')],
        ['label' => 'Số tiền', 'value' => data_get($payment->request_payload, 'amount') ? number_format((int) data_get($payment->request_payload, 'amount')).' '.$payment->currency : null],
        ['label' => 'Ngân hàng', 'value' => data_get($payment->request_payload, 'bank_id')],
        ['label' => 'Số tài khoản', 'value' => data_get($payment->request_payload, 'account_no')],
        ['label' => 'Tên tài khoản', 'value' => data_get($payment->request_payload, 'account_name')],
        ['label' => 'Mẫu QR', 'value' => data_get($payment->request_payload, 'qr_template')],
        ['label' => 'Nội dung chuyển khoản', 'value' => data_get($payment->request_payload, 'transfer_content') ?: data_get($payment->request_payload, 'booking_code')],
        ['label' => 'Nhà cung cấp', 'value' => data_get($payment->request_payload, 'provider_label')],
    ], fn ($row) => filled($row['value'] ?? null)));

    $responseRows = array_values(array_filter([
        ['label' => 'Trạng thái phản hồi', 'value' => data_get($payment->response_payload, 'status')],
        ['label' => 'Thông báo', 'value' => data_get($payment->response_payload, 'message')],
        ['label' => 'Mã giao dịch', 'value' => $payment->external_txn_ref],
        ['label' => 'Ghi nhận lúc', 'value' => optional($payment->paid_at)->format('d/m/Y H:i')],
    ], fn ($row) => filled($row['value'] ?? null)));

    $qrImage = data_get($payment->request_payload, 'qr_image_url');
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
                    <li>Thông tin kỹ thuật đã được chuyển sang dạng dễ đọc thay vì hiển thị JSON thô.</li>
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
            <div class="card-header fw-semibold">Thông tin yêu cầu thanh toán</div>
            <div class="card-body">
                @if($requestRows)
                    <div class="row g-3">
                        @foreach($requestRows as $row)
                            <div class="col-md-6">
                                <div class="small text-secondary">{{ $row['label'] }}</div>
                                <div class="fw-semibold text-break">{{ $row['value'] }}</div>
                            </div>
                        @endforeach
                        @if($qrImage)
                            <div class="col-12">
                                <div class="small text-secondary mb-2">QR chuyển khoản</div>
                                <a href="{{ $qrImage }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">Mở ảnh QR</a>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-muted">Không có dữ liệu yêu cầu thanh toán.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Kết quả phản hồi</div>
            <div class="card-body">
                @if($responseRows)
                    <div class="row g-3">
                        @foreach($responseRows as $row)
                            <div class="col-md-6">
                                <div class="small text-secondary">{{ $row['label'] }}</div>
                                <div class="fw-semibold text-break">{{ $row['value'] }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted">Chưa có phản hồi từ kênh thanh toán.</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
