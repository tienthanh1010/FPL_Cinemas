@extends('admin.layout')

@section('title', 'Rạp')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Quản lý Rạp</h2>
        <a href="{{ route('admin.cinemas.create') }}" class="btn btn-primary">+ Thêm rạp</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.cinemas.index') }}">
        <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên, mã, tỉnh/quận...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Tìm</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-dark w-100" href="{{ route('admin.cinemas.index') }}">Xoá lọc</a>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Mã</th>
                    <th>Tên</th>
                    <th>Chuỗi</th>
                    <th>Địa chỉ</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($cinemas as $cinema)
                    <tr>
                        <td>{{ $cinema->id }}</td>
                        <td class="font-monospace">{{ $cinema->cinema_code }}</td>
                        <td class="fw-semibold">{{ $cinema->name }}</td>
                        <td>{{ $cinema->chain?->name ?? '-' }}</td>
                        <td>
                            {{ $cinema->address_line }}
                            <div class="text-muted small">{{ $cinema->district }} {{ $cinema->province }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $cinema->status === 'ACTIVE' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $cinema->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.cinemas.edit', $cinema) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.cinemas.destroy', $cinema) }}" class="d-inline" onsubmit="return confirm('Xoá rạp này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Chưa có dữ liệu rạp.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $cinemas->links() }}
        </div>
    </div>
@endsection
