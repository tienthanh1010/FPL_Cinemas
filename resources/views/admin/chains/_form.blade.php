@php
    $isEdit = $chain->exists;
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Mã chuỗi *</label>
        <input name="chain_code" value="{{ old('chain_code', $chain->chain_code) }}" class="form-control" required>
    </div>

    <div class="col-md-8">
        <label class="form-label">Tên chuỗi *</label>
        <input name="name" value="{{ old('name', $chain->name) }}" class="form-control" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên pháp lý</label>
        <input name="legal_name" value="{{ old('legal_name', $chain->legal_name) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Mã số thuế</label>
        <input name="tax_code" value="{{ old('tax_code', $chain->tax_code) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Hotline</label>
        <input name="hotline" value="{{ old('hotline', $chain->hotline) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="{{ old('email', $chain->email) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Website</label>
        <input name="website" value="{{ old('website', $chain->website) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái *</label>
        <select name="status" class="form-select" required>
            @foreach(['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'] as $k => $v)
                <option value="{{ $k }}" @selected(old('status', $chain->status ?? 'ACTIVE') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo chuỗi' }}</button>
    <a href="{{ route('admin.chains.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
