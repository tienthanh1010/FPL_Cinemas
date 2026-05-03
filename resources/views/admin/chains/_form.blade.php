@php
    $isEdit = $chain->exists;
@endphp

<div class="section-card">
    <h3>Thông tin chuỗi rạp</h3>
    <p class="section-description">Dùng để chuẩn hoá dữ liệu hệ thống rạp trước khi tạo từng rạp thành viên.</p>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Mã chuỗi *</label>
            <input name="chain_code" value="{{ old('chain_code', $chain->chain_code) }}" class="form-control" placeholder="Ví dụ: beta" required>
        </div>

        <div class="col-lg-8">
            <label class="form-label">Tên chuỗi *</label>
            <input name="name" value="{{ old('name', $chain->name) }}" class="form-control" placeholder="Ví dụ: BETA CINEMA" required>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Tên pháp lý</label>
            <input name="legal_name" value="{{ old('legal_name', $chain->legal_name) }}" class="form-control" placeholder="Ví dụ: Công ty Cổ phần ...">
        </div>

        <div class="col-lg-6">
            <label class="form-label">Mã số thuế</label>
            <input name="tax_code" value="{{ old('tax_code', $chain->tax_code) }}" class="form-control">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Hotline</label>
            <input name="hotline" value="{{ old('hotline', $chain->hotline) }}" class="form-control">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ old('email', $chain->email) }}" class="form-control">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Website</label>
            <input type="url" name="website" value="{{ old('website', $chain->website) }}" class="form-control" placeholder="https://example.com">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach(['ACTIVE' => 'Đang hoạt động', 'INACTIVE' => 'Tạm ẩn'] as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $chain->status ?? 'ACTIVE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo chuỗi' }}
    </button>
    <a href="{{ route('admin.chains.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>
