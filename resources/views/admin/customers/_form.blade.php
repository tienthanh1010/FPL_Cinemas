<div class="card"><div class="card-body row g-3">
<div class="col-md-6"><label class="form-label">Họ tên</label><input class="form-control" name="full_name" value="{{ old('full_name', $customer->full_name) }}" required></div>
<div class="col-md-3"><label class="form-label">Số điện thoại</label><input class="form-control" name="phone" value="{{ old('phone', $customer->phone) }}"></div>
<div class="col-md-3"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="{{ old('email', $customer->email) }}"></div>
<div class="col-md-3"><label class="form-label">Ngày sinh</label><input class="form-control" type="date" name="dob" value="{{ old('dob', optional($customer->dob)->format('Y-m-d')) }}"></div>
<div class="col-md-3"><label class="form-label">Giới tính</label><select class="form-select" name="gender"><option value="">Chọn</option>@foreach(['MALE'=>'Nam','FEMALE'=>'Nữ','OTHER'=>'Khác'] as $key=>$label)<option value="{{ $key }}" @selected(old('gender',$customer->gender)===$key)>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-3"><label class="form-label">Thành phố</label><input class="form-control" name="city" value="{{ old('city', $customer->city) }}"></div>
<div class="col-md-3"><label class="form-label">Trạng thái tài khoản</label><select class="form-select" name="account_status">@foreach(['ACTIVE'=>'Đang hoạt động','LOCKED'=>'Khoá','INACTIVE'=>'Ngưng dùng'] as $key=>$label)<option value="{{ $key }}" @selected(old('account_status',$customer->account_status ?? 'ACTIVE')===$key)>{{ $label }}</option>@endforeach</select></div>
</div></div>
<div class="d-flex gap-2 mt-3"><button class="btn btn-primary">Lưu khách hàng</button><a href="{{ route('admin.customers.index') }}" class="btn btn-light-soft">Quay lại</a></div>
