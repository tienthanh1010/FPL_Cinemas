@extends('admin.layout')

@section('title', 'Thêm phòng chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Auditorium</p>
            <h2>Tạo phòng chiếu</h2>
            <p>Khai báo phòng chiếu mới cho từng rạp đang hoạt động.</p>
        </div>
        <div>
            <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.auditoriums.store') }}">
                @csrf
                @include('admin.auditoriums._form')
            </form>
        </div>
    </div>
@endsection
