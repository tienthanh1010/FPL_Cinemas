@extends('admin.layout')

@section('title', 'Sửa phòng chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Auditorium</p>
            <h2>Chỉnh sửa phòng #{{ $auditorium->id }}</h2>
            <p>Cập nhật loại màn hình, mã phòng hoặc trạng thái hoạt động của phòng chiếu.</p>
        </div>
        <div>
            <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.auditoriums.update', $auditorium) }}">
                @csrf
                @method('PUT')
                @include('admin.auditoriums._form')
            </form>
        </div>
    </div>
@endsection
