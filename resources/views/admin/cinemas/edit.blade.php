@extends('admin.layout')

@section('title', 'Cập nhật rạp')

@section('content')
    <section class="page-header">
        <div>
<<<<<<< HEAD
            <p class="eyebrow">Edit single cinema</p>
            <h2>Cập nhật {{ $cinema->name ?: 'FPL Cinema' }}</h2>
            <p>Chỉnh sửa thông tin rạp duy nhất đang được website và toàn bộ hệ thống quản trị sử dụng.</p>
        </div>
        <div>
            <a href="{{ route('admin.cinemas.show', $cinema) }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại thông tin rạp</a>
=======
            <p class="eyebrow">Edit Cinema</p>
            <h2>Chỉnh sửa rạp #{{ $cinema->id }}</h2>
            <p>Cập nhật thông tin địa điểm, timezone và giờ mở cửa chuẩn hoá.</p>
        </div>
        <div>
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cinemas.update', $cinema) }}">
                @csrf
                @method('PUT')
                @include('admin.cinemas._form')
            </form>
        </div>
    </div>
@endsection
