@extends('admin.layout')

@section('title', 'Thêm suất chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Show</p>
            <h2>Tạo suất chiếu</h2>
            <p>Chọn đúng phòng và phiên bản phim để lên lịch chiếu hợp lệ.</p>
        </div>
        <div>
            <a href="{{ route('admin.shows.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shows.store') }}">
                @csrf
                @include('admin.shows._form')
            </form>
        </div>
    </div>
@endsection
