@php
    $isEdit = $show->exists;
    $fmt = fn($dt) => $dt ? \Carbon\Carbon::parse($dt)->format('Y-m-d\TH:i') : '';
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Phòng chiếu *</label>
        <select name="auditorium_id" class="form-select" required>
            <option value="">-- Chọn phòng --</option>
            @foreach($auditoriums as $a)
                <option value="{{ $a->id }}" @selected((string)old('auditorium_id', $show->auditorium_id) === (string)$a->id)>
                    {{ $a->cinema?->name }} • {{ $a->name }} ({{ $a->auditorium_code }})
                </option>
            @endforeach
        </select>
        <div class="form-text">Nếu danh sách phòng trống, hãy tạo rạp & phòng chiếu trước.</div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Phiên bản phim *</label>
        <select name="movie_version_id" class="form-select" required>
            <option value="">-- Chọn phiên bản --</option>
            @foreach($movieVersions as $mv)
                <option value="{{ $mv->id }}" @selected((string)old('movie_version_id', $show->movie_version_id) === (string)$mv->id)>
                    {{ $mv->label }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Nếu danh sách này trống, bạn cần có dữ liệu trong bảng <span class="font-monospace">movie_versions</span> (và <span class="font-monospace">movies</span>).</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Bắt đầu *</label>
        <input type="datetime-local" name="start_time" value="{{ old('start_time', $fmt($show->start_time)) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Kết thúc *</label>
        <input type="datetime-local" name="end_time" value="{{ old('end_time', $fmt($show->end_time)) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Mở bán từ</label>
        <input type="datetime-local" name="on_sale_from" value="{{ old('on_sale_from', $fmt($show->on_sale_from)) }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Mở bán đến</label>
        <input type="datetime-local" name="on_sale_until" value="{{ old('on_sale_until', $fmt($show->on_sale_until)) }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Trạng thái *</label>
        <select name="status" class="form-select" required>
            @foreach($statuses as $st)
                <option value="{{ $st }}" @selected(old('status', $show->status ?? 'SCHEDULED') === $st)>{{ $st }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo suất' }}</button>
    <a href="{{ route('admin.shows.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
