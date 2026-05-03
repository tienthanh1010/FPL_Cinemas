@extends('admin.layout')

@section('title', 'Phiên bản phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Movie Versions</p>
            <h2>Quản lý phiên bản phim</h2>
            <p>Theo dõi các biến thể chiếu theo định dạng, audio và subtitle của từng phim.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.movies.index') }}" class="btn btn-light-soft"><i class="bi bi-film me-1"></i> Danh sách phim</a>
            <a href="{{ route('admin.movie_versions.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm phiên bản</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.movie_versions.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên phim, định dạng, audio, subtitle, ghi chú...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.movie_versions.index') }}">Xoá lọc</a>
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
                    <th>Định dạng</th>
                    <th>Audio</th>
                    <th>Subtitle</th>
                    <th>Ghi chú</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($versions as $version)
                    <tr>
                        <td class="fw-semibold">#{{ $version->id }}</td>
                        <td>
                            <div class="list-primary">{{ $version->movie?->title ?? '-' }}</div>
                            <div class="list-secondary">Movie ID: {{ $version->movie_id }}</div>
                        </td>
                        <td><span class="badge badge-soft-primary">{{ $version->format }}</span></td>
                        <td>{{ $version->audio_language }}</td>
                        <td>{{ $version->subtitle_language ?: 'Không có' }}</td>
                        <td class="text-secondary">{{ $version->notes ?: '—' }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.movie_versions.show', $version) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.movie_versions.edit', $version) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.movie_versions.destroy', $version) }}" class="d-inline" onsubmit="return confirm('Xoá phiên bản phim này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có dữ liệu phiên bản phim.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $versions->links() }}</div>
    </div>
@endsection
