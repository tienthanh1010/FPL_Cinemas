@extends('admin.layout')

@section('title', 'Chuỗi rạp')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h2 class="h4 mb-0">Quản lý Chuỗi rạp</h2>
        <a href="{{ route('admin.chains.create') }}" class="btn btn-primary">+ Thêm chuỗi</a>
    </div>

    <form class="row g-2 mb-3" method="GET" action="{{ route('admin.chains.index') }}">
        <div class="col-md-6">
            <input class="form-control" name="q" value="{{ $q }}" placeholder="Tìm theo tên hoặc mã chuỗi...">
        </div>
        <div class="col-md-2">
            <button class="btn btn-outline-secondary w-100">Tìm</button>
        </div>
        <div class="col-md-2">
            <a class="btn btn-outline-dark w-100" href="{{ route('admin.chains.index') }}">Xoá lọc</a>
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
                    <th>Hotline</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($chains as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td class="font-monospace">{{ $c->chain_code }}</td>
                        <td class="fw-semibold">{{ $c->name }}</td>
                        <td>{{ $c->hotline }}</td>
                        <td>
                            <span class="badge {{ $c->status === 'ACTIVE' ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $c->status }}</span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.chains.edit', $c) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                            <form method="POST" action="{{ route('admin.chains.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Xoá chuỗi rạp này?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Xoá</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Chưa có dữ liệu chuỗi rạp.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            {{ $chains->links() }}
        </div>
    </div>
@endsection
