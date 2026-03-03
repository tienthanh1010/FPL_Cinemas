@extends('admin.layout')

@section('title', 'Dashboard')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Dashboard</h2>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Phim</div>
                    <div class="fs-3 fw-semibold">{{ $stats['movies'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Chuỗi rạp</div>
                    <div class="fs-3 fw-semibold">{{ $stats['chains'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Rạp</div>
                    <div class="fs-3 fw-semibold">{{ $stats['cinemas'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Phòng chiếu</div>
                    <div class="fs-3 fw-semibold">{{ $stats['auditoriums'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Suất chiếu</div>
                    <div class="fs-3 fw-semibold">{{ $stats['shows'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <div class="fw-semibold">Suất chiếu mới nhất</div>
            <a href="{{ route('admin.shows.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Phim</th>
                    <th>Rạp / Phòng</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Trạng thái</th>
                </tr>
                </thead>
                <tbody>
                @forelse($latestShows as $show)
                    <tr>
                        <td>{{ $show->id }}</td>
                        <td>{{ $show->movieVersion?->movie?->title ?? '-' }}</td>
                        <td>
                            {{ $show->auditorium?->cinema?->name ?? '-' }}
                            <div class="text-muted small">{{ $show->auditorium?->name ?? '-' }}</div>
                        </td>
                        <td>{{ optional($show->start_time)->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($show->end_time)->format('d/m/Y H:i') }}</td>
                        <td><span class="badge text-bg-secondary">{{ $show->status }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu suất chiếu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
