@extends('admin.layout')

@section('title', 'Sửa rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Cinema</p>
            <h2>Chỉnh sửa rạp #{{ $cinema->id }}</h2>
            <p>Cập nhật thông tin địa điểm, timezone và giờ mở cửa chuẩn hoá.</p>
        </div>
        <div>
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
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
