@extends('admin.layout')

@section('title', 'Chi tiết hoàn tiền')

@section('content')
@php
    $badgeClass = match($refund->status) {
        'SUCCESS' => 'badge-soft-success',
        'PENDING' => 'badge-soft-warning',
        'FAILED', 'REJECTED', 'CANCELLED' => 'badge-soft-danger',
        default => 'badge-soft-secondary',
    };
    $payment = $refund->payment;
    $booking = $payment?->booking;
@endphp

<section class="page-header">
    <div>
        <p class="eyebrow">Refund detail</p>
        <h2>{{ $refund->external_ref ?: ('RF-'.$refund->id) }}</h2>
        <p>{{ $booking?->booking_code ?: 'Không có booking' }} · {{ $payment?->external_txn_ref ?: ('PAY-'.($payment?->id ?? 'N/A')) }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.refunds.index') }}" class="btn btn-light-soft">Quay lại</a>
        @if($payment)
            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-secondary">Mở giao dịch</a>
        @endif
        @if($booking)
            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline-secondary">Mở booking</a>
        @endif
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Số tiền refund</div><div class="metric-value">{{ number_format($refund->amount) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Refund thành công</div><div class="metric-value">{{ number_format($metrics['success_amount']) }}đ</div><div class="metric-caption">Trên cùng giao dịch</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Phần còn có thể hoàn</div><div class="metric-value">{{ number_format($metrics['remaining_amount']) }}đ</div><div class="metric-caption">Sau khi trừ refund SUCCESS</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái</div><div class="metric-value fs-5"><span class="badge {{ $badgeClass }}">{{ $statusOptions[$refund->status] ?? $refund->status }}</span></div><div class="metric-caption">{{ optional($refund->updated_at)->format('d/m/Y H:i') ?: '—' }}</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Thông tin refund</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>ID:</strong> #{{ $refund->id }}</div>
                    <div class="col-md-6"><strong>Payment:</strong> {{ $payment?->external_txn_ref ?: ('PAY-'.($payment?->id ?? 'N/A')) }}</div>
                    <div class="col-md-6"><strong>Booking:</strong> {{ $booking?->booking_code ?: 'Không có' }}</div>
                    <div class="col-md-6"><strong>Payment status:</strong> {{ $payment?->status ?: '—' }}</div>
                    <div class="col-md-6"><strong>Provider / Method:</strong> {{ $payment?->provider ?: 'N/A' }} / {{ $payment?->method ?: 'N/A' }}</div>
                    <div class="col-md-6"><strong>Giá trị giao dịch gốc:</strong> {{ number_format($metrics['payment_amount']) }}{{ $payment?->currency ?: 'VND' }}</div>
                    <div class="col-md-8"><strong>Khách hàng:</strong> {{ $booking?->contact_name ?: ($booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                    <div class="col-md-4"><strong>Liên hệ:</strong> {{ $booking?->contact_phone ?: ($booking?->contact_email ?: 'Chưa có') }}</div>
                    <div class="col-12"><strong>Phim / suất:</strong> {{ $booking?->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($booking?->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }} · {{ $booking?->show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                    <div class="col-12"><strong>Lý do:</strong> {{ $refund->reason ?: 'Chưa ghi lý do hoàn tiền' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Cập nhật refund</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.refunds.update', $refund) }}" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="form-label">Số tiền</label>
                        <input type="number" min="1" class="form-control" name="amount" value="{{ old('amount', $refund->amount) }}">
                    </div>
                    <div>
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $refund->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">External ref</label>
                        <input type="text" class="form-control" name="external_ref" value="{{ old('external_ref', $refund->external_ref) }}" placeholder="Ví dụ: RF-20260404001">
                    </div>
                    <div>
                        <label class="form-label">Lý do</label>
                        <textarea class="form-control" rows="3" name="reason" placeholder="Ghi chú lý do hoàn tiền">{{ old('reason', $refund->reason) }}</textarea>
                    </div>
                    <button class="btn btn-primary">Lưu refund</button>
                </form>

                <hr>

                <div class="small text-secondary mb-2">Ghi chú nghiệp vụ</div>
                <ul class="small text-secondary mb-0 ps-3">
                    <li>Refund SUCCESS sẽ tự đồng bộ lại payment và paid_amount của booking.</li>
                    <li>Nếu tổng refund SUCCESS bằng giá trị giao dịch gốc, payment sẽ chuyển sang REFUNDED.</li>
                    <li>Nếu booking chưa ở trạng thái kết thúc, paid_amount có thể trở về 0 và booking quay về chờ thanh toán.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Ghế thuộc booking</div>
            <div class="card-body">
                @if($booking && $booking->tickets->isNotEmpty())
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($booking->tickets as $ticket)
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
            <div class="card-header fw-semibold">Các refund khác cùng giao dịch</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Số tiền</th>
                            <th>Trạng thái</th>
                            <th>External ref</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($payment?->refunds ?? collect() as $item)
                        <tr>
                            <td>
                                <a href="{{ route('admin.refunds.show', $item) }}" class="text-decoration-none">#{{ $item->id }}</a>
                            </td>
                            <td>{{ number_format($item->amount) }}đ</td>
                            <td>{{ $statusOptions[$item->status] ?? $item->status }}</td>
                            <td>{{ $item->external_ref ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">Chưa có refund nào khác.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
