@extends('admin.layout')

@section('title', 'Báo cáo vận hành')
@section('page_title', 'Báo cáo vận hành')
@section('page_subtitle', 'Doanh thu chỉ tính payment CAPTURED, vé bán chỉ tính vé ISSUED; ghế bị huỷ/hết hạn không còn ảnh hưởng số vé/lấp đầy.')

@section('content')
@php
    $fmt = fn($amount) => number_format((int) $amount) . 'đ';
    $periodNames = ['day' => 'Theo ngày', 'month' => 'Theo tháng', 'quarter' => 'Theo quý', 'year' => 'Theo năm', 'custom' => 'Tùy chọn'];
@endphp

<div class="card toolbar-card mb-4">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-2">
                <label class="form-label">Kiểu báo cáo</label>
                <select name="period" class="form-select" id="reportPeriod">
                    @foreach($periodNames as $key => $label)
                        <option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 report-date-field">
                <label class="form-label">Ngày đại diện</label>
                <input type="date" name="date" value="{{ request('date', now()->toDateString()) }}" class="form-control">
                <div class="form-text">Tháng/quý/năm sẽ lấy theo ngày này.</div>
            </div>
            <div class="col-lg-2 custom-date-field">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="start_date" value="{{ request('start_date', $start->toDateString()) }}" class="form-control">
            </div>
            <div class="col-lg-2 custom-date-field">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="end_date" value="{{ request('end_date', $end->toDateString()) }}" class="form-control">
            </div>
            <div class="col-lg-2">
                <button class="btn btn-primary w-100"><i class="bi bi-funnel me-1"></i>Xem báo cáo</button>
            </div>
            <div class="col-lg-2">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-light-soft w-100">Về tháng hiện tại</a>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info border-0 rounded-4 mb-4">
    <div class="fw-bold">{{ $periodLabel }}: {{ $start->format('d/m/Y') }} - {{ $end->format('d/m/Y') }}</div>
    <div class="small">Logic chuẩn: doanh thu = payment đã thu - refund thành công; vé bán/lấp đầy = booking_tickets.status = ISSUED. Booking PENDING, CANCELLED, EXPIRED hoặc ghế đã nhả không được tính vào báo cáo.</div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Suất chiếu</div><div class="metric-value">{{ number_format($summary['shows']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Vé đã bán</div><div class="metric-value">{{ number_format($summary['tickets']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Doanh thu ròng</div><div class="metric-value">{{ $fmt($summary['revenue']) }}</div><div class="metric-hint">Tổng thu {{ $fmt($summary['gross_revenue']) }} · Hoàn {{ $fmt($summary['refund_amount']) }}</div></div></div>
    <div class="col-md-3"><div class="metric-card"><div class="metric-label">Tỷ lệ lấp đầy</div><div class="metric-value">{{ $summary['occupancy'] }}%</div></div></div>
</div>

<div class="row g-3 mb-4">
    @foreach(['day' => 'Hôm nay', 'month' => 'Tháng này', 'quarter' => 'Quý này', 'year' => 'Năm nay'] as $key => $label)
        <div class="col-md-3">
            <div class="section-card h-100 mb-0">
                <div class="section-description mb-2">{{ $label }}</div>
                <h4 class="mb-1">{{ $fmt($quickSummaries[$key]['revenue']) }}</h4>
                <div class="small text-secondary">{{ number_format($quickSummaries[$key]['tickets']) }} vé · {{ number_format($quickSummaries[$key]['shows']) }} suất · {{ $quickSummaries[$key]['occupancy'] }}%</div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Doanh thu theo phim</div><div class="small text-secondary">Xếp hạng theo payment đã thu trừ refund.</div></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>Phim</th><th>Booking đã thu</th><th>Doanh thu ròng</th></tr></thead>
                <tbody>
                @forelse($revenueByMovie as $row)
                    <tr><td class="fw-semibold">{{ $row->title }}</td><td>{{ number_format($row->booking_count) }}</td><td>{{ $fmt($row->revenue) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty-state">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Doanh thu theo phòng</div><div class="small text-secondary">So sánh hiệu quả từng phòng chiếu.</div></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>Phòng</th><th>Loại</th><th>Booking</th><th>Doanh thu</th></tr></thead>
                <tbody>
                @forelse($revenueByAuditorium as $row)
                    <tr><td class="fw-semibold">{{ $row->name }}</td><td>{{ $row->screen_type }}</td><td>{{ number_format($row->booking_count) }}</td><td>{{ $fmt($row->revenue) }}</td></tr>
                @empty
                    <tr><td colspan="4" class="empty-state">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Số vé theo khung giờ</div><div class="small text-secondary">Chỉ tính vé đã phát hành.</div></div>
            <div class="card-body d-grid gap-3">
                @forelse($ticketsByHour as $row)
                    <div>
                        <div class="d-flex justify-content-between"><span>{{ str_pad($row->hour_slot, 2, '0', STR_PAD_LEFT) }}:00</span><strong>{{ number_format($row->tickets_sold) }} vé</strong></div>
                        <div class="progress mt-2" style="height:8px"><div class="progress-bar" style="width: {{ min(($ticketsByHour->max('tickets_sold') ? ($row->tickets_sold / $ticketsByHour->max('tickets_sold')) * 100 : 0), 100) }}%"></div></div>
                    </div>
                @empty
                    <div class="empty-state">Chưa có dữ liệu.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-7">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Tỷ lệ lấp đầy theo ngày</div><div class="small text-secondary">Ghế huỷ/hết hạn không tính là ghế đã bán.</div></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>Ngày</th><th>Số suất</th><th>Vé bán</th><th>Lấp đầy</th></tr></thead>
                <tbody>
                @forelse($occupancyByDay as $row)
                    <tr><td class="fw-semibold">{{ \Carbon\Carbon::parse($row->report_date)->format('d/m/Y') }}</td><td>{{ number_format($row->shows_count) }}</td><td>{{ number_format($row->tickets_sold) }}</td><td>{{ $row->occupancy_rate }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="empty-state">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Top phim bán chạy</div><div class="small text-secondary">Xếp hạng theo số vé đã phát hành.</div></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>Phim</th><th>Vé bán</th><th>Doanh thu vé trước giảm</th></tr></thead>
                <tbody>
                @forelse($topMovies as $row)
                    <tr><td class="fw-semibold">{{ $row->title }}</td><td>{{ number_format($row->tickets_sold) }}</td><td>{{ $fmt($row->ticket_gross) }}</td></tr>
                @empty
                    <tr><td colspan="3" class="empty-state">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header"><div class="fw-bold">Top suất chiếu đông khách</div><div class="small text-secondary">Theo vé ISSUED.</div></div>
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead><tr><th>Suất chiếu</th><th>Phòng</th><th>Vé</th><th>Lấp đầy</th><th>Doanh thu vé</th></tr></thead>
                <tbody>
                @forelse($topShows as $show)
                    <tr>
                        <td><div class="fw-semibold">{{ $show->movieVersion?->movie?->title ?? '-' }}</div><div class="small text-secondary">{{ optional($show->start_time)->format('d/m/Y H:i') }}</div></td>
                        <td>{{ $show->auditorium?->name ?? '-' }}</td>
                        <td>{{ number_format($show->tickets_sold) }}</td>
                        <td>{{ $show->occupancy_rate }}%</td>
                        <td>{{ $fmt($show->gross_revenue) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="empty-state">Chưa có dữ liệu.</td></tr>
                @endforelse
                </tbody>
            </table></div>
        </div>
    </div>
</div>

<script>
(function(){
    const period = document.getElementById('reportPeriod');
    const customFields = document.querySelectorAll('.custom-date-field');
    const dateFields = document.querySelectorAll('.report-date-field');
    function sync(){
        const isCustom = period.value === 'custom';
        customFields.forEach(el => el.style.display = isCustom ? '' : 'none');
        dateFields.forEach(el => el.style.display = isCustom ? 'none' : '');
    }
    period.addEventListener('change', sync);
    sync();
})();
</script>
@endsection
