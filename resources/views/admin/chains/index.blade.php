@extends('admin.layout')

@section('title', 'Chuỗi rạp')

@section('content')
    <section class="page-header">
        <div>
            <p class="eyebrow">Cinema Chains</p>
            <h2>Quản lý chuỗi rạp</h2>
            <p>Khai báo các thương hiệu / hệ thống rạp để liên kết với từng địa điểm rạp cụ thể.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft"><i class="bi bi-buildings me-1"></i> Danh sách rạp</a>
            <a href="{{ route('admin.chains.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle me-1"></i> Thêm chuỗi</a>
        </div>
    </section>

    <div class="card toolbar-card">
        <div class="card-body">
            <form class="row g-3 align-items-end" method="GET" action="{{ route('admin.chains.index') }}">
                <div class="col-lg-8">
                    <label class="form-label">Tìm kiếm</label>
                    <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên chuỗi, mã chuỗi...">
                </div>
                <div class="col-sm-6 col-lg-2">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1"></i> Tìm</button>
                </div>
                <div class="col-sm-6 col-lg-2">
                    <a class="btn btn-light-soft w-100" href="{{ route('admin.chains.index') }}">Xoá lọc</a>
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
                    <th>Tên chuỗi</th>
                    <th>Liên hệ</th>
                    <th>Website</th>
                    <th>Trạng thái</th>
                    <th class="text-end">Thao tác</th>
                </tr>
                </thead>
                <tbody>
                @forelse($chains as $chain)
                    <tr>
                        <td class="fw-semibold">#{{ $chain->id }}</td>
                        <td><span class="badge badge-soft-primary">{{ $chain->chain_code }}</span></td>
                        <td>
                            <div class="list-primary">{{ $chain->name }}</div>
                            <div class="list-secondary">{{ $chain->legal_name ?: 'Chưa có tên pháp lý' }}</div>
                        </td>
                        <td>
                            <div class="list-primary">{{ $chain->hotline ?: 'Chưa cập nhật hotline' }}</div>
                            <div class="list-secondary">{{ $chain->email ?: '—' }}</div>
                        </td>
                        <td>
                            @if($chain->website)
                                <a href="{{ $chain->website }}" target="_blank" class="btn btn-sm btn-light-soft">Mở website</a>
                            @else
                                <span class="text-secondary small">Chưa có</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $chain->status === 'ACTIVE' ? 'badge-soft-success' : 'badge-soft-secondary' }}">{{ $chain->status }}</span></td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.chains.edit', $chain) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.chains.destroy', $chain) }}" class="d-inline" onsubmit="return confirm('Xoá chuỗi rạp này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="empty-state">Chưa có dữ liệu chuỗi rạp.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body border-top">{{ $chains->links() }}</div>
    </div>
@endsection
