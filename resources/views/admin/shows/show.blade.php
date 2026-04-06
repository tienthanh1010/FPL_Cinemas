@extends('admin.layout')

@section('title', 'Chi tiết suất chiếu')

@push('styles')
<style>
<<<<<<< HEAD
    .seatmap-shell {
        background:
            radial-gradient(circle at top, rgba(59, 130, 246, .18), transparent 42%),
            linear-gradient(180deg, #0f172a 0%, #111827 55%, #0b1220 100%);
        border-radius: 28px;
        border: 1px solid rgba(148, 163, 184, .18);
        padding: 28px;
        box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
        color: #e5eefc;
    }
    .screen-arc {
        max-width: 760px;
        margin: 0 auto 28px;
        text-align: center;
    }
    .screen-arc::before {
        content: '';
        display: block;
        height: 30px;
        border-radius: 999px 999px 24px 24px;
        background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(191,219,254,.82));
        box-shadow: 0 10px 35px rgba(147, 197, 253, .35);
    }
    .screen-arc span {
        display: inline-block;
        margin-top: 10px;
        padding: 4px 12px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .06);
        color: #cbd5e1;
        font-size: .74rem;
        letter-spacing: .28em;
        text-transform: uppercase;
    }
    .seatmap-note {
        margin: 0 0 18px;
        color: #cbd5e1;
        font-size: .92rem;
    }
    .legend-wrap {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-bottom: 20px;
    }
    .legend-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 14px;
        border-radius: 999px;
        font-size: .84rem;
        font-weight: 700;
        color: #0f172a;
        background: #fff;
    }
    .legend-chip::before {
        content: '';
        width: 10px;
        height: 10px;
        border-radius: 999px;
        background: currentColor;
        box-shadow: 0 0 0 4px rgba(255,255,255,.12);
    }
    .chip-empty { background: #dcfce7; color: #15803d; }
    .chip-hold { background: #fef3c7; color: #b45309; }
    .chip-booked { background: #fee2e2; color: #b91c1c; }
    .chip-blocked { background: #e2e8f0; color: #475569; }
    .chip-maintenance { background: #dbeafe; color: #1d4ed8; }

    .seat-section {
        margin-top: 18px;
        padding: 18px;
        border-radius: 22px;
        background: rgba(255,255,255,.05);
        border: 1px solid rgba(148,163,184,.14);
        backdrop-filter: blur(6px);
    }
    .seat-section + .seat-section { margin-top: 16px; }
    .seat-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .seat-section-title {
        font-size: 1.02rem;
        font-weight: 800;
        margin: 0;
        color: #f8fafc;
    }
    .seat-section-subtitle {
        color: #93c5fd;
        font-size: .84rem;
        margin: 4px 0 0;
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .seat-row {
        display: grid;
        grid-template-columns: 44px 1fr;
        align-items: center;
        gap: 14px;
    }
    .seat-row + .seat-row { margin-top: 12px; }
    .seat-row-label {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border-radius: 14px;
        background: rgba(255,255,255,.09);
        border: 1px solid rgba(148,163,184,.18);
        color: #f8fafc;
        font-weight: 800;
    }
    .seat-row-banks {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 28px;
        flex-wrap: wrap;
    }
    .seat-bank {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        justify-content: center;
    }
    .seat-tile {
        min-width: 76px;
        min-height: 68px;
        border-radius: 18px 18px 14px 14px;
        border: 1px solid rgba(255,255,255,.12);
        padding: 8px 10px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        box-shadow: inset 0 -8px 0 rgba(15, 23, 42, .08), 0 8px 18px rgba(15, 23, 42, .16);
        transition: transform .18s ease, box-shadow .18s ease, filter .18s ease;
        overflow: hidden;
    }
    .seat-tile:hover { transform: translateY(-2px); box-shadow: inset 0 -8px 0 rgba(15, 23, 42, .08), 0 14px 22px rgba(15, 23, 42, .24); }
    .seat-tile button {
        border: 0;
        background: transparent;
        width: 100%;
        padding: 0;
        color: inherit;
    }
    .seat-tile.couple { min-width: 118px; }
    .seat-tile.vip { box-shadow: inset 0 -8px 0 rgba(120, 53, 15, .10), 0 10px 20px rgba(245, 158, 11, .18); }
    .seat-tile .seat-code {
        display: block;
        font-weight: 800;
        font-size: .95rem;
        color: #0f172a;
        line-height: 1.1;
    }
    .seat-tile .seat-meta {
        display: block;
        margin-top: 4px;
        font-size: .73rem;
        color: #475569;
        line-height: 1.2;
    }
    .seat-empty { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }
    .seat-hold { background: linear-gradient(180deg, #fffbeb 0%, #fef3c7 100%); }
    .seat-booked { background: linear-gradient(180deg, #fef2f2 0%, #fee2e2 100%); }
    .seat-blocked { background: linear-gradient(180deg, #f8fafc 0%, #e2e8f0 100%); }
    .seat-maintenance { background: linear-gradient(180deg, #eff6ff 0%, #dbeafe 100%); }
    .section-regular .seat-section-subtitle { color: #86efac; }
    .section-vip .seat-section-subtitle { color: #fbbf24; }
    .section-couple .seat-section-subtitle { color: #f9a8d4; }

    @media (max-width: 768px) {
        .seatmap-shell { padding: 18px; }
        .seat-row { grid-template-columns: 1fr; }
        .seat-row-label { margin: 0 auto; }
        .seat-row-banks { gap: 12px; }
        .seat-bank { gap: 8px; }
        .seat-tile, .seat-tile.couple { min-width: 72px; }
    }
=======
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
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
</style>
@endpush

@section('content')
<<<<<<< HEAD
@php
    $typeDetector = function ($typeName) {
        $type = mb_strtolower((string) $typeName);
        if (str_contains($type, 'doi') || str_contains($type, 'đôi') || str_contains($type, 'couple')) {
            return 'couple';
        }
        if (str_contains($type, 'vip')) {
            return 'vip';
        }
        return 'regular';
    };

    $sectionTitles = [
        'regular' => ['title' => 'Khu ghế thường', 'subtitle' => 'Phía trước màn hình'],
        'vip' => ['title' => 'Khu ghế VIP', 'subtitle' => 'Trung tâm tầm nhìn đẹp'],
        'couple' => ['title' => 'Khu ghế đôi', 'subtitle' => 'Hàng sau cùng của phòng chiếu'],
    ];

    $orderedRows = collect($seats)
        ->map(function ($items, $row) use ($typeDetector) {
            $items = collect($items)->sortBy('col_number')->values();
            $sampleType = $items->pluck('seat_type_name')->filter()->countBy()->sortDesc()->keys()->first() ?? 'Ghế thường';
            return [
                'row' => $row,
                'section' => $typeDetector($sampleType),
                'type_name' => $sampleType,
                'items' => $items,
            ];
        })
        ->groupBy('section');
@endphp
=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
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
<<<<<<< HEAD
        <div class="seatmap-shell">
            <div class="screen-arc"><span>Màn hình</span></div>
            <p class="seatmap-note">Sơ đồ đã được sắp theo chuẩn rạp: ghế thường ở phía trước, ghế VIP ở giữa và ghế đôi ở hàng dưới cùng. Nhấn ghế trống để khoá, nhấn ghế đang khoá để mở khoá.</p>

            <div class="legend-wrap">
                <span class="legend-chip chip-empty">Trống</span>
                <span class="legend-chip chip-hold">Đang giữ</span>
                <span class="legend-chip chip-booked">Đã đặt</span>
                <span class="legend-chip chip-blocked">Khoá thủ công</span>
                <span class="legend-chip chip-maintenance">Ghế hỏng / bảo trì</span>
            </div>

            @foreach(['regular', 'vip', 'couple'] as $section)
                @continue(blank($orderedRows->get($section)))
                <div class="seat-section section-{{ $section }}">
                    <div class="seat-section-head">
                        <div>
                            <h3 class="seat-section-title">{{ $sectionTitles[$section]['title'] }}</h3>
                            <p class="seat-section-subtitle">{{ $sectionTitles[$section]['subtitle'] }}</p>
                        </div>
                        <span class="badge text-bg-light">{{ collect($orderedRows->get($section))->count() }} dãy</span>
                    </div>

                    @foreach(collect($orderedRows->get($section))->sortBy('row', SORT_NATURAL) as $rowData)
                        @php
                            $rowSeats = collect($rowData['items'])->values();
                            $half = (int) ceil($rowSeats->count() / 2);
                            $leftBank = $rowSeats->take($half);
                            $rightBank = $rowSeats->slice($half);
                        @endphp
                        <div class="seat-row">
                            <div class="seat-row-label">{{ $rowData['row'] }}</div>
                            <div class="seat-row-banks">
                                <div class="seat-bank">
                                    @foreach($leftBank as $seat)
                                        @php
                                            $statusClass = match($seat['status']) {
                                                'booked' => 'seat-booked',
                                                'hold' => 'seat-hold',
                                                'blocked' => 'seat-blocked',
                                                'maintenance' => 'seat-maintenance',
                                                default => 'seat-empty',
                                            };
                                            $typeClass = $typeDetector($seat['seat_type_name']);
                                        @endphp
                                        <div class="seat-tile {{ $statusClass }} {{ $typeClass === 'couple' ? 'couple' : '' }} {{ $typeClass === 'vip' ? 'vip' : '' }}" title="{{ $seat['seat_type_name'] }}">
                                            @if($seat['status'] === 'empty')
                                                <form method="POST" action="{{ route('admin.shows.seats.block', $show) }}">
                                                    @csrf
                                                    <input type="hidden" name="seat_id" value="{{ $seat['id'] }}">
                                                    <input type="hidden" name="reason" value="Khoá thủ công từ admin">
                                                    <button type="submit">
                                                        <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                        <span class="seat-meta">{{ $seat['seat_type_name'] }}</span>
                                                    </button>
                                                </form>
                                            @elseif($seat['status'] === 'blocked')
                                                <form method="POST" action="{{ route('admin.shows.seats.unblock', [$show, $seat['block_id']]) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit">
                                                        <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                        <span class="seat-meta">Mở khoá</span>
                                                    </button>
                                                </form>
                                            @else
                                                <div>
                                                    <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                    <span class="seat-meta">{{ $seat['seat_type_name'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                                @if($rightBank->isNotEmpty())
                                    <div class="seat-bank">
                                        @foreach($rightBank as $seat)
                                            @php
                                                $statusClass = match($seat['status']) {
                                                    'booked' => 'seat-booked',
                                                    'hold' => 'seat-hold',
                                                    'blocked' => 'seat-blocked',
                                                    'maintenance' => 'seat-maintenance',
                                                    default => 'seat-empty',
                                                };
                                                $typeClass = $typeDetector($seat['seat_type_name']);
                                            @endphp
                                            <div class="seat-tile {{ $statusClass }} {{ $typeClass === 'couple' ? 'couple' : '' }} {{ $typeClass === 'vip' ? 'vip' : '' }}" title="{{ $seat['seat_type_name'] }}">
                                                @if($seat['status'] === 'empty')
                                                    <form method="POST" action="{{ route('admin.shows.seats.block', $show) }}">
                                                        @csrf
                                                        <input type="hidden" name="seat_id" value="{{ $seat['id'] }}">
                                                        <input type="hidden" name="reason" value="Khoá thủ công từ admin">
                                                        <button type="submit">
                                                            <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                            <span class="seat-meta">{{ $seat['seat_type_name'] }}</span>
                                                        </button>
                                                    </form>
                                                @elseif($seat['status'] === 'blocked')
                                                    <form method="POST" action="{{ route('admin.shows.seats.unblock', [$show, $seat['block_id']]) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit">
                                                            <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                            <span class="seat-meta">Mở khoá</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <div>
                                                        <span class="seat-code">{{ $seat['seat_code'] }}</span>
                                                        <span class="seat-meta">{{ $seat['seat_type_name'] }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
=======
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
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
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
