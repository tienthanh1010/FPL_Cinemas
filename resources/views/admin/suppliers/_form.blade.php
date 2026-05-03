<div class="card"><div class="card-body row g-3">
    <div class="col-md-8"><label class="form-label">Tên nhà cung cấp</label><input class="form-control" name="name" value="{{ old('name', $supplier->name) }}" required></div>
    <div class="col-md-4"><label class="form-label">Trạng thái</label><select class="form-select" name="status"><option value="ACTIVE" @selected(old('status', $supplier->status ?: 'ACTIVE') === 'ACTIVE')>ACTIVE</option><option value="INACTIVE" @selected(old('status', $supplier->status) === 'INACTIVE')>INACTIVE</option></select></div>
    <div class="col-md-4"><label class="form-label">Mã số thuế</label><input class="form-control" name="tax_code" value="{{ old('tax_code', $supplier->tax_code) }}"></div>
    <div class="col-md-4"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="{{ old('phone', $supplier->phone) }}"></div>
    <div class="col-md-4"><label class="form-label">Email</label><input class="form-control" name="email" value="{{ old('email', $supplier->email) }}"></div>
    <div class="col-12"><label class="form-label">Địa chỉ</label><input class="form-control" name="address_line" value="{{ old('address_line', $supplier->address_line) }}"></div>
    <div class="col-md-4"><label class="form-label">Phường/Xã</label><input class="form-control" name="ward" value="{{ old('ward', $supplier->ward) }}"></div>
    <div class="col-md-4"><label class="form-label">Quận/Huyện</label><input class="form-control" name="district" value="{{ old('district', $supplier->district) }}"></div>
    <div class="col-md-4"><label class="form-label">Tỉnh/Thành</label><input class="form-control" name="province" value="{{ old('province', $supplier->province) }}"></div>
</div></div>
<div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Lưu nhà cung cấp</button><a href="{{ route('admin.suppliers.index') }}" class="btn btn-light-soft">Quay lại</a></div>
