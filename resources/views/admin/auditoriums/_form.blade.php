@php
    $isEdit = $auditorium->exists;
@endphp

<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">Rạp *</label>
        <select name="cinema_id" class="form-select" required>
            <option value="">-- Chọn rạp --</option>
            @foreach($cinemas as $c)
                <option value="{{ $c->id }}" @selected((string)old('cinema_id', $auditorium->cinema_id) === (string)$c->id)>
                    {{ $c->name }} ({{ $c->cinema_code }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Nếu danh sách rạp trống, hãy tạo rạp trước.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label">Mã phòng *</label>
        <input name="auditorium_code" value="{{ old('auditorium_code', $auditorium->auditorium_code) }}" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Loại màn *</label>
        <select name="screen_type" class="form-select" required>
            @foreach($screenTypes as $t)
                <option value="{{ $t }}" @selected(old('screen_type', $auditorium->screen_type ?? 'STANDARD') === $t)>{{ $t }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên phòng *</label>
        <input name="name" value="{{ old('name', $auditorium->name) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Seat map version *</label>
        <input type="number" min="1" name="seat_map_version" value="{{ old('seat_map_version', $auditorium->seat_map_version ?? 1) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Hoạt động *</label>
        <select name="is_active" class="form-select" required>
            <option value="1" @selected((string)old('is_active', (int)($auditorium->is_active ?? 1)) === '1')>YES</option>
            <option value="0" @selected((string)old('is_active', (int)($auditorium->is_active ?? 1)) === '0')>NO</option>
        </select>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo phòng' }}</button>
    <a href="{{ route('admin.auditoriums.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
