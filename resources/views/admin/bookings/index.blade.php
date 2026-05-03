@extends('admin.layout')

@section('title', 'Quản lý booking')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Booking Center</p>
        <h2>Danh sách booking / đơn đặt vé</h2>
        <p>Tìm nhanh theo mã booking, số điện thoại, email và lọc theo phim, suất chiếu, ngày chiếu, trạng thái.</p>
    </div>
</section>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng booking</div><div class="metric-value">{{ number_format($summary['bookings']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tổng vé</div><div class="metric-value">{{ number_format($summary['tickets']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Doanh thu</div><div class="metric-value">{{ number_format($summary['revenue']) }}đ</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Đơn kết thúc</div><div class="metric-value">{{ number_format($summary['cancelled']) }}</div></div></div>
</div>

<div class="card toolbar-card mb-3">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-4">
                <label class="form-label">Tìm kiếm</label>
                <input class="form-control" name="q" value="{{ $filters['q'] }}" placeholder="Mã booking / SĐT / email / tên khách hàng">
            </div>
            <div class="col-lg-2">
                <label class="form-label">Trạng thái</label>
                <select class="form-select" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Phim</label>
                <select class="form-select" name="movie_id">
                    <option value="">Tất cả phim</option>
                    @foreach($movies as $movie)
                        <option value="{{ $movie->id }}" @selected((int) $filters['movie_id'] === (int) $movie->id)>{{ $movie->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Suất chiếu</label>
                <select class="form-select" name="show_id">
                    <option value="">Tất cả suất</option>
                    @foreach($shows as $show)
                        <option value="{{ $show->id }}" @selected((int) $filters['show_id'] === (int) $show->id)>
                            {{ $show->movieVersion?->movie?->title ?? 'Suất chiếu' }} · {{ optional($show->start_time)->format('d/m/Y H:i') }} · {{ $show->auditorium?->name }}
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
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-light-soft flex-fill">Xoá lọc</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Booking</th>
                    <th>Khách hàng</th>
                    <th>Phim / suất</th>
                    <th>Ghế / combo</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($bookings as $booking)
                @php
                    $badgeClass = match($booking->status) {
                        'PAID', 'CONFIRMED', 'COMPLETED' => 'badge-soft-success',
                        'CANCELLED' => 'badge-soft-danger',
                        'EXPIRED' => 'badge-soft-secondary',
                        default => 'badge-soft-warning',
                    };
                    $seatCodes = $booking->tickets->map(fn($ticket) => $ticket->seat?->seat_code)->filter()->implode(', ');
                    $comboCount = (int) $booking->bookingProducts->sum('qty');
                @endphp
                <tr>
                    <td>
                        <div class="list-primary">{{ $booking->booking_code }}</div>
                        <div class="list-secondary">Tạo lúc {{ optional($booking->created_at)->format('d/m/Y H:i') }}</div>
                        <div class="list-secondary">{{ $booking->currency }}</div>
                    </td>
                    <td>
                        <div>{{ $booking->contact_name ?: ($booking->customer?->full_name ?? 'Khách lẻ') }}</div>
                        <div class="list-secondary">{{ $booking->contact_phone ?: 'Chưa có SĐT' }}</div>
                        <div class="list-secondary">{{ $booking->contact_email ?: 'Chưa có email' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ $booking->show?->movieVersion?->movie?->title ?? 'Suất chiếu' }}</div>
                        <div class="list-secondary">{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: 'Chưa có lịch' }}</div>
                        <div class="list-secondary">{{ $booking->show?->auditorium?->name ?: 'Chưa có phòng' }}</div>
                    </td>
                    <td>
                        <div>{{ $booking->tickets->count() }} vé</div>
                        <div class="list-secondary">Ghế: {{ $seatCodes ?: '—' }}</div>
                        <div class="list-secondary">Combo: {{ $comboCount > 0 ? $comboCount . ' món' : 'Không có' }}</div>
                    </td>
                    <td>
                        <div class="list-primary">{{ number_format($booking->total_amount) }}đ</div>
                        <div class="list-secondary">Giảm giá {{ number_format($booking->discount_amount) }}đ</div>
                        <div class="list-secondary">Đã thu {{ number_format($booking->paid_amount) }}đ</div>
                    </td>
                    <td><span class="badge {{ $badgeClass }}">{{ $statusOptions[$booking->status] ?? $booking->status }}</span></td>
                    <td class="text-end">
                        <div class="d-inline-flex gap-2 flex-wrap justify-content-end">
                            <a href="{{ route('admin.bookings.show', $booking) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                            @if(! in_array($booking->status, ['CANCELLED', 'EXPIRED'], true))
                                <form method="POST" action="{{ route('admin.bookings.cancel', $booking) }}" onsubmit="return confirm('Huỷ booking này và trả lại ghế/tồn kho?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-danger">Huỷ đơn</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty-state">Chưa có booking nào khớp điều kiện lọc.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $bookings->links() }}</div>
</div>
@endsection
