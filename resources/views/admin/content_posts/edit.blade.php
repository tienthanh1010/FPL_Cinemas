@extends('admin.layout')

@section('title', 'Chỉnh sửa nội dung')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Content</p>
            <h2>{{ $contentPost->title }}</h2>
            <p>Chỉnh sửa bài viết, ưu đãi và lịch hiển thị ngoài giao diện khách hàng.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.content_posts.show', $contentPost) }}" class="btn btn-light-soft"><i class="bi bi-eye me-1"></i> Xem nhanh</a>
            <a href="{{ route('admin.content_posts.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.content_posts.update', $contentPost) }}">
                @csrf
                @method('PUT')
                @include('admin.content_posts._form')
            </form>
        </div>
    </div>
@endsection
