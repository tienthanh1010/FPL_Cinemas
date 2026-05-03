@php
    $isEdit = $movie->exists;
@endphp

<div class="row g-3">
    <div class="col-md-8">
        <label class="form-label">Tiêu đề *</label>
        <input name="title" value="{{ old('title', $movie->title) }}" class="form-control" required>
    </div>

    <div class="col-md-4">
        <label class="form-label">Trạng thái *</label>
        <select name="status" class="form-select" required>
            @foreach(['ACTIVE' => 'ACTIVE', 'INACTIVE' => 'INACTIVE'] as $k => $v)
                <option value="{{ $k }}" @selected(old('status', $movie->status ?? 'ACTIVE') === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Tên gốc</label>
        <input name="original_title" value="{{ old('original_title', $movie->original_title) }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Thời lượng (phút) *</label>
        <input type="number" min="1" name="duration_minutes" value="{{ old('duration_minutes', $movie->duration_minutes) }}" class="form-control" required>
    </div>

    <div class="col-md-3">
        <label class="form-label">Ngày phát hành</label>
        <input type="date" name="release_date" value="{{ old('release_date', optional($movie->release_date)->format('Y-m-d')) }}" class="form-control">
    </div>

    <div class="col-md-4">
        <label class="form-label">Phân loại</label>
        <select name="content_rating_id" class="form-select">
            <option value="">-- Không chọn --</option>
            @foreach($ratings as $r)
                <option value="{{ $r->id }}" @selected((string)old('content_rating_id', $movie->content_rating_id) === (string)$r->id)>
                    {{ $r->code }} - {{ $r->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-4">
        <label class="form-label">Ngôn ngữ gốc</label>
        <input name="language_original" value="{{ old('language_original', $movie->language_original) }}" class="form-control" placeholder="VI / EN ...">
    </div>

    <div class="col-md-4">
        <label class="form-label">Số giấy phép phổ biến</label>
        <input name="censorship_license_no" value="{{ old('censorship_license_no', $movie->censorship_license_no) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Poster URL</label>
        <input name="poster_url" value="{{ old('poster_url', $movie->poster_url) }}" class="form-control">
    </div>

    <div class="col-md-6">
        <label class="form-label">Trailer URL</label>
        <input name="trailer_url" value="{{ old('trailer_url', $movie->trailer_url) }}" class="form-control">
    </div>

    <div class="col-12">
        <label class="form-label">Tóm tắt</label>
        <textarea name="synopsis" rows="4" class="form-control">{{ old('synopsis', $movie->synopsis) }}</textarea>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button class="btn btn-primary">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo phim' }}</button>
    <a href="{{ route('admin.movies.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
