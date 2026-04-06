@extends('admin.layout')

@section('title', 'Thêm phiên bản phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Version</p>
            <h2>Thêm phiên bản phim</h2>
            <p>Tạo biến thể chiếu riêng cho một bộ phim cụ thể.</p>
        </div>
        <div>
            <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movie_versions.store') }}">
                @csrf
                @include('admin.movie_versions._form')
            </form>
        </div>
    </div>
@endsection
