@extends('admin.layout')

@section('title', 'Báo cáo vận hành')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Reporting Center</p>
            <h2>Báo cáo theo phim, phòng, suất và ngày</h2>
            <p>Phân tích doanh thu, vé bán, tỷ lệ lấp đầy và các top performer để chốt báo cáo cuối ngày hoặc cuối tháng.</p>
        </div>
    </section>

    <div class="card mb-4">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="get">
                <div class="col-md-4">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="form-control">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-funnel me-1"></i> Lọc báo cáo</button>
                    <a href="{{ route('admin.reports.index') }}" class="btn btn-light-soft">Mặc định</a>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3"><div class="card metric-card"><div class="card-body"><div class="metric-label">Suất trong kỳ</div><div class="metric-value">{{ number_format($summary['shows']) }}</div><div class="metric-caption">Tổng số suất trong khoảng lọc</div></div></div></div>
        <div class="col-12 col-sm-6 col-xl-3"><div class="card metric-card"><div class="card-body"><div class="metric-label">Vé đã bán</div><div class="metric-value">{{ number_format($summary['tickets']) }}</div><div class="metric-caption">Số vé phát sinh theo suất trong kỳ</div></div></div></div>
        <div class="col-12 col-sm-6 col-xl-3"><div class="card metric-card"><div class="card-body"><div class="metric-label">Doanh thu</div><div class="metric-value">{{ number_format($summary['revenue']) }}đ</div><div class="metric-caption">Đã lọc booking paid/confirmed/completed</div></div></div></div>
        <div class="col-12 col-sm-6 col-xl-3"><div class="card metric-card"><div class="card-body"><div class="metric-label">Lấp đầy bình quân</div><div class="metric-value">{{ $summary['occupancy'] }}%</div><div class="metric-caption">So sánh vé bán với sức chứa toàn kỳ</div></div></div></div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Doanh thu theo phim</div><div class="small text-secondary">Xếp hạng 10 phim có doanh thu cao nhất</div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Phim</th><th>Booking</th><th>Doanh thu</th></tr></thead>
                        <tbody>
                        @forelse($revenueByMovie as $row)
                            <tr><td class="fw-semibold">{{ $row->title }}</td><td>{{ number_format($row->booking_count) }}</td><td>{{ number_format($row->revenue) }}đ</td></tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">Chưa có dữ liệu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Doanh thu theo phòng</div><div class="small text-secondary">Phòng chiếu nào đang mang lại doanh thu tốt hơn</div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Phòng</th><th>Loại màn hình</th><th>Booking</th><th>Doanh thu</th></tr></thead>
                        <tbody>
                        @forelse($revenueByAuditorium as $row)
                            <tr><td class="fw-semibold">{{ $row->name }}</td><td>{{ $row->screen_type }}</td><td>{{ number_format($row->booking_count) }}</td><td>{{ number_format($row->revenue) }}đ</td></tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">Chưa có dữ liệu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-5">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Số vé theo khung giờ</div><div class="small text-secondary">Nhận biết khung giờ bán chạy</div></div>
                <div class="card-body">
                    <div class="d-grid gap-3">
                        @forelse($ticketsByHour as $row)
                            <div>
                                <div class="d-flex justify-content-between"><span>{{ str_pad($row->hour_slot, 2, '0', STR_PAD_LEFT) }}:00</span><strong>{{ number_format($row->tickets_sold) }} vé</strong></div>
                                <div class="progress mt-2" style="height: 8px;"><div class="progress-bar" style="width: {{ min(($ticketsByHour->max('tickets_sold') ? ($row->tickets_sold / $ticketsByHour->max('tickets_sold')) * 100 : 0), 100) }}%"></div></div>
                            </div>
                        @empty
                            <div class="empty-state">Chưa có dữ liệu.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Tỷ lệ lấp đầy theo ngày</div><div class="small text-secondary">Theo dõi xu hướng kín chỗ theo ngày</div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Ngày</th><th>Số suất</th><th>Vé bán</th><th>Tỷ lệ lấp đầy</th></tr></thead>
                        <tbody>
                        @forelse($occupancyByDay as $row)
                            <tr>
                                <td class="fw-semibold">{{ \Carbon\Carbon::parse($row->report_date)->format('d/m/Y') }}</td>
                                <td>{{ number_format($row->shows_count) }}</td>
                                <td>{{ number_format($row->tickets_sold) }}</td>
                                <td>{{ $row->occupancy_rate }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">Chưa có dữ liệu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Top phim bán chạy</div><div class="small text-secondary">Xếp hạng theo số vé và doanh thu</div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Phim</th><th>Vé bán</th><th>Doanh thu vé</th></tr></thead>
                        <tbody>
                        @forelse($topMovies as $row)
                            <tr><td class="fw-semibold">{{ $row->title }}</td><td>{{ number_format($row->tickets_sold) }}</td><td>{{ number_format($row->revenue) }}đ</td></tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">Chưa có dữ liệu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card h-100">
                <div class="card-header"><div class="fw-bold">Top suất chiếu đông khách</div><div class="small text-secondary">Ưu tiên đánh giá các suất có hiệu suất cao</div></div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Suất chiếu</th><th>Phòng</th><th>Vé bán</th><th>Lấp đầy</th><th>Doanh thu</th></tr></thead>
                        <tbody>
                        @forelse($topShows as $show)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $show->movieVersion?->movie?->title ?? '-' }}</div>
                                    <div class="small text-secondary">{{ optional($show->start_time)->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>{{ $show->auditorium?->name ?? '-' }}</td>
                                <td>{{ number_format($show->tickets_sold) }}</td>
                                <td>{{ $show->occupancy_rate }}%</td>
                                <td>{{ number_format($show->gross_revenue) }}đ</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">Chưa có dữ liệu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="soft-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><div class="fw-bold">Báo cáo cuối ngày</div><div class="small text-secondary">Snapshot để chốt vận hành trong hôm nay</div></div>
                    <i class="bi bi-calendar-day fs-4 text-primary"></i>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['today']['shows']) }}</h3><p class="section-description mb-0">Suất hôm nay</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['today']['tickets']) }}</h3><p class="section-description mb-0">Vé hôm nay</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['today']['revenue']) }}đ</h3><p class="section-description mb-0">Doanh thu hôm nay</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ $dailySummary['today']['occupancy'] }}%</h3><p class="section-description mb-0">Lấp đầy hôm nay</p></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="soft-card h-100"><div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div><div class="fw-bold">Báo cáo cuối tháng</div><div class="small text-secondary">Snapshot nhanh cho tháng hiện tại</div></div>
                    <i class="bi bi-calendar-month fs-4 text-success"></i>
                </div>
                <div class="row g-3 text-center">
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['month']['shows']) }}</h3><p class="section-description mb-0">Suất trong tháng</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['month']['tickets']) }}</h3><p class="section-description mb-0">Vé trong tháng</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ number_format($dailySummary['month']['revenue']) }}đ</h3><p class="section-description mb-0">Doanh thu tháng</p></div></div>
                    <div class="col-6"><div class="section-card mb-0"><h3>{{ $dailySummary['month']['occupancy'] }}%</h3><p class="section-description mb-0">Lấp đầy tháng</p></div></div>
                </div>
            </div></div>
        </div>
    </div>
@endsection
