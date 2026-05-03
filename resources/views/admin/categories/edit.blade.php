@extends('admin.layout')

@section('title', 'Sửa thể loại phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Edit Category</p>
            <h2>Chỉnh sửa thể loại #{{ $category->id }}</h2>
            <p>Cập nhật mã và tên thể loại đang được gắn với phim.</p>
        </div>
        <div>
            <a class="btn btn-light-soft" href="{{ route('admin.categories.index') }}"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.categories.update', $category) }}" class="card">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="section-card mb-0">
                <h3>Thông tin thể loại</h3>
                <p class="section-description">Giữ mã thể loại ổn định để tránh gây nhầm dữ liệu khi tìm kiếm và lọc phim.</p>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Mã thể loại *</label>
                        <input class="form-control" name="code" value="{{ old('code', $category->code) }}" required>
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label">Tên thể loại *</label>
                        <input class="form-control" name="name" value="{{ old('name', $category->name) }}" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body border-top pt-0">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> Cập nhật thể loại</button>
        </div>
    </form>
@endsection
