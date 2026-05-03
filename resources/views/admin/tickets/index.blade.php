@extends('admin.layout')

@section('title', 'Soát vé / check-in')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Ticket Gate</p>
        <h2>Soát vé / check-in</h2>
        <p>Tra cứu vé theo mã vé, booking, số điện thoại hoặc email; theo dõi trạng thái đã check-in và xử lý nhanh tại cổng soát vé.</p>
    </div>
</section>

<div class="card toolbar-card mb-3 border border-success-subtle">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="POST" action="{{ route('admin.tickets.quick_checkin') }}">
            @csrf
            <div class="col-lg-8">
                <label class="form-label">Check-in nhanh bằng mã vé</label>
                <input class="form-control form-control-lg" name="ticket_code" placeholder="Nhập hoặc quét mã vé, ví dụ: T6TUUSY069C1" autofocus>
            </div>
            <div class="col-lg-4 d-grid">
                <button class="btn btn-success btn-lg"><i class="bi bi-qr-code-scan me-1"></i>Check-in ngay</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng số vé</div><div class="metric-value">{{ number_format($summary['tickets']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Chưa check-in</div><div class="metric-value">{{ number_format($summary['issued']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đã check-in</div><div class="metric-value">{{ number_format($summary['used']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Không còn hiệu lực</div><div class="metric-value">{{ number_format($summary['invalid']) }}</div></div></div>
</div>

<div class="card toolbar-card mb-3">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-4">
                <label class="form-label">Tìm kiếm</label>
                <input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Mã vé / booking / SĐT / email / tên khách">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái vé</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái booking</label>
                <select class="form-select" name="booking_status">
                    <option value="">Tất cả</option>
                    @foreach($bookingStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['booking_status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Phim</label>
                <select class="form-select" name="movie_id">
                    <option value="">Tất cả phim</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}" @selected((int) $filters['movie_id'] === (int) $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Suất chiếu</label>
                <select class="form-select" name="show_id">
                    <option value="">Tất cả suất</option>
                    @foreach($shows as $show)
                        <option value="{{ $show->id }}" @selected((int) $filters['show_id'] === (int) $show->id)>
                            {{ $show->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($show->start_time)->format('d/m/Y H:i') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="form-label">Từ ngày chiếu</label>
                <input type="date" class="form-control" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Đến ngày chiếu</label>
                <input type="date" class="form-control" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
            <div class="col-lg-4 d-flex gap-2">
                <button class="btn btn-primary flex-fill"><i class="bi bi-search me-1"></i>Tìm & lọc</button>
                <a href="{{ route('admin.tickets.index') }}" class="btn btn-light-soft flex-fill">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Vé</th>
                    <th>Khách / booking</th>
                    <th>Phim / suất / ghế</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($tickets as $ticket)
                @php
                    $bookingTicket = $ticket->bookingTicket;
                    $booking = $bookingTicket?->booking;
                    $seat = $bookingTicket?->seat;
                    $show = $booking?->show;
                    $badgeClass = match($ticket->status) {
                        'USED' => 'badge-soft-success',
                        'PRINTED' => 'badge-soft-primary',
                        'ISSUED' => 'badge-soft-warning',
                        'VOID', 'REFUNDED' => 'badge-soft-danger',
                        default => 'badge-soft-secondary',
                    };
                @endphp
                <tr>
                    <td>
                        <div class="list-primary">{{ $ticket->ticket_code }}</div>
                        <div class="list-secondary">Phát hành: {{ optional($ticket->issued_at)->format('d/m/Y H:i') ?: '—' }}</div>
                        <div class="list-secondary">Check-in: {{ optional($ticket->used_at)->format('d/m/Y H:i') ?: 'Chưa check-in' }}</div>
                        <div class="list-secondary">In vé: {{ optional($ticket->printed_at)->format('d/m/Y H:i') ?: 'Chưa in vé' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $booking?->contact_name ?: ($booking?->customer?->full_name ?? 'Khách lẻ') }}</div>
                        <div>{{ $booking?->booking_code ?: 'Không có booking' }}</div>
                        <div class="list-secondary">{{ $booking?->contact_phone ?: ($booking?->contact_email ?: 'Chưa có liên hệ') }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $show?->movieVersion?->movie?->title ?? 'Suất chiếu' }}</div>
                        <div>{{ optional($show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }} · {{ $show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                        <div class="list-secondary">Ghế {{ $seat?->seat_code ?: ('#'.($bookingTicket?->seat_id ?? 'N/A')) }} · {{ $bookingTicket?->seatType?->name ?: 'Loại ghế' }}</div>
                    </td>
                    <td>
                        <div><span class="badge {{ $badgeClass }}">{{ $statusOptions[$ticket->status] ?? $ticket->status }}</span></div>
                        <div class="list-secondary mt-1">Booking: {{ $bookingStatusOptions[$booking?->status] ?? ($booking?->status ?: '—') }}</div>
                    </td>
                    <td class="text-end">
                        <div class="d-flex justify-content-end gap-2 flex-wrap">
                            <a href="{{ route('admin.tickets.show', $ticket) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                            @if($ticket->status === 'ISSUED')
                                <form method="POST" action="{{ route('admin.tickets.checkin', $ticket) }}" onsubmit="return confirm('Xác nhận check-in vé này?')">
                                    @csrf
                                    <button class="btn btn-sm btn-success">Check-in</button>
                                </form>
                            @elseif($ticket->status === 'USED')
                                <form method="POST" action="{{ route('admin.tickets.print', $ticket) }}" target="_blank" onsubmit="return confirm('Xác nhận in vé cứng? Sau khi in, trạng thái vé sẽ chuyển sang Đã in vé.')">
                                    @csrf
                                    <button class="btn btn-sm btn-primary">In vé</button>
                                </form>
                                <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}" onsubmit="return confirm('Mở lại vé này để kiểm vé lại?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning">Mở lại</button>
                                </form>
                            @elseif($ticket->status === 'PRINTED')
                                <form method="POST" action="{{ route('admin.tickets.print', $ticket) }}" target="_blank">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary">In lại</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty-state">Chưa có vé nào khớp điều kiện lọc.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $tickets->links() }}</div>
</div>
@endsection
