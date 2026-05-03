@php
    $isEdit = $auditorium->exists;
@endphp

<div class="section-card">
    <h3>Thông tin phòng chiếu</h3>
    <p class="section-description">Phòng chiếu sẽ tự động gắn vào rạp duy nhất của hệ thống.</p>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Mã phòng *</label>
            <input name="auditorium_code" value="{{ old('auditorium_code', $auditorium->auditorium_code) }}" class="form-control" placeholder="Ví dụ: AUD1_1" required>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Loại màn *</label>
            <select name="screen_type" class="form-select" required>
                @foreach($screenTypes as $screenType)
                    <option value="{{ $screenType }}" @selected(old('screen_type', $auditorium->screen_type ?? 'STANDARD') === $screenType)>{{ $screenType }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Hoạt động *</label>
            <select name="is_active" class="form-select" required>
                <option value="1" @selected((string) old('is_active', (int) ($auditorium->is_active ?? 1)) === '1')>Có</option>
                <option value="0" @selected((string) old('is_active', (int) ($auditorium->is_active ?? 1)) === '0')>Không</option>
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Tên phòng *</label>
            <input name="name" value="{{ old('name', $auditorium->name) }}" class="form-control" placeholder="Ví dụ: Phòng 1" required>
        </div>

        <div class="col-lg-3">
            <label class="form-label">Seat map version *</label>
            <input type="number" min="1" name="seat_map_version" value="{{ old('seat_map_version', $auditorium->seat_map_version ?? 1) }}" class="form-control" required>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo phòng' }}
    </button>
    <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>
