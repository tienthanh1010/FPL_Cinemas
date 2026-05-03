@extends('admin.layout')

@section('title', 'Phiên bản phim')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Quản lý Phiên bản phim</h2>
        <a href="{{ route('admin.movie_versions.create') }}" class="btn btn-primary">+ Thêm phiên bản</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.movie_versions.index') }}">
        <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên phim, format, ngôn ngữ...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Tìm</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-dark w-100" href="{{ route('admin.movie_versions.index') }}">Xoá lọc</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Phim</th>
                    <th>Format</th>
                    <th>Audio</th>
                    <th>Sub</th>
                    <th>Ghi chú</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($versions as $v)
                    <tr>
                        <td>{{ $v->id }}</td>
                        <td class="fw-semibold">{{ $v->movie?->title ?? '-' }}</td>
                        <td><span class="badge text-bg-info">{{ $v->format }}</span></td>
                        <td>{{ $v->audio_language }}</td>
                        <td>{{ $v->subtitle_language }}</td>
                        <td class="text-muted">{{ $v->notes }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.movie_versions.edit', $v) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.movie_versions.destroy', $v) }}" class="d-inline" onsubmit="return confirm('Xoá phiên bản phim này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu phiên bản phim.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $versions->links() }}
        </div>
    </div>
@endsection
