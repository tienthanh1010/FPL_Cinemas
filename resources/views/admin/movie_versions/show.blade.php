@extends('admin.layout')

@section('title', 'Chi tiết phiên bản phim')

@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Movie version detail</p>
        <h2>{{ $movieVersion->movie?->title }}</h2>
        <p>{{ $movieVersion->format }} · {{ $movieVersion->audio_language }}{{ $movieVersion->subtitle_language ? ' / ' . $movieVersion->subtitle_language : '' }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('admin.movie_versions.edit', $movieVersion) }}" class="btn btn-primary">Sửa</a>
        <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>
<div class="card mb-4"><div class="card-body">
    <div><strong>Ghi chú:</strong> {{ $movieVersion->notes ?: '—' }}</div>
    <div><strong>Số suất chiếu:</strong> {{ $movieVersion->shows->count() }}</div>
</div></div>
<div class="card">
    <div class="card-header fw-semibold">Suất chiếu dùng phiên bản này</div>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Phòng</th><th>Bắt đầu</th><th>Trạng thái</th><th></th></tr></thead>
        <tbody>
        @forelse($movieVersion->shows as $show)
            <tr>
                <td>{{ $show->auditorium?->name }}</td>
                <td>{{ optional($show->start_time)->format('d/m/Y H:i') }}</td>
                <td>{{ $show->status }}</td>
                <td class="text-end"><a href="{{ route('admin.shows.show', $show) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
            </tr>
        @empty
            <tr><td colspan="4" class="empty-state">Chưa có suất chiếu nào.</td></tr>
        @endforelse
        </tbody>
    </table></div>
</div>
@endsection
