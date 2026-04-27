@extends('admin.layout')
@section('title', 'Chi tiết tài khoản admin')
@section('content')
<section class="page-header">
    <div>
        <p class="eyebrow">Admin profile</p>
        <h2>{{ $adminUser->name }}</h2>
        <p>Email đăng nhập: {{ $adminUser->email }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.admin_users.edit', $adminUser) }}" class="btn btn-primary">Chỉnh sửa</a>
        <a href="{{ route('admin.admin_users.index') }}" class="btn btn-light-soft">Quay lại</a>
    </div>
</section>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <div class="list-secondary mb-1">Tên hiển thị</div>
            <div class="list-primary">{{ $adminUser->name }}</div>
            <hr>
            <div class="list-secondary mb-1">Email</div>
            <div>{{ $adminUser->email }}</div>
            <hr>
            <div class="list-secondary mb-1">Ngày tạo</div>
            <div>{{ optional($adminUser->created_at)->format('d/m/Y H:i') ?: '—' }}</div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="list-primary mb-3">Vai trò được gán</div>
            <div class="d-flex flex-wrap gap-2 mb-4">
                @forelse($adminUser->roles as $role)
                    <span class="badge badge-soft-primary">{{ $role->name }} · {{ $role->code }}</span>
                @empty
                    <span class="text-secondary">Chưa gán vai trò</span>
                @endforelse
            </div>
            <div class="alert alert-info mb-0">
                Tài khoản này sẽ được phép truy cập các nhóm chức năng tương ứng với vai trò đã gán. Nếu có nhiều vai trò, quyền sẽ được cộng dồn.
            </div>
        </div></div>
    </div>
</div>
@endsection
