@extends('admin.layout')

@section('title', 'Suất chiếu')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Quản lý Suất chiếu</h2>
        <a href="{{ route('admin.shows.create') }}" class="btn btn-primary">+ Thêm suất</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.shows.index') }}">
        <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên phim hoặc tên rạp...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Tìm</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-dark w-100" href="{{ route('admin.shows.index') }}">Xoá lọc</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
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
                @forelse($shows as $s)
                    <tr>
                        <td>{{ $s->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $s->movieVersion?->movie?->title ?? '-' }}</div>
                            <div class="text-muted small">{{ $s->movieVersion?->format ?? '-' }} • {{ $s->movieVersion?->audio_language ?? '' }}{{ $s->movieVersion?->subtitle_language ? ('/' . $s->movieVersion->subtitle_language) : '' }}</div>
                        </td>
                        <td>
                            {{ $s->auditorium?->cinema?->name ?? '-' }}
                            <div class="text-muted small">{{ $s->auditorium?->name ?? '-' }}</div>
                        </td>
                        <td>
                            <div>{{ optional($s->start_time)->format('d/m/Y H:i') }}</div>
                            <div class="text-muted small">→ {{ optional($s->end_time)->format('d/m/Y H:i') }}</div>
                        </td>
                        <td><span class="badge text-bg-secondary">{{ $s->status }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.shows.edit', $s) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.shows.destroy', $s) }}" class="d-inline" onsubmit="return confirm('Xoá suất chiếu này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu suất chiếu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $shows->links() }}
        </div>
    </div>
@endsection
