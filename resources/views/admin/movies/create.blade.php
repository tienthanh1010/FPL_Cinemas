@extends('admin.layout')

@section('title', 'Thêm phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Movie</p>
            <h2>Tạo phim mới</h2>
            <p>Điền thông tin cốt lõi và gắn các dữ liệu liên kết ngay trong cùng một form.</p>
        </div>
        <div>
            <a href="{{ route('admin.movies.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movies.store') }}">
                @csrf
                @include('admin.movies._form')
            </form>
        </div>
    </div>
@endsection
