@extends('admin.layout')

@section('title', 'Thêm rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Cinema</p>
            <h2>Tạo rạp mới</h2>
            <p>Khai báo địa điểm rạp, múi giờ và lịch giờ mở cửa theo từng ngày.</p>
        </div>
        <div>
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.cinemas.store') }}">
                @csrf
                @include('admin.cinemas._form')
            </form>
        </div>
    </div>
@endsection
