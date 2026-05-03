@extends('admin.layout')
@section('title', 'Tài khoản admin')
@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Admin permissions</p>
        <h2>Tài khoản admin</h2>
        <p>Quản lý tài khoản đăng nhập admin và phân vai trò truy cập theo nghiệp vụ.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.admin_users.create') }}" class="btn btn-primary">Thêm tài khoản admin</a>
    </div>
</section>

<div class="row g-3 mb-3">
    @foreach([
        ['Tổng tài khoản', $report['total']],
        ['Quyền Admin', $report['admins']],
        ['Manager', $report['managers']],
        ['Soát vé', $report['checkin']],
    ] as [$label, $value])
        <div class="col-md-6 col-xl-3">
            <div class="card"><div class="card-body"><div class="list-secondary">{{ $label }}</div><div class="h3 mb-0 mt-2">{{ $value }}</div></div></div>
        </div>
    @endforeach
</div>

<div class="card toolbar-card mb-3">
    <div class="card-body">
        <form class="row g-3 align-items-end" method="GET">
            <div class="col-lg-8">
                <label class="form-label">Tìm kiếm</label>
                <input class="form-control" name="q" value="{{ $q }}" placeholder="Tên hoặc email admin...">
            </div>
            <div class="col-lg-2"><button class="btn btn-primary w-100">Tìm</button></div>
            <div class="col-lg-2"><a href="{{ route('admin.admin_users.index') }}" class="btn btn-light-soft w-100">Xoá lọc</a></div>
        </form>
    </div>
</div>

@if($errors->has('delete'))
    <div class="alert alert-danger">{{ $errors->first('delete') }}</div>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Tài khoản</th>
                    <th>Vai trò</th>
                    <th>Quyền chính</th>
                    <th class="text-end">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adminUsers as $item)
                    <tr>
                        <td>
                            <div class="list-primary">{{ $item->name }}</div>
                            <div class="list-secondary">{{ $item->email }}</div>
                        </td>
                        <td>{{ $item->roles->pluck('name')->implode(', ') ?: 'Chưa gán vai trò' }}</td>
                        <td>
                            @php($codes = $item->roles->pluck('code')->all())
                            <div class="d-flex flex-wrap gap-2">
                                @if(in_array('ADMIN', $codes, true))<span class="badge badge-soft-primary">Toàn quyền</span>@endif
                                @if(in_array('MANAGER', $codes, true))<span class="badge badge-soft-success">Điều hành</span>@endif
                                @if(in_array('TICKET_COUNTER', $codes, true))<span class="badge badge-soft-warning">Quầy vé</span>@endif
                                @if(in_array('TICKET_CHECKIN', $codes, true))<span class="badge badge-soft-info">Soát vé</span>@endif
                                @if(in_array('FNB', $codes, true))<span class="badge badge-soft-secondary">F&B</span>@endif
                                @if(in_array('TECHNICIAN', $codes, true))<span class="badge badge-soft-danger">Kỹ thuật</span>@endif
                            </div>
                        </td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <a href="{{ route('admin.admin_users.show', $item) }}" class="btn btn-sm btn-outline-secondary">Xem</a>
                                <a href="{{ route('admin.admin_users.edit', $item) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                <form method="POST" action="{{ route('admin.admin_users.destroy', $item) }}" onsubmit="return confirm('Xoá tài khoản admin này?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Xoá</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty-state">Chưa có tài khoản admin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-body border-top">{{ $adminUsers->links() }}</div>
</div>
@endsection
