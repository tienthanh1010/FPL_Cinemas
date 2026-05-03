@php
    $isEdit = $movieVersion->exists;
@endphp

<div class="section-card">
    <h3>Thông tin phiên bản chiếu</h3>
    <p class="section-description">Dùng cho từng định dạng phát hành của phim như 2D, 3D, IMAX hoặc phiên bản lồng tiếng/phụ đề khác nhau.</p>

    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label">Phim *</label>
            <select name="movie_id" class="form-select" required>
                <option value="">-- Chọn phim --</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" @selected((string) old('movie_id', $movieVersion->movie_id) === (string) $movie->id)>
                        {{ $movie->title }}
                    </option>
                @endforeach
            </select>
            <div class="form-text">Nếu danh sách trống, hãy tạo phim trước rồi quay lại màn hình này.</div>
        </div>

        <div class="col-lg-2">
            <label class="form-label">Định dạng *</label>
            <select name="format" class="form-select" required>
                @foreach($formatOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('format', $movieVersion->format ?? '2D') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2">
            <label class="form-label">Audio *</label>
            <select name="audio_language" class="form-select" required>
                @foreach($languageOptions as $code => $label)
                    <option value="{{ $code }}" @selected(old('audio_language', $movieVersion->audio_language ?? 'VI') === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2">
            <label class="form-label">Subtitle</label>
            <select name="subtitle_language" class="form-select">
                <option value="">Không có</option>
                @foreach($languageOptions as $code => $label)
                    <option value="{{ $code }}" @selected(old('subtitle_language', $movieVersion->subtitle_language) === $code)>{{ $code }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Ghi chú</label>
            <input name="notes" value="{{ old('notes', $movieVersion->notes) }}" class="form-control" placeholder="Ví dụ: Lồng tiếng Việt, phụ đề EN, bản đặc biệt cuối tuần...">
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo phiên bản' }}
    </button>
    <a href="{{ route('admin.movie_versions.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>
