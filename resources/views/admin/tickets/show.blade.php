@extends('admin.layout')

@section('title', 'Chi tiết vé / check-in')

@section('content')
@php
    $bookingTicket = $ticket->bookingTicket;
    $seat = $bookingTicket?->seat;
    $show = $booking?->show;
    $badgeClass = match($ticket->status) {
        'USED' => 'badge-soft-success',
        'PRINTED' => 'badge-soft-primary',
        'ISSUED' => 'badge-soft-warning',
        'VOID', 'REFUNDED' => 'badge-soft-danger',
        default => 'badge-soft-secondary',
    };
    $canTicketCredit = $ticket->status === 'ISSUED'
        && empty($compensationMeta)
        && $booking
        && in_array((string) $booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true)
        && (int) ($booking->paid_amount ?? 0) > 0;
@endphp

<section class="page-header">
    <div>
        <p class="eyebrow">Ticket detail</p>
        <h2>{{ $ticket->ticket_code }}</h2>
        <p>{{ $show?->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.tickets.index') }}" class="btn btn-light-soft">Quay lại</a>
        @if($booking)
            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-outline-secondary">Mở booking</a>
        @endif
        @if($ticket->status === 'ISSUED')
            <form method="POST" action="{{ route('admin.tickets.checkin', $ticket) }}" onsubmit="return confirm('Xác nhận check-in vé này?')">
                @csrf
                <button class="btn btn-success">Check-in vé</button>
            </form>
            @if($canTicketCredit)
                <form method="POST" action="{{ route('admin.tickets.compensate', $ticket) }}" onsubmit="return confirm('Tạo hoàn ticket cho vé này và vô hiệu vé hiện tại?')">
                    @csrf
                    <button class="btn btn-outline-danger">Hoàn ticket</button>
                </form>
            @endif
        @elseif(in_array($ticket->status, ['USED', 'PRINTED']))
            <form method="POST" action="{{ route('admin.tickets.print', $ticket) }}" target="_blank" @if($ticket->status === 'USED') onsubmit="return confirm('Xác nhận in vé cứng cho vé này? Sau khi in, trạng thái vé sẽ chuyển sang Đã in vé.')" @endif>
                @csrf
                <button class="btn btn-primary">{{ $ticket->status === 'PRINTED' ? 'In lại vé cứng' : 'In vé cứng' }}</button>
            </form>
            <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}" onsubmit="return confirm('Mở lại vé để kiểm vé lại?')">
                @csrf
                <button class="btn btn-outline-warning">Mở lại vé</button>
            </form>
        @endif
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Trạng thái vé</div><div class="metric-value fs-5"><span class="badge {{ $badgeClass }}">{{ $statusOptions[$ticket->status] ?? $ticket->status }}</span></div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Ghế trong booking</div><div class="metric-value">{{ number_format($metrics['booking_ticket_count']) }}</div><div class="metric-caption">Cùng booking hiện tại</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đã check-in</div><div class="metric-value">{{ number_format($metrics['used_count']) }}</div><div class="metric-caption">Trong cùng booking</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Chưa check-in</div><div class="metric-value">{{ number_format($metrics['issued_count']) }}</div><div class="metric-caption">Trong cùng booking</div></div></div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header fw-semibold">Thông tin vé</div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6"><strong>Mã vé:</strong> {{ $ticket->ticket_code }}</div>
                    <div class="col-md-6"><strong>Mã scan:</strong> {{ ticket_scan_payload($ticket) ?: 'Chưa có' }}</div>
                    <div class="col-md-6"><strong>Phát hành lúc:</strong> {{ optional($ticket->issued_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="col-md-6"><strong>Check-in lúc:</strong> {{ optional($ticket->used_at)->format('d/m/Y H:i') ?: 'Chưa check-in' }}</div>
                    <div class="col-md-6"><strong>In vé lúc:</strong> {{ optional($ticket->printed_at)->format('d/m/Y H:i') ?: 'Chưa in vé' }}</div>
                    <div class="col-md-6"><strong>Ghế:</strong> {{ $seat?->seat_code ?: ('#'.($bookingTicket?->seat_id ?? 'N/A')) }}</div>
                    <div class="col-md-6"><strong>Loại ghế:</strong> {{ $bookingTicket?->seatType?->name ?: ('#'.($bookingTicket?->seat_type_id ?? 'N/A')) }}</div>
                    <div class="col-md-6"><strong>Giá vé:</strong> {{ number_format($bookingTicket?->final_price_amount ?? 0) }}đ</div>
                    <div class="col-md-8"><strong>Phim:</strong> {{ $show?->movieVersion?->movie?->title ?? 'Chưa có' }}</div>
                    <div class="col-md-4"><strong>Suất chiếu:</strong> {{ optional($show?->start_time)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="col-md-6"><strong>Phòng:</strong> {{ $show?->auditorium?->name ?: 'Chưa có' }}</div>
                    <div class="col-md-6"><strong>Rạp:</strong> {{ $show?->auditorium?->cinema?->name ?: 'Chưa có' }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Hoàn ticket ghế hỏng</div>
            <div class="card-body">
                @if(!empty($compensationMeta))
                    <div class="alert alert-success mb-3">Vé này đã được hoàn ticket, không hoàn tiền mặt.</div>
                    <div><strong>Mã hoàn ticket:</strong> {{ $compensationMeta['coupon_code'] ?? '—' }}</div>
                    <div><strong>Giá trị:</strong> {{ number_format((int) ($compensationMeta['amount'] ?? 0)) }}đ</div>
                    <div><strong>Lý do:</strong> Ghế gặp sự cố kỹ thuật / không thể phục vụ.</div>
                    <div><strong>Tạo lúc:</strong> {{ !empty($compensationLog?->created_at) ? \Carbon\Carbon::parse($compensationLog->created_at)->format('d/m/Y H:i') : '—' }}</div>
                    <div class="small text-secondary mt-2">Khách có thể dùng ticket credit này cho booking mới thay cho hoàn tiền trực tiếp.</div>
                @else
                    <div class="small text-secondary mb-3">Dùng khi ghế bị hỏng, không thể phục vụ và không còn ghế thay thế. Hệ thống sẽ tạo ticket credit đúng bằng giá vé khách đã mua.</div>
                    @if($canTicketCredit)
                        <form method="POST" action="{{ route('admin.tickets.compensate', $ticket) }}" onsubmit="return confirm('Xác nhận hoàn ticket cho vé này?')">
                            @csrf
                            <button class="btn btn-danger">Tạo hoàn ticket</button>
                        </form>
                    @else
                        <div class="text-muted">Chỉ booking đã thanh toán và vé đang phát hành mới có thể hoàn ticket.</div>
                    @endif
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Mã quét vé</div>
            <div class="card-body text-center">
                @php($scanPayload = ticket_scan_payload($ticket))
                @if($scanPayload)
                    <div class="mb-3">
                        <img src="{{ ticket_barcode_image_url($scanPayload, 76) }}" alt="Barcode {{ $ticket->ticket_code }}" class="img-fluid rounded-3 border bg-white p-2">
                    </div>
                    <div class="small text-secondary mb-2">Scan mã vạch từ vé cứng hoặc màn hình điện thoại để check-in.</div>
                    <div class="fw-semibold text-break">{{ $scanPayload }}</div>
                @else
                    <div class="text-muted">Vé này chưa có dữ liệu mã vạch.</div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header fw-semibold">Khách hàng / booking</div>
            <div class="card-body">
                <div><strong>Booking:</strong> {{ $booking?->booking_code ?: 'Không có' }}</div>
                <div><strong>Trạng thái booking:</strong> {{ $bookingStatusOptions[$booking?->status] ?? ($booking?->status ?: '—') }}</div>
                <div><strong>Khách hàng:</strong> {{ $booking?->contact_name ?: ($booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                <div><strong>SĐT:</strong> {{ $booking?->contact_phone ?: 'Chưa có' }}</div>
                <div><strong>Email:</strong> {{ $booking?->contact_email ?: 'Chưa có' }}</div>
                <div><strong>Đã thu:</strong> {{ number_format($booking?->paid_amount ?? 0) }}đ</div>
                <div><strong>Tổng tiền booking:</strong> {{ number_format($booking?->total_amount ?? 0) }}đ</div>

                <hr>

                <div class="small text-secondary mb-2">Lưu ý check-in</div>
                <ul class="small text-secondary mb-0 ps-3">
                    <li>Chỉ vé ở trạng thái <strong>ISSUED</strong> mới được check-in.</li>
                    <li>Booking phải ở trạng thái đã thanh toán/xác nhận/hoàn tất.</li>
                    <li>Vé <strong>VOID</strong> hoặc <strong>REFUNDED</strong> không thể check-in.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Các vé khác cùng booking</div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Mã vé</th>
                    <th>Ghế</th>
                    <th>Trạng thái</th>
                    <th>Check-in lúc</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookingTickets as $item)
                @php($itemSeat = $item->bookingTicket?->seat)
                <tr>
                    <td class="fw-semibold">{{ $item->ticket_code }}</td>
                    <td>{{ $itemSeat?->seat_code ?: ('#'.($item->bookingTicket?->seat_id ?? 'N/A')) }}</td>
                    <td>{{ $statusOptions[$item->status] ?? $item->status }}</td>
                    <td>{{ optional($item->used_at)->format('d/m/Y H:i') ?: 'Chưa check-in' }}</td>
                    <td class="text-end"><a href="{{ route('admin.tickets.show', $item) }}" class="btn btn-sm btn-outline-secondary">Xem</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">Booking này chưa có vé điện tử nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
