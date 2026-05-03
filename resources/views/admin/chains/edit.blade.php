@extends('admin.layout')

@section('title', 'Sửa chuỗi rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Chain</p>
            <h2>Chỉnh sửa chuỗi #{{ $chain->id }}</h2>
            <p>Cập nhật thông tin thương hiệu, liên hệ và website của chuỗi rạp.</p>
        </div>
        <div>
            <a href="{{ route('admin.chains.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.chains.update', $chain) }}">
                @csrf
                @method('PUT')
                @include('admin.chains._form')
            </form>
        </div>
    </div>
@endsection
