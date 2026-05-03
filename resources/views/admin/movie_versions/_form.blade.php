@php
    $isEdit = $movieVersion->exists;
@endphp

<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Phim *</label>
        <select name="movie_id" class="form-select" required>
            <option value="">-- Chọn phim --</option>
            @foreach($movies as $m)
                <option value="{{ $m->id }}" @selected((string)old('movie_id', $movieVersion->movie_id) === (string)$m->id)>
                    {{ $m->title }}
                </option>
            @endforeach
        </select>
        <div class="form-text">Nếu danh sách phim trống, hãy tạo phim trước.</div>
    </div>

    <div class="col-md-3">
        <label class="form-label">Format *</label>
        <select name="format" class="form-select" required>
            @foreach($formats as $f)
                <option value="{{ $f }}" @selected(old('format', $movieVersion->format ?? '2D') === $f)>{{ $f }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3">
        <label class="form-label">Audio *</label>
        <input name="audio_language" value="{{ old('audio_language', $movieVersion->audio_language ?? 'VI') }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Sub</label>
        <input name="subtitle_language" value="{{ old('subtitle_language', $movieVersion->subtitle_language) }}" class="form-control" placeholder="VI">
    </div>

    <div class="col-md-9">
        <label class="form-label">Ghi chú</label>
        <input name="notes" value="{{ old('notes', $movieVersion->notes) }}" class="form-control">
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo phiên bản' }}</button>
    <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
