@extends('admin.layout')

@section('title', 'Thêm nội dung marketing')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Content</p>
            <h2>Thêm tin tức / ưu đãi</h2>
            <p>Tạo nội dung để hiển thị đồng thời cho cả giao diện người dùng và màn quản trị.</p>
        </div>
        <div>
            <a href="{{ route('admin.content_posts.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.content_posts.store') }}">
                @csrf
                @include('admin.content_posts._form')
            </form>
        </div>
    </div>
@endsection
