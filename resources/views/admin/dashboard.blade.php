@extends('admin.layout')

@section('title', 'Tổng quan vận hành')
@section('title', 'Tổng quan vận hành')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Daily Operations</p>
            <h2>Dashboard vận hành theo ngày</h2>
            <p>Theo dõi nhanh số suất hôm nay, vé bán, doanh thu, tỷ lệ lấp đầy và các cảnh báo cần xử lý ngay.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.index') }}" class="btn btn-light-soft"><i class="bi bi-bar-chart-line me-1"></i> Xem báo cáo chi tiết</a>
            <a href="{{ route('admin.shows.create') }}" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Thêm suất chiếu</a>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card"><div class="card-body">
                <div class="metric-label">Suất chiếu hôm nay</div>
                <div class="metric-value">{{ $stats['shows_today'] }}</div>
                <div class="metric-caption">Tổng số suất bắt đầu trong ngày</div>
            </div></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card"><div class="card-body">
                <div class="metric-label">Vé bán hôm nay</div>
                <div class="metric-value">{{ number_format($stats['tickets_today']) }}</div>
                <div class="metric-caption">Tổng ghế đã giữ hoặc đã xuất vé</div>
            </div></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card"><div class="card-body">
                <div class="metric-label">Doanh thu hôm nay</div>
                <div class="metric-value">{{ number_format($stats['revenue_today']) }}đ</div>
                <div class="metric-caption">Chỉ tính booking đã thanh toán</div>
            </div></div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card metric-card"><div class="card-body">
                <div class="metric-label">Tỷ lệ lấp đầy</div>
                <div class="metric-value">{{ $stats['occupancy_today'] }}%</div>
                <div class="metric-caption">Tính theo toàn bộ sức chứa của các suất trong ngày</div>
            </div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-7">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-bold">Suất sắp bắt đầu</div>
                        <div class="small text-secondary">Ưu tiên xử lý các suất chuẩn bị mở cửa soát vé</div>
                    </div>
                    <a href="{{ route('admin.shows.index') }}" class="btn btn-sm btn-light-soft">Xem tất cả</a>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle table-hover mb-0">
                        <thead>
                        <tr>
                            <th>Phim</th>
                            <th>Phòng</th>
                            <th>Bắt đầu</th>
                            <th>Lấp đầy</th>
                            <th>Trạng thái</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($upcomingShows as $show)
                            <tr>
                                <td>
                                    <div class="list-primary">{{ $show->movieVersion?->movie?->title ?? '-' }}</div>
                                    <div class="list-secondary">{{ $show->movieVersion?->format ?? '-' }}</div>
                                </td>
                                <td>
                                    <div class="list-primary">{{ $show->auditorium?->name ?? '-' }}</div>
                                    <div class="list-secondary">{{ $show->auditorium?->cinema?->name ?? 'Rạp chính' }}</div>
                                </td>
                                <td>
                                    <div class="list-primary">{{ optional($show->start_time)->format('d/m/Y H:i') }}</div>
                                    <div class="list-secondary">{{ optional($show->end_time)->format('H:i') }}</div>
                                </td>
                                <td>
                                    <div class="list-primary">{{ $show->sold }}/{{ $show->capacity }} ghế</div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: {{ min($show->occupancy_rate, 100) }}%"></div>
                                    </div>
                                    <div class="list-secondary mt-1">{{ $show->occupancy_rate }}%</div>
                                </td>
                                <td><span class="badge badge-soft-primary">{{ $show->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">Không có suất sắp bắt đầu.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-5">
            <div class="soft-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <div class="fw-bold">Suất gần đầy / hết vé</div>
                            <div class="small text-secondary">Các suất có tỷ lệ lấp đầy từ 70% trở lên</div>
                        </div>
                        <i class="bi bi-fire text-danger fs-4"></i>
                    </div>
                    <div class="d-grid gap-3">
                        @forelse($nearlyFullShows as $show)
                            <a href="{{ route('admin.shows.show', $show) }}" class="section-card d-block mb-0">
                                <div class="d-flex justify-content-between gap-2">
                                    <div>
                                        <h3 class="mb-1">{{ $show->movieVersion?->movie?->title ?? '-' }}</h3>
                                        <p class="section-description mb-0">{{ $show->auditorium?->name ?? '-' }} · {{ optional($show->start_time)->format('d/m H:i') }}</p>
                                    </div>
                                    <span class="badge badge-soft-warning align-self-start">{{ $show->occupancy_rate }}%</span>
                                </div>
                            </a>
                        @empty
                            <div class="empty-state mb-0">Chưa có suất nào gần đầy trong hôm nay.</div>
                        @endforelse
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="fw-bold">Cảnh báo trùng suất</div>
                    <div class="small text-secondary">2 phim cùng phòng trùng thời gian</div>
                </div>
                <div class="card-body">
                    @forelse($alerts['conflicts'] as $conflict)
                        <div class="alert alert-danger mb-3">
                            <div class="fw-semibold">{{ $conflict->first->auditorium?->name ?? '-' }}</div>
                            <div>{{ $conflict->first->movieVersion?->movie?->title ?? '-' }} · {{ optional($conflict->first->start_time)->format('H:i') }} - {{ optional($conflict->first->end_time)->format('H:i') }}</div>
                            <div>{{ $conflict->second->movieVersion?->movie?->title ?? '-' }} · {{ optional($conflict->second->start_time)->format('H:i') }} - {{ optional($conflict->second->end_time)->format('H:i') }}</div>
                        </div>
                    @empty
                        <div class="empty-state mb-0">Không phát hiện suất bị trùng trong hôm nay.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="fw-bold">Phòng chưa có giá</div>
                    <div class="small text-secondary">Suất chưa có dòng giá active trong show_prices</div>
                </div>
                <div class="card-body">
                    @forelse($alerts['missingPrices'] as $show)
                        <div class="alert alert-warning mb-3">
                            <div class="fw-semibold">{{ $show->movieVersion?->movie?->title ?? '-' }}</div>
                            <div>{{ $show->auditorium?->name ?? '-' }} · {{ optional($show->start_time)->format('d/m H:i') }}</div>
                        </div>
                    @empty
                        <div class="empty-state mb-0">Tất cả suất hôm nay đã có giá bán.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card h-100">
                <div class="card-header">
                    <div class="fw-bold">Suất chưa mở bán</div>
                    <div class="small text-secondary">Chưa ON_SALE hoặc chưa tới thời điểm on_sale_from</div>
                </div>
                <div class="card-body">
                    @forelse($alerts['notOnSale'] as $show)
                        <div class="alert alert-info mb-3">
                            <div class="fw-semibold">{{ $show->movieVersion?->movie?->title ?? '-' }}</div>
                            <div>{{ $show->auditorium?->name ?? '-' }} · {{ optional($show->start_time)->format('d/m H:i') }}</div>
                            <div class="small">{{ $show->status }}</div>
                        </div>
                    @empty
                        <div class="empty-state mb-0">Tất cả suất hôm nay đã mở bán đúng kế hoạch.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
