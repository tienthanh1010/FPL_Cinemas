@extends('admin.layout')

@section('title', 'Cập nhật phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Movie</p>
            <h2>Chỉnh sửa phim #{{ $movie->id }}</h2>
            <p>Cập nhật media, thể loại, ê-kíp và các phiên bản chiếu của phim.</p>
        </div>
        <div>
            <a href="{{ route('admin.movies.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movies.update', $movie) }}">
                @csrf
                @method('PUT')
                @include('admin.movies._form')
            </form>
        </div>
    </div>
@endsection
