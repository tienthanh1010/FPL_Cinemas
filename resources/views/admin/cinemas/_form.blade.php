@php
    $isEdit = $cinema->exists;
@endphp

<div class="section-card">
    <h3>Thông tin rạp</h3>
    <p class="section-description">Hệ thống này chỉ quản lý 1 rạp duy nhất, nên bạn chỉ cần cập nhật thật chuẩn thông tin rạp đang vận hành.</p>

<div class="section-card">
    <h3>Thông tin rạp</h3>
    <p class="section-description">Hệ thống này chỉ quản lý 1 rạp duy nhất, nên bạn chỉ cần cập nhật thật chuẩn thông tin rạp đang vận hành.</p>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Mã rạp *</label>
            <input name="cinema_code" value="{{ old('cinema_code', $cinema->cinema_code) }}" class="form-control" placeholder="Ví dụ: HN01" required>
        </div>
    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Mã rạp *</label>
            <input name="cinema_code" value="{{ old('cinema_code', $cinema->cinema_code) }}" class="form-control" placeholder="Ví dụ: HN01" required>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach(['ACTIVE' => 'Đang hoạt động', 'INACTIVE' => 'Tạm ẩn'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $cinema->status ?? 'ACTIVE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Timezone *</label>
            <select name="timezone" class="form-select" required>
                @foreach($timezones as $value => $label)
                    <option value="{{ $value }}" @selected(old('timezone', $cinema->timezone ?? 'Asia/Ho_Chi_Minh') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-4">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach(['ACTIVE' => 'Đang hoạt động', 'INACTIVE' => 'Tạm ẩn'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $cinema->status ?? 'ACTIVE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Timezone *</label>
            <select name="timezone" class="form-select" required>
                @foreach($timezones as $value => $label)
                    <option value="{{ $value }}" @selected(old('timezone', $cinema->timezone ?? 'Asia/Ho_Chi_Minh') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Tên rạp *</label>
            <input name="name" value="{{ old('name', $cinema->name) }}" class="form-control" placeholder="Ví dụ: Rạp FPL Cinemas" required>
        </div>

        <div class="col-lg-3">
            <label class="form-label">Điện thoại</label>
            <input name="phone" value="{{ old('phone', $cinema->phone) }}" class="form-control" placeholder="1900 1234">
        </div>
        <div class="col-lg-3">
            <label class="form-label">Điện thoại</label>
            <input name="phone" value="{{ old('phone', $cinema->phone) }}" class="form-control" placeholder="1900 1234">
        </div>

        <div class="col-lg-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $cinema->email) }}" class="form-control" placeholder="rap@example.com">
        </div>
        <div class="col-lg-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $cinema->email) }}" class="form-control" placeholder="rap@example.com">
        </div>

        <div class="col-lg-8">
            <label class="form-label">Địa chỉ</label>
            <input name="address_line" value="{{ old('address_line', $cinema->address_line) }}" class="form-control" placeholder="Số nhà, đường, toà nhà...">
        </div>
        <div class="col-lg-8">
            <label class="form-label">Địa chỉ</label>
            <input name="address_line" value="{{ old('address_line', $cinema->address_line) }}" class="form-control" placeholder="Số nhà, đường, toà nhà...">
        </div>

        <div class="col-md-4">
            <label class="form-label">Phường/Xã</label>
            <input name="ward" value="{{ old('ward', $cinema->ward) }}" class="form-control">
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
            <label class="form-label">Quận/Huyện</label>
            <input name="district" value="{{ old('district', $cinema->district) }}" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Tỉnh/Thành phố</label>
            <input name="province" value="{{ old('province', $cinema->province) }}" class="form-control">
        </div>
        <div class="col-md-4">
            <label class="form-label">Tỉnh/Thành phố</label>
            <input name="province" value="{{ old('province', $cinema->province) }}" class="form-control">
        </div>

        <div class="col-md-4">
            <label class="form-label">Mã quốc gia *</label>
            <input name="country_code" value="{{ old('country_code', $cinema->country_code ?? 'VN') }}" class="form-control text-uppercase" maxlength="2" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Mã quốc gia *</label>
            <input name="country_code" value="{{ old('country_code', $cinema->country_code ?? 'VN') }}" class="form-control text-uppercase" maxlength="2" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Latitude</label>
            <input name="latitude" value="{{ old('latitude', $cinema->latitude) }}" class="form-control" placeholder="21.0277638">
        </div>
        <div class="col-md-4">
            <label class="form-label">Latitude</label>
            <input name="latitude" value="{{ old('latitude', $cinema->latitude) }}" class="form-control" placeholder="21.0277638">
        </div>

        <div class="col-md-4">
            <label class="form-label">Longitude</label>
            <input name="longitude" value="{{ old('longitude', $cinema->longitude) }}" class="form-control" placeholder="105.8341598">
        </div>
    </div>
</div>
        <div class="col-md-4">
            <label class="form-label">Longitude</label>
            <input name="longitude" value="{{ old('longitude', $cinema->longitude) }}" class="form-control" placeholder="105.8341598">
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Giờ mở cửa theo ngày</h3>
    <div class="row g-3">
        @foreach($openingHourRows as $dayKey => $row)
            <div class="col-12">
                <div class="version-row">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="opening_hours_days[{{ $dayKey }}][enabled]" value="1" id="opening_hours_{{ $dayKey }}" {{ (string) $row['enabled'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="opening_hours_{{ $dayKey }}">{{ $row['label'] }}</label>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label">Mở cửa</label>
                            <input type="time" name="opening_hours_days[{{ $dayKey }}][open]" value="{{ $row['open'] }}" class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label">Đóng cửa</label>
                            <input type="time" name="opening_hours_days[{{ $dayKey }}][close]" value="{{ $row['close'] }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
<div class="section-card">
    <h3>Giờ mở cửa theo ngày</h3>
    <div class="row g-3">
        @foreach($openingHourRows as $dayKey => $row)
            <div class="col-12">
                <div class="version-row">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="opening_hours_days[{{ $dayKey }}][enabled]" value="1" id="opening_hours_{{ $dayKey }}" {{ (string) $row['enabled'] === '1' ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold" for="opening_hours_{{ $dayKey }}">{{ $row['label'] }}</label>
                            </div>
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label">Mở cửa</label>
                            <input type="time" name="opening_hours_days[{{ $dayKey }}][open]" value="{{ $row['open'] }}" class="form-control">
                        </div>
                        <div class="col-lg-4 col-md-4">
                            <label class="form-label">Đóng cửa</label>
                            <input type="time" name="opening_hours_days[{{ $dayKey }}][close]" value="{{ $row['close'] }}" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo rạp' }}
    </button>
    <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo rạp' }}
    </button>
    <a href="{{ route('admin.cinemas.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>
