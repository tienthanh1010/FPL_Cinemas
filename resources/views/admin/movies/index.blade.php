@extends('admin.layout')

@section('title', 'Quản lý phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Movie Management</p>
            <h2>Danh sách phim</h2>
            <p>Quản lý tiêu đề, poster, trailer, thể loại, ê-kíp và số lượng phiên bản chiếu.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.categories.index') }}" class="btn btn-light-soft"><i class="bi bi-tags me-1"></i> Thể loại</a>
            <a href="{{ route('admin.movies.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm phim</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.movies.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên phim, tên gốc, thể loại, người tham gia...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.movies.index') }}">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Phim</th>
                    <th>Thông tin</th>
                    <th>Liên kết</th>
                    <th>Phiên bản</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($movies as $movie)
                    @php
                        $statusClass = $movie->status === 'ACTIVE' ? 'badge-soft-success' : 'badge-soft-secondary';
                        $directors = $movie->directorCredits->pluck('full_name')->implode(', ');
                    @endphp
                    <tr>
                        <td>
                            <div class="d-flex gap-3 align-items-start">
                                @if($movie->poster_url)
                                    <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}" class="poster-thumb">
                                @else
                                    <div class="poster-thumb d-inline-flex align-items-center justify-content-center text-primary fw-bold">🎬</div>
                                @endif
                                <div>
                                    <div class="list-primary">{{ $movie->title }}</div>
                                    <div class="list-secondary">{{ $movie->original_title ?: 'Chưa có tên gốc' }}</div>
                                    @if($movie->genres->isNotEmpty())
                                        <div class="mt-2 d-flex gap-1 flex-wrap">
                                            @foreach($movie->genres->take(3) as $genre)
                                                <span class="badge badge-soft-primary">{{ $genre->name }}</span>
                                            @endforeach
                                            @if($movie->genres->count() > 3)
                                                <span class="badge badge-soft-secondary">+{{ $movie->genres->count() - 3 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="list-primary">{{ $movie->duration_minutes }} phút · {{ $movie->language_original }}</div>
                            <div class="list-secondary">{{ optional($movie->release_date)->format('d/m/Y') ?: 'Chưa có ngày phát hành' }}</div>
                            <div class="list-secondary">Phân loại: {{ $movie->contentRating?->code ?? 'Chưa có' }}</div>
                            @if($directors)
                                <div class="list-secondary">Đạo diễn: {{ $directors }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                @if($movie->poster_url)
                                    <a href="{{ $movie->poster_url }}" target="_blank" class="btn btn-sm btn-light-soft">Poster</a>
                                @endif
                                @if($movie->trailer_url)
                                    <a href="{{ $movie->trailer_url }}" target="_blank" class="btn btn-sm btn-light-soft">Trailer</a>
                                @endif
                                @if(!$movie->poster_url && !$movie->trailer_url)
                                    <span class="text-secondary small">Chưa có media</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="list-primary">{{ $movie->versions_count }} phiên bản</div>
                            <div class="list-secondary">
                                {{ $movie->versions->take(2)->map(fn($version) => $version->format . ' · ' . $version->audio_language . ($version->subtitle_language ? '/' . $version->subtitle_language : ''))->implode(' • ') ?: 'Sẽ tự tạo mặc định nếu để trống' }}
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ $statusClass }}">{{ $statusOptions[$movie->status] ?? $movie->status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.movies.show', $movie) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.movies.destroy', $movie) }}" class="d-inline" onsubmit="return confirm('Xoá phim này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty-state">Chưa có dữ liệu phim. Hãy tạo phim đầu tiên của bạn.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $movies->links() }}</div>
    </div>
@endsection
