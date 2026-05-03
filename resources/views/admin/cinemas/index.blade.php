@extends('admin.layout')

@section('title', 'Rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Cinemas</p>
            <h2>Quản lý rạp</h2>
            <p>Quản lý thông tin rạp duy nhất, địa chỉ, múi giờ và số lượng phòng chiếu.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.cinemas.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm rạp</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.cinemas.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên rạp, mã rạp, quận, tỉnh...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.cinemas.index') }}">Xoá lọc</a>
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
                    <th>Rạp</th>
                                        <th>Địa chỉ</th>
                    <th>Timezone</th>
                    <th>Phòng chiếu</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cinemas as $cinema)
                    <tr>
                        <td class="fw-semibold">#{{ $cinema->id }}</td>
                        <td>
                            <div class="list-primary">{{ $cinema->name }}</div>
                            <div class="list-secondary">Mã: {{ $cinema->cinema_code }}</div>
                        </td>
                                                <td>
                            <div class="list-primary">{{ $cinema->address_line ?: 'Chưa cập nhật địa chỉ' }}</div>
                            <div class="list-secondary">{{ collect([$cinema->ward, $cinema->district, $cinema->province])->filter()->implode(', ') ?: '—' }}</div>
                        </td>
                        <td>{{ $cinema->timezone }}</td>
                        <td>{{ $cinema->auditoriums->count() }}</td>
                        <td>
                            <span class="badge {{ $cinema->status === 'ACTIVE' ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $cinema->status }}</span>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.cinemas.show', $cinema) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.cinemas.edit', $cinema) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.cinemas.destroy', $cinema) }}" class="d-inline" onsubmit="return confirm('Xoá rạp này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có dữ liệu rạp.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $cinemas->links() }}</div>
    </div>
@endsection
