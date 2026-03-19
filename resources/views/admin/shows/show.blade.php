@extends('admin.layout')

@section('title', 'Chi tiết suất chiếu')

@push('styles')
<style>
    .seat-grid { display:grid; gap:10px; }
    .seat-row { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .seat-label { min-width:26px; font-weight:700; }
    .seat-tile { min-width:72px; border-radius:14px; border:1px solid rgba(15,23,42,.1); padding:8px 10px; font-size:.8rem; text-align:center; background:#fff; }
    .seat-tile button { border:0; background:transparent; width:100%; padding:0; }
    .seat-empty { background:#ecfdf5; }
    .seat-hold { background:#fef3c7; }
    .seat-booked { background:#fee2e2; }
    .seat-blocked { background:#e2e8f0; }
    .seat-maintenance { background:#dbeafe; }
    .legend-chip { padding:8px 12px; border-radius:999px; font-size:.85rem; font-weight:600; }
</style>
@endpush

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Show detail</p>
        <h2>{{ $show->movieVersion?->movie?->title ?: 'Suất chiếu' }}</h2>
        <p>{{ $show->auditorium?->name }} · {{ optional($show->start_time)->format('d/m/Y H:i') }} - {{ optional($show->end_time)->format('H:i') }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.shows.edit', $show) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.shows.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="fw-semibold">Số vé đã bán</div><div class="display-6">{{ $soldTickets }}</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="fw-semibold">Doanh thu</div><div class="display-6">{{ number_format($revenue) }}</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="fw-semibold">Tỷ lệ lấp đầy</div><div class="display-6">{{ $fillRate }}%</div></div></div></div>
    <div class="col-md-3"><div class="card h-100"><div class="card-body"><div class="fw-semibold">Tổng ghế hoạt động</div><div class="display-6">{{ $totalSeats }}</div></div></div></div>
</div>

<div class="card mb-4"><div class="card-body">
    <div class="row g-3">
        <div class="col-lg-4"><strong>Phim:</strong> {{ $show->movieVersion?->movie?->title ?: '-' }}</div>
        <div class="col-lg-4"><strong>Phòng chiếu:</strong> {{ $show->auditorium?->name ?: '-' }}</div>
        <div class="col-lg-4"><strong>Trạng thái:</strong> {{ $statusOptions[$show->status] ?? $show->status }}</div>
        <div class="col-lg-4"><strong>Ngày chiếu:</strong> {{ optional($show->start_time)->format('d/m/Y') }}</div>
        <div class="col-lg-4"><strong>Giờ bắt đầu:</strong> {{ optional($show->start_time)->format('H:i') }}</div>
        <div class="col-lg-4"><strong>Giờ kết thúc:</strong> {{ optional($show->end_time)->format('H:i') }}</div>
        <div class="col-lg-6"><strong>Hồ sơ giá:</strong> {{ $show->pricingProfile?->name ?: '—' }}</div>
        <div class="col-lg-6"><strong>Màn hình:</strong> {{ $show->auditorium?->screen_type ?: 'STANDARD' }}</div>
    </div>
</div></div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Ma trận giá vé theo suất chiếu</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead>
            <tr>
                <th>Loại ghế</th>
                @foreach($ticketTypeNames as $ticketType)
                    <th>{{ $ticketType }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
        @foreach($seatTypeNames as $seatTypeId => $seatType)
            <tr>
                <td>{{ $seatType }}</td>
                @foreach($ticketTypeNames as $ticketTypeId => $ticketType)
                    <td>{{ number_format($priceMatrix[$seatTypeId][$ticketTypeId] ?? 0) }} VND</td>
                @endforeach
            </tr>
        @endforeach
        </tbody>
    </table></div>
</div>

<div class="card mb-4">
    <div class="card-header fw-semibold">Sơ đồ ghế trực quan theo suất chiếu</div>
    <div class="card-body">
        <div class="d-flex gap-2 flex-wrap mb-3">
            <span class="legend-chip seat-empty">Trống</span>
            <span class="legend-chip seat-hold">Đang giữ</span>
            <span class="legend-chip seat-booked">Đã đặt</span>
            <span class="legend-chip seat-blocked">Khoá thủ công</span>
            <span class="legend-chip seat-maintenance">Ghế hỏng / bảo trì</span>
        </div>
        <div class="alert alert-light border small">Nhấn vào ghế màu xanh để khoá thủ công. Nhấn ghế màu xám để mở khoá. Ghế đang giữ/đã đặt không thể thay đổi trực tiếp.</div>
        <div class="seat-grid">
            @foreach($seats as $row => $items)
                <div class="seat-row">
                    <span class="seat-label">{{ $row }}</span>
                    @foreach($items as $seat)
                        @php
                            $cls = match($seat['status']) {
                                'booked' => 'seat-booked',
                                'hold' => 'seat-hold',
                                'blocked' => 'seat-blocked',
                                'maintenance' => 'seat-maintenance',
                                default => 'seat-empty',
                            };
                        @endphp
                        <div class="seat-tile {{ $cls }}" title="{{ $seat['seat_type_name'] }}">
                            @if($seat['status'] === 'empty')
                                <form method="POST" action="{{ route('admin.shows.seats.block', $show) }}">
                                    @csrf
                                    <input type="hidden" name="seat_id" value="{{ $seat['id'] }}">
                                    <input type="hidden" name="reason" value="Khoá thủ công từ admin">
                                    <button type="submit">
                                        <div class="fw-semibold">{{ $seat['seat_code'] }}</div>
                                        <div class="text-muted">{{ $seat['seat_type_name'] }}</div>
                                    </button>
                                </form>
                            @elseif($seat['status'] === 'blocked')
                                <form method="POST" action="{{ route('admin.shows.seats.unblock', [$show, $seat['block_id']]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit">
                                        <div class="fw-semibold">{{ $seat['seat_code'] }}</div>
                                        <div class="text-muted">Mở khoá</div>
                                    </button>
                                </form>
                            @else
                                <div class="fw-semibold">{{ $seat['seat_code'] }}</div>
                                <div class="text-muted">{{ $seat['seat_type_name'] }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header fw-semibold">Danh sách booking</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Mã</th><th>Khách hàng</th><th>Ghế</th><th>Tổng tiền</th><th>Trạng thái</th></tr></thead>
        <tbody>
        @forelse($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_code }}</td>
                <td>
                    <div>{{ $booking->contact_name }}</div>
                    <div class="text-muted small">{{ $booking->contact_phone }}</div>
                </td>
                <td>{{ $booking->tickets->map(fn($t) => $t->seat?->seat_code)->filter()->implode(', ') ?: '—' }}</td>
                <td>{{ number_format($booking->total_amount) }} {{ $booking->currency }}</td>
                <td>{{ $booking->status }}</td>
            </tr>
        @empty
            <tr><td colspan="5" class="empty-state">Chưa có booking nào cho suất chiếu này.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
