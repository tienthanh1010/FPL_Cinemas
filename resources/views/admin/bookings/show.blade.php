@extends('admin.layout')

@section('title', 'Chi tiết booking')

@section('content')
@php
    $badgeClass = match($booking->status) {
        'PAID', 'CONFIRMED', 'COMPLETED' => 'badge-soft-success',
        'CANCELLED' => 'badge-soft-danger',
        'EXPIRED' => 'badge-soft-secondary',
        default => 'badge-soft-warning',
    };
@endphp

<section class="page-header">
    <div>
        <p class="eyebrow">Booking detail</p>
        <h2>{{ $booking->booking_code }}</h2>
        <p>{{ $booking->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-light-soft">Quay lại</a>
        @if(! in_array($booking->status, ['CANCELLED', 'EXPIRED'], true))
            <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Huỷ booking này và trả lại ghế/tồn kho?')">
                @csrf
                <button class="btn btn-outline-danger">Huỷ booking</button>
            </form>
        @endif
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng tiền</div><div class="metric-value">{{ number_format($booking->total_amount) }}đ</div><div class="metric-caption">Đã thu {{ number_format($booking->paid_amount) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Số vé</div><div class="metric-value">{{ number_format($totals['ticket_count']) }}</div><div class="metric-caption">Ghế đã gắn với booking</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Combo / F&B</div><div class="metric-value">{{ number_format($totals['product_qty']) }}</div><div class="metric-caption">Tổng số lượng sản phẩm</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Giảm giá</div><div class="metric-value">{{ number_format($booking->discount_amount) }}đ</div><div class="metric-caption">{{ number_format($totals['discount_count']) }} dòng khuyến mãi</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Thông tin booking</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Mã booking:</strong> {{ $booking->booking_code }}</div>
                    <div class="col-md-6"><strong>Trạng thái:</strong> <span class="badge {{ $badgeClass }}">{{ $statusOptions[$booking->status] ?? $booking->status }}</span></div>
                    <div class="col-md-6"><strong>Khách hàng:</strong> {{ $booking->contact_name ?: ($booking->customer?->full_name ?? 'Khách lẻ') }}</div>
                    <div class="col-md-6"><strong>SĐT:</strong> {{ $booking->contact_phone ?: 'Chưa có' }}</div>
                    <div class="col-md-6"><strong>Email:</strong> {{ $booking->contact_email ?: 'Chưa có' }}</div>
                    <div class="col-md-6"><strong>Tạo lúc:</strong> {{ optional($booking->created_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="col-md-6"><strong>Hết hạn giữ chỗ:</strong> {{ optional($booking->expires_at)->format('d/m/Y H:i') ?: 'Không có' }}</div>
                    <div class="col-md-6"><strong>Rạp:</strong> {{ $booking->show?->auditorium?->cinema?->name ?? ($booking->cinema?->name ?? 'Chưa có') }}</div>
                    <div class="col-md-6"><strong>Phòng chiếu:</strong> {{ $booking->show?->auditorium?->name ?: 'Chưa có' }}</div>
                    <div class="col-md-8"><strong>Phim:</strong> {{ $booking->show?->movieVersion?->movie?->title ?? 'Chưa có' }}</div>
                    <div class="col-md-4"><strong>Suất chiếu:</strong> {{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</div>
                    @if($booking->notes)
                        <div class="col-12"><strong>Ghi chú:</strong> {{ $booking->notes }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Cập nhật trạng thái</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="d-grid gap-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="form-label">Trạng thái mới</label>
                        <select class="form-select" name="status">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected($booking->status === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary">Lưu trạng thái</button>
                </form>

                <hr>

                <div class="small text-secondary mb-2">Thông tin thanh toán hiện có</div>
                <div><strong>Số giao dịch:</strong> {{ $booking->payments->count() }}</div>
                <div><strong>Đã ghi nhận thanh toán:</strong> {{ number_format((int) $booking->paid_amount) }}đ</div>
                <div><strong>Khách hàng liên kết:</strong>
                    @if($booking->customer)
                        <a href="{{ route('admin.customers.show', $booking->customer) }}">{{ $booking->customer->full_name }}</a>
                    @else
                        Không có
                    @endif
                </div>
                <div><strong>Xem suất chiếu:</strong>
                    @if($booking->show)
                        <a href="{{ route('admin.shows.show', $booking->show) }}">Mở chi tiết suất</a>
                    @else
                        Không có
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Chi tiết vé và ghế</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Ghế</th>
                    <th>Loại ghế</th>
                    <th>Loại vé</th>
                    <th>Vé điện tử</th>
                    <th>Đơn giá</th>
                    <th>Giảm giá</th>
                    <th>Thành tiền</th>
                    <th>Trạng thái vé</th>
                </tr>
            </thead>
            <tbody>
            @forelse($booking->tickets as $ticket)
                <tr>
                    <td class="fw-semibold">{{ $ticket->seat?->seat_code ?: ('#'.$ticket->seat_id) }}</td>
                    <td>{{ $ticket->seatType?->name ?: ('#'.$ticket->seat_type_id) }}</td>
                    <td>{{ $ticket->ticketType?->name ?: ('#'.$ticket->ticket_type_id) }}</td>
                    <td>
                        @if($ticket->ticket)
                            <a href="{{ route('admin.tickets.show', $ticket->ticket) }}" class="text-decoration-none">{{ $ticket->ticket->ticket_code }}</a>
                        @else
                            <span class="text-muted">Chưa phát hành</span>
                        @endif
                    </td>
                    <td>{{ number_format($ticket->unit_price_amount) }}đ</td>
                    <td>{{ number_format($ticket->discount_amount) }}đ</td>
                    <td>{{ number_format($ticket->final_price_amount) }}đ</td>
                    <td>{{ $ticket->status }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="empty-state">Booking chưa có vé nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Combo / sản phẩm F&B</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Giảm giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($booking->bookingProducts as $item)
                        <tr>
                            <td>{{ $item->product?->name ?: ('#'.$item->product_id) }}</td>
                            <td>{{ number_format($item->qty) }}</td>
                            <td>{{ number_format($item->unit_price_amount) }}đ</td>
                            <td>{{ number_format($item->discount_amount) }}đ</td>
                            <td>{{ number_format($item->final_amount) }}đ</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Booking không có sản phẩm F&B.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card h-100">
            <div class="card-header fw-semibold">Giảm giá / voucher áp dụng</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Khuyến mãi</th>
                            <th>Phạm vi</th>
                            <th>Mã coupon</th>
                            <th>Số tiền</th>
                            <th>Thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($booking->discounts as $discount)
                        <tr>
                            <td>
                                <div>{{ $discount->promotion?->name ?: 'Khuyến mãi hệ thống' }}</div>
                                <div class="list-secondary">{{ $discount->promotion?->code ?: 'AUTO' }}</div>
                            </td>
                            <td>{{ $discount->applied_to }}</td>
                            <td>{{ $discount->coupon?->code ?: 'Không dùng coupon' }}</td>
                            <td>{{ number_format($discount->discount_amount) }}đ</td>
                            <td>{{ optional($discount->created_at)->format('d/m/Y H:i') ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">Booking chưa áp dụng khuyến mãi nào.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Lịch sử thanh toán</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Provider / method</th>
                    <th>Trạng thái</th>
                    <th>Số tiền</th>
                    <th>Đã thanh toán</th>
                    <th>Ghi chú</th>
                </tr>
            </thead>
            <tbody>
            @forelse($booking->payments as $payment)
                <tr>
                    <td>
                        <div class="fw-semibold">{{ $payment->provider ?: 'N/A' }}</div>
                        <div class="list-secondary">{{ $payment->method ?: 'N/A' }}</div>
                    </td>
                    <td>{{ $payment->status }}</td>
                    <td>{{ number_format($payment->amount) }} {{ $payment->currency }}</td>
                    <td>{{ optional($payment->paid_at)->format('d/m/Y H:i') ?: 'Chưa thanh toán' }}</td>
                    <td>
                        <div class="list-secondary">{{ $payment->status === 'CAPTURED' ? 'Đã thu tiền thành công' : 'Theo dõi chi tiết trong màn giao dịch' }}</div>
                        <div class="list-secondary"><a href="{{ route('admin.payments.show', $payment) }}">Xem giao dịch</a></div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">Chưa có giao dịch thanh toán cho booking này.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
