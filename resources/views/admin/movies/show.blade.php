@extends('admin.layout')

@section('title', 'Chi tiết phim')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Movie detail</p>
        <h2>{{ $movie->title }}</h2>
        <p>{{ $movie->duration_minutes }} phút · {{ optional($movie->release_date)->format('d/m/Y') ?: 'Chưa có ngày phát hành' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.movies.edit', $movie) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.movies.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>
<div class="row g-3 mb-4">
    <div class="col-lg-8"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-2">Thông tin phim</div>
        <div><strong>Tên gốc:</strong> {{ $movie->original_title ?: '—' }}</div>
        <div><strong>Độ tuổi:</strong> {{ $movie->contentRating?->name ?: '—' }}</div>
        <div><strong>Thể loại:</strong> {{ $movie->genres->pluck('name')->implode(', ') ?: '—' }}</div>
        <div><strong>Đạo diễn:</strong> {{ $movie->directorCredits->pluck('full_name')->implode(', ') ?: '—' }}</div>
        <div><strong>Hot:</strong> {{ $movie->is_hot ? 'Có' : 'Không' }}</div>
        <div><strong>Hiển thị slide:</strong> {{ $movie->is_on_slider ? 'Có' : 'Không' }}</div>
        <div><strong>Diễn viên:</strong> {{ $movie->castCredits->pluck('full_name')->implode(', ') ?: '—' }}</div>
        <div class="mt-3">{{ $movie->synopsis ?: 'Chưa có mô tả.' }}</div>
    </div></div></div>
    <div class="col-lg-4"><div class="card h-100"><div class="card-body">
        <div class="fw-semibold mb-2">Phiên bản</div>
        @foreach($movie->versions as $version)
            <div class="mb-2 d-flex justify-content-between align-items-center border-bottom pb-2">
                <div>{{ $version->format }} · {{ $version->audio_language }}{{ $version->subtitle_language ? ' / ' . $version->subtitle_language : '' }}</div>
                <a href="{{ route('admin.movie_versions.show', $version) }}" class="btn btn-sm btn-outline-primary">Xem</a>
            </div>
        @endforeach
    </div></div></div>
</div>
@endsection
