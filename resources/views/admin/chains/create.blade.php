@extends('admin.layout')

@section('title', 'Thêm chuỗi rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Chain</p>
            <h2>Tạo chuỗi rạp</h2>
            <p>Khai báo thương hiệu / hệ thống rạp trước khi tạo từng rạp thành viên.</p>
        </div>
        <div>
            <a href="{{ route('admin.chains.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.chains.store') }}">
                @csrf
                @include('admin.chains._form')
            </form>
        </div>
    </div>
@endsection
