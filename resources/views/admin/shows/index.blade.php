@extends('admin.layout')

@section('title', 'Suất chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Shows</p>
            <h2>Quản lý suất chiếu</h2>
            <p>Giám sát lịch chiếu theo rạp, phòng, phiên bản phim và trạng thái mở bán.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-light-soft"><i class="bi bi-collection-play me-1"></i> Phiên bản phim</a>
            <a href="{{ route('admin.shows.create') }}" class="btn btn-primary"><i class="bi bi-calendar-plus me-1"></i> Thêm suất chiếu</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.shows.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên phim, rạp, phòng chiếu...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.shows.index') }}">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Phim</th>
                    <th>Rạp / Phòng</th>
                    <th>Thời gian</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($shows as $show)
                    @php
                        $statusClass = match($show->status) {
                            'ON_SALE' => 'badge-soft-success',
                            'SCHEDULED' => 'badge-soft-primary',
                            'SOLD_OUT' => 'badge-soft-warning',
                            'CANCELLED' => 'badge-soft-danger',
                            default => 'badge-soft-secondary',
                        };
                    @endphp
                    <tr>
                        <td class="fw-semibold">#{{ $show->id }}</td>
                        <td>
                            <div class="list-primary">{{ $show->movieVersion?->movie?->title ?? '-' }}</div>
                            <div class="list-secondary">{{ $show->movieVersion?->format ?? '-' }} · {{ $show->movieVersion?->audio_language ?? '-' }}{{ $show->movieVersion?->subtitle_language ? (' / ' . $show->movieVersion->subtitle_language) : '' }}</div>
                        </td>
                        <td>
                            <div class="list-primary">{{ $show->auditorium?->cinema?->name ?? '-' }}</div>
                            <div class="list-secondary">{{ $show->auditorium?->name ?? '-' }} · {{ $show->auditorium?->auditorium_code ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="list-primary">{{ optional($show->start_time)->format('d/m/Y H:i') }}</div>
                            <div class="list-secondary">Đến {{ optional($show->end_time)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td><span class="badge {{ $statusClass }}">{{ $statusOptions[$show->status] ?? $show->status }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.shows.show', $show) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.shows.edit', $show) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.shows.destroy', $show) }}" class="d-inline" onsubmit="return confirm('Xoá suất chiếu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Chưa có dữ liệu suất chiếu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $shows->links() }}</div>
    </div>
@endsection
