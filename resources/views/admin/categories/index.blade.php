@extends('admin.layout')

@section('title', 'Thể loại phim')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Categories</p>
            <h2>Quản lý thể loại phim</h2>
            <p>Quản trị danh mục thể loại để gắn vào phim một cách nhất quán và có logic.</p>
        </div>
        <div>
            <a class="btn btn-primary" href="{{ route('admin.categories.create') }}"><i class="bi bi-plus-circle me-1"></i> Thêm thể loại</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.categories.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên thể loại hoặc mã thể loại...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.categories.index') }}">Xoá lọc</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Mã</th>
                    <th>Tên thể loại</th>
                    <th>Số phim đang dùng</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr>
                        <td class="fw-semibold">#{{ $category->id }}</td>
                        <td><span class="badge badge-soft-primary">{{ $category->code }}</span></td>
                        <td class="list-primary">{{ $category->name }}</td>
                        <td>{{ $category->movies_count }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="{{ route('admin.categories.show', $category) }}">Xem</a>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.categories.edit', $category) }}">Sửa</a>
                                <form class="d-inline" method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Xoá thể loại này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="empty-state">Chưa có dữ liệu thể loại phim.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $categories->links() }}</div>
    </div>
@endsection
