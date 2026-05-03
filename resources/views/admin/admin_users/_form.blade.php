<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100"><div class="card-body">
            <div class="mb-3">
                <label class="form-label">Tên hiển thị</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $adminUserModel->name) }}">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Email đăng nhập</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $adminUserModel->email) }}">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mật khẩu {{ $adminUserModel->exists ? '(để trống nếu không đổi)' : '' }}</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nhập lại mật khẩu</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100"><div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <div class="list-primary">Vai trò truy cập</div>
                    <div class="list-secondary">Chọn ít nhất 1 vai trò cho tài khoản admin.</div>
                </div>
            </div>
            @php($selectedRoles = collect(old('role_ids', $adminUserModel->roles->pluck('id')->all()))->map(fn($id) => (int) $id)->all())
            <div class="d-grid gap-2">
                @foreach($roles as $role)
                    <label class="border rounded-4 p-3 d-flex gap-3 align-items-start">
                        <input type="checkbox" class="form-check-input mt-1" name="role_ids[]" value="{{ $role->id }}" {{ in_array($role->id, $selectedRoles, true) ? 'checked' : '' }}>
                        <span>
                            <strong class="d-block">{{ $role->name }}</strong>
                            <span class="text-secondary small">{{ $role->code }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('role_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @error('role_ids.*')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div></div>
    </div>
</div>
<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $adminUserModel->exists ? 'Lưu thay đổi' : 'Tạo tài khoản' }}</button>
    <a href="{{ route('admin.admin_users.index') }}" class="btn btn-light-soft">Quay lại</a>
</div>
