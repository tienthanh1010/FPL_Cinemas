@extends('admin.layout')

@section('title', 'Thêm thể loại phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Create Category</p>
            <h2>Tạo thể loại phim</h2>
            <p>Tạo danh mục dùng chung để chọn trong form phim.</p>
        </div>
        <div>
            <a class="btn btn-light-soft" href="{{ route('admin.categories.index') }}"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
        </div>
    </section>

    <form method="POST" action="{{ route('admin.categories.store') }}" class="card">
        @csrf
        <div class="card-body">
            <div class="section-card mb-0">
                <h3>Thông tin thể loại</h3>
                <p class="section-description">Mã thể loại nên ngắn gọn và dễ tái sử dụng trong dữ liệu phim.</p>

                <div class="row g-3">
                    <div class="col-lg-4">
                        <label class="form-label">Mã thể loại *</label>
                        <input class="form-control" name="code" value="{{ old('code') }}" placeholder="ACTION" required>
                        <div class="form-text">Gợi ý: viết liền, không dấu, dễ nhớ.</div>
                    </div>
                    <div class="col-lg-8">
                        <label class="form-label">Tên thể loại *</label>
                        <input class="form-control" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Hành động" required>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body border-top pt-0">
            <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> Lưu thể loại</button>
        </div>
    </form>
@endsection
