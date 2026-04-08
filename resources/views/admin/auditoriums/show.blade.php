@extends('admin.layout')

@section('title', 'Chi tiết phòng chiếu')

<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
@push('styles')
<style>
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
    .chip-active { background: #dcfce7; color: #15803d; }
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
    .seat-active { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }
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
</style>
@endpush

@section('content')
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

    $groupedRows = $seats->groupBy('row_label')->map(function ($items, $row) use ($typeDetector) {
        $items = collect($items)->sortBy('col_number')->values();
        $sampleType = $items->pluck('seat_type_name')->filter()->countBy()->sortDesc()->keys()->first() ?? 'Ghế thường';
        return [
            'row' => $row,
            'section' => $typeDetector($sampleType),
            'type_name' => $sampleType,
            'items' => $items,
        ];
    })->groupBy('section');
@endphp
<<<<<<< HEAD
=======
=======
@section('content')
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
<section class="page-header">
    <div>
        <p class="eyebrow">Auditorium detail</p>
        <h2>{{ $auditorium->name }}</h2>
        <p>{{ $auditorium->auditorium_code }} · {{ $auditorium->screen_type }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.auditoriums.edit', $auditorium) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>
<div class="row g-3 mb-4">
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="fw-semibold">Tổng ghế</div><div class="display-6">{{ $seatStats['total'] ?? $seats->count() }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="fw-semibold">Ghế hoạt động</div><div class="display-6">{{ $seatStats['active'] ?? $seats->where('is_active', 1)->count() }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="fw-semibold">Ghế bảo trì</div><div class="display-6">{{ $seatStats['maintenance'] ?? $seats->where('is_active', 0)->count() }}</div></div></div></div>
    <div class="col-md-3"><div class="card"><div class="card-body"><div class="fw-semibold">Seat map version</div><div class="display-6">{{ $auditorium->seat_map_version }}</div></div></div></div>
</div>
<div class="card mb-4">
    <div class="card-header fw-semibold">Sơ đồ ghế phòng chiếu</div>
    <div class="card-body">
        <div class="seatmap-shell">
            <div class="screen-arc"><span>Màn hình</span></div>
            <p class="seatmap-note">Sơ đồ được trình bày giống chuẩn rạp thực tế: ghế thường phía trước, ghế VIP ở giữa và ghế đôi ở cuối phòng để bạn dễ kiểm tra bố cục ghế.</p>

            <div class="legend-wrap">
                <span class="legend-chip chip-active">Ghế hoạt động</span>
                <span class="legend-chip chip-maintenance">Ghế hỏng / bảo trì</span>
            </div>

            @foreach(['regular', 'vip', 'couple'] as $section)
                @continue(blank($groupedRows->get($section)))
                <div class="seat-section section-{{ $section }}">
                    <div class="seat-section-head">
                        <div>
                            <h3 class="seat-section-title">{{ $sectionTitles[$section]['title'] }}</h3>
                            <p class="seat-section-subtitle">{{ $sectionTitles[$section]['subtitle'] }}</p>
                        </div>
                        <span class="badge text-bg-light">{{ collect($groupedRows->get($section))->count() }} dãy</span>
                    </div>

                    @foreach(collect($groupedRows->get($section))->sortBy('row', SORT_NATURAL) as $rowData)
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
                                        @php $typeClass = $typeDetector($seat->seat_type_name); @endphp
                                        <div class="seat-tile {{ (int) $seat->is_active === 1 ? 'seat-active' : 'seat-maintenance' }} {{ $typeClass === 'couple' ? 'couple' : '' }} {{ $typeClass === 'vip' ? 'vip' : '' }}" title="{{ $seat->seat_type_name ?? 'Ghế' }}">
                                            <div>
                                                <span class="seat-code">{{ $seat->seat_code }}</span>
                                                <span class="seat-meta">{{ $seat->seat_type_name ?? 'Ghế' }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @if($rightBank->isNotEmpty())
                                    <div class="seat-bank">
                                        @foreach($rightBank as $seat)
                                            @php $typeClass = $typeDetector($seat->seat_type_name); @endphp
                                            <div class="seat-tile {{ (int) $seat->is_active === 1 ? 'seat-active' : 'seat-maintenance' }} {{ $typeClass === 'couple' ? 'couple' : '' }} {{ $typeClass === 'vip' ? 'vip' : '' }}" title="{{ $seat->seat_type_name ?? 'Ghế' }}">
                                                <div>
                                                    <span class="seat-code">{{ $seat->seat_code }}</span>
                                                    <span class="seat-meta">{{ $seat->seat_type_name ?? 'Ghế' }}</span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
<<<<<<< HEAD
=======
=======
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Tổng ghế</div><div class="display-6">{{ $seats->count() }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Seat map version</div><div class="display-6">{{ $auditorium->seat_map_version }}</div></div></div></div>
    <div class="col-md-4"><div class="card"><div class="card-body"><div class="fw-semibold">Hoạt động</div><div class="display-6">{{ $auditorium->is_active ? 'Có' : 'Không' }}</div></div></div></div>
</div>
<div class="card mb-4">
    <div class="card-header fw-semibold">Sơ đồ ghế</div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            @foreach($seats as $seat)
                <span class="badge badge-soft-primary">{{ $seat->seat_code }} · {{ $seat->seat_type_name }}</span>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            @endforeach
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header fw-semibold">Suất chiếu gần đây</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Phim</th><th>Bắt đầu</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        @forelse($auditorium->shows as $show)
            <tr>
                <td>{{ $show->movieVersion?->movie?->title ?: '-' }}</td>
                <td>{{ optional($show->start_time)->format('d/m/Y H:i') }}</td>
                <td>{{ $show->status }}</td>
                <td class="text-end"><a href="{{ route('admin.shows.show', $show) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Chưa có suất chiếu.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
