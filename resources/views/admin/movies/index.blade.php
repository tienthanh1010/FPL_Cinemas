@extends('admin.layout')

@section('title', 'Phim')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Quản lý Phim</h2>
        <a href="{{ route('admin.movies.create') }}" class="btn btn-primary">+ Thêm phim</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.movies.index') }}">
        <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên phim...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Tìm</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-dark w-100" href="{{ route('admin.movies.index') }}">Xoá lọc</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Tiêu đề</th>
                    <th>Thời lượng</th>
                    <th>Ngày phát hành</th>
                    <th>Phân loại</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($movies as $movie)
                    <tr>
                        <td>{{ $movie->id }}</td>
                        <td>
                            <div class="fw-semibold">{{ $movie->title }}</div>
                            <div class="text-muted small">{{ $movie->original_title }}</div>
                        </td>
                        <td>{{ $movie->duration_minutes }} phút</td>
                        <td>{{ optional($movie->release_date)->format('d/m/Y') }}</td>
                        <td>{{ $movie->contentRating?->code ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $movie->status === 'ACTIVE' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $movie->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" class="d-inline" onsubmit="return confirm('Xoá phim này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu phim.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $movies->links() }}
        </div>
    </div>
@endsection
