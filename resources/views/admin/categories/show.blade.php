@extends('admin.layout')

@section('title', 'Chi tiết thể loại')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Category detail</p>
        <h2>{{ $category->name }}</h2>
        <p>Mã thể loại: {{ $category->code }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>
<div class="card mb-4"><div class="card-body"><strong>Số phim:</strong> {{ $category->movies->count() }}</div></div>
<div class="card">
    <div class="card-header fw-semibold">Phim thuộc thể loại này</div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Tên phim</th><th>Ngày phát hành</th><th></th></tr></thead>
            <tbody>
            @forelse($category->movies as $movie)
                <tr>
                    <td>{{ $movie->title }}</td>
                    <td>{{ optional($movie->release_date)->format('d/m/Y') ?: '—' }}</td>
                    <td class="text-end"><a href="{{ route('admin.movies.show', $movie) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty-state">Chưa có phim nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
