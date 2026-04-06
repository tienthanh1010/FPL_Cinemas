@extends('admin.layout')

@section('title', 'Sửa phiên bản phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Version</p>
            <h2>Chỉnh sửa phiên bản #{{ $movieVersion->id }}</h2>
            <p>Cập nhật định dạng, audio, subtitle hoặc ghi chú chiếu của phiên bản này.</p>
        </div>
        <div>
            <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.movie_versions.update', $movieVersion) }}">
                @csrf
                @method('PUT')
                @include('admin.movie_versions._form')
            </form>
        </div>
    </div>
@endsection
