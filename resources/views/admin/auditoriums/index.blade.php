@extends('admin.layout')

@section('title', 'Phòng chiếu')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Auditoriums</p>
            <h2>Quản lý phòng chiếu</h2>
            <p>Theo dõi các phòng chiếu theo từng rạp, loại màn hình và trạng thái hoạt động.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft"><i class="bi bi-buildings me-1"></i> Danh sách rạp</a>
            <a href="{{ route('admin.auditoriums.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm phòng</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.auditoriums.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên phòng, mã phòng...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.auditoriums.index') }}">Xoá lọc</a>
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
                    <th>Phòng</th>
                    <th>Rạp</th>
                    <th>Loại màn</th>
                    <th>Sơ đồ ghế</th>
                    <th>Hoạt động</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($auditoriums as $auditorium)
                    <tr>
                        <td class="fw-semibold">#{{ $auditorium->id }}</td>
                        <td>
                            <div class="list-primary">{{ $auditorium->name }}</div>
                            <div class="list-secondary">Mã: {{ $auditorium->auditorium_code }}</div>
                        </td>
                        <td>{{ $auditorium->cinema?->name ?? '-' }}</td>
                        <td><span class="badge badge-soft-primary">{{ $auditorium->screen_type }}</span></td>
                        <td>Version {{ $auditorium->seat_map_version }}</td>
                        <td><span class="badge {{ $auditorium->is_active ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $auditorium->is_active ? 'Có' : 'Không' }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.auditoriums.show', $auditorium) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.auditoriums.edit', $auditorium) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.auditoriums.destroy', $auditorium) }}" class="d-inline" onsubmit="return confirm('Xoá phòng chiếu này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có dữ liệu phòng chiếu.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $auditoriums->links() }}</div>
    </div>
@endsection
