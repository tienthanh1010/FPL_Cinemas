@extends('admin.layout')

@section('title', 'Xem nội dung')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Preview Content</p>
            <h2>{{ $contentPost->title }}</h2>
            <p>Xem nhanh nội dung trước khi phát hành ngoài giao diện người dùng.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.content_posts.edit', $contentPost) }}" class="btn btn-primary"><i class="bi bi-pencil-square me-1"></i> Chỉnh sửa</a>
            <a href="{{ route('admin.content_posts.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại</a>
        </div>
    </section>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    @if($contentPost->cover_image_url)
                        <img src="{{ $contentPost->cover_image_url }}" alt="{{ $contentPost->title }}" class="img-fluid rounded-4 mb-4 w-100">
                    @endif
                    <div class="d-flex gap-2 flex-wrap mb-3">
                        <span class="badge badge-soft-primary">{{ $typeOptions[$contentPost->type] ?? $contentPost->type }}</span>
                        <span class="badge {{ $contentPost->status === 'PUBLISHED' ? 'badge-soft-success' : ($contentPost->status === 'ARCHIVED' ? 'badge-soft-secondary' : 'badge-soft-warning') }}">{{ $statusOptions[$contentPost->status] ?? $contentPost->status }}</span>
                        @if($contentPost->is_featured)
                            <span class="badge badge-soft-warning">Nổi bật</span>
                        @endif
                    </div>
                    @if($contentPost->excerpt)
                        <p class="lead">{{ $contentPost->excerpt }}</p>
                    @endif
                    <div style="white-space: pre-line; line-height: 1.8;">{{ $contentPost->content }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <h3 class="h5 mb-3">Thông tin hiển thị</h3>
                    <div class="list-secondary mb-2">Slug: <span class="font-monospace">/{{ $contentPost->slug }}</span></div>
                    <div class="list-secondary mb-2">Badge: {{ $contentPost->badge_label ?: '—' }}</div>
                    <div class="list-secondary mb-2">Published at: {{ optional($contentPost->published_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="list-secondary mb-2">Starts at: {{ optional($contentPost->starts_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="list-secondary mb-2">Ends at: {{ optional($contentPost->ends_at)->format('d/m/Y H:i') ?: '—' }}</div>
                    <div class="list-secondary mb-2">Sort order: {{ $contentPost->sort_order }}</div>
                    <div class="list-secondary mb-0">Cập nhật lần cuối: {{ optional($contentPost->updated_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection
