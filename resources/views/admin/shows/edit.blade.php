@extends('admin.layout')

@section('title', 'Sửa suất chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Show</p>
            <h2>Chỉnh sửa suất chiếu #{{ $show->id }}</h2>
            <p>Cập nhật lịch chiếu nhưng vẫn giữ an toàn logic thời gian cho từng phòng.</p>
        </div>
        <div>
            <a href="{{ route('admin.shows.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.shows.update', $show) }}">
                @csrf
                @method('PUT')
                @include('admin.shows._form')
            </form>
        </div>
    </div>
@endsection
