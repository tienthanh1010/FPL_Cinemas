@php
    $isEdit = $cinema->exists;
    $openingHoursJson = old('opening_hours');
    if ($openingHoursJson === null) {
        $openingHoursJson = $cinema->opening_hours ? json_encode($cinema->opening_hours, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '';
    }
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Chuỗi rạp *</label>
        <select name="chain_id" class="form-select" required>
            <option value="">-- Chọn chuỗi --</option>
            @foreach($chains as $c)
                <option value="{{ $c->id }}" @selected((string)old('chain_id', $cinema->chain_id) === (string)$c->id)>
                    {{ $c->name }} ({{ $c->chain_code }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Nếu chưa có chuỗi rạp, bạn hãy thêm dữ liệu vào bảng <span class="font-monospace">cinema_chains</span>.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Mã rạp *</label>
        <input name="cinema_code" value="{{ old('cinema_code', $cinema->cinema_code) }}" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái *</label>
        <select name="status" class="form-select" required>
            @foreach(['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'] as $k => $v)
                <option value="{{ $k }}" @selected(old('status', $cinema->status ?? 'ACTIVE') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên rạp *</label>
        <input name="name" value="{{ old('name', $cinema->name) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Điện thoại</label>
        <input name="phone" value="{{ old('phone', $cinema->phone) }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $cinema->email) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Timezone *</label>
        <input name="timezone" value="{{ old('timezone', $cinema->timezone ?? 'Asia/Ho_Chi_Minh') }}" class="form-control" required>
    </div>

    <div class="col-md-8">
        <label class="form-label">Địa chỉ</label>
        <input name="address_line" value="{{ old('address_line', $cinema->address_line) }}" class="form-control" placeholder="Số nhà, đường...">
    </div>

    <div class="col-md-4">
        <label class="form-label">Phường/Xã</label>
        <input name="ward" value="{{ old('ward', $cinema->ward) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Quận/Huyện</label>
        <input name="district" value="{{ old('district', $cinema->district) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Tỉnh/TP</label>
        <input name="province" value="{{ old('province', $cinema->province) }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Mã quốc gia *</label>
        <input name="country_code" value="{{ old('country_code', $cinema->country_code ?? 'VN') }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Latitude</label>
        <input name="latitude" value="{{ old('latitude', $cinema->latitude) }}" class="form-control" placeholder="10.1234567">
    </div>

    <div class="col-md-3">
        <label class="form-label">Longitude</label>
        <input name="longitude" value="{{ old('longitude', $cinema->longitude) }}" class="form-control" placeholder="106.1234567">
    </div>

    <div class="col-12">
        <label class="form-label">Giờ mở cửa (JSON)</label>
        <textarea name="opening_hours" rows="4" class="form-control" placeholder='{"mon":"09:00-23:00","tue":"09:00-23:00"}'>{{ $openingHoursJson }}</textarea>
        <div class="form-text">Bỏ trống nếu không dùng.</div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo rạp' }}</button>
    <a href="{{ route('admin.cinemas.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
