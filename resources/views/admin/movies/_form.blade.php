@php
    $isEdit = $movie->exists;
    $selectedGenreIds = collect($selectedGenreIds ?? [])->map(fn($id) => (string) $id)->all();
    $roleLabels = admin_movie_role_labels();
@endphp

<div class="section-card">
    <h3>Thông tin phim</h3>
    <p class="section-description">Nhóm thông tin cốt lõi để hiển thị trên hệ thống bán vé và quản trị nội dung.</p>

    <div class="row g-3">
        <div class="col-lg-8">
            <label class="form-label">Tiêu đề phim *</label>
            <input name="title" value="{{ old('title', $movie->title) }}" class="form-control" placeholder="Ví dụ: Doraemon Movie 30" required>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $movie->status ?? 'ACTIVE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Tên gốc</label>
            <input name="original_title" value="{{ old('original_title', $movie->original_title) }}" class="form-control" placeholder="Tên quốc tế hoặc tên phát hành gốc">
        </div>

        <div class="col-lg-3">
            <label class="form-label">Thời lượng (phút) *</label>
            <input type="number" min="1" max="600" name="duration_minutes" value="{{ old('duration_minutes', $movie->duration_minutes) }}" class="form-control" required>
        </div>

        <div class="col-lg-3">
            <label class="form-label">Ngày phát hành</label>
            <input type="date" name="release_date" value="{{ old('release_date', optional($movie->release_date)->format('Y-m-d')) }}" class="form-control">
        </div>

        <div class="col-lg-4">
            <label class="form-label">Phân loại độ tuổi</label>
            <select name="content_rating_id" class="form-select">
                <option value="">-- Chưa phân loại --</option>
                @foreach($ratings as $rating)
                    <option value="{{ $rating->id }}" @selected((string) old('content_rating_id', $movie->content_rating_id) === (string) $rating->id)>
                        {{ $rating->code }} - {{ $rating->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Ngôn ngữ gốc *</label>
            <select name="language_original" class="form-select" required>
                @foreach($languageOptions as $code => $label)
                    <option value="{{ $code }}" @selected(old('language_original', $movie->language_original ?? 'VI') === $code)>{{ $code }} · {{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Số giấy phép phổ biến</label>
            <input name="censorship_license_no" value="{{ old('censorship_license_no', $movie->censorship_license_no) }}" class="form-control" placeholder="Ví dụ: 1234/QĐ-BVHTTDL">
        </div>

        <div class="col-12">
            <label class="form-label">Tóm tắt nội dung</label>
            <textarea name="synopsis" rows="5" class="form-control" placeholder="Mô tả ngắn gọn nội dung phim, điểm nhấn, bối cảnh...">{{ old('synopsis', $movie->synopsis) }}</textarea>
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Dữ liệu liên kết</h3>
    <p class="section-description">Không nhập rời rạc. Phim nên được gắn thể loại, ê-kíp sáng tạo và dàn diễn viên để dữ liệu có tính logic.</p>

    <div class="row g-3">
        <div class="col-12">
            <label class="form-label">Thể loại phim</label>
            <select name="genre_ids[]" class="form-select" multiple>
                @foreach($genres as $genre)
                    <option value="{{ $genre->id }}" @selected(in_array((string) $genre->id, $selectedGenreIds, true))>
                        {{ $genre->name }} ({{ $genre->code }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Giữ Ctrl / Cmd để chọn nhiều thể loại. Bạn cũng có thể quản lý riêng ở mục <strong>Thể loại</strong>.</div>
        </div>

        <div class="col-lg-4">
            <label class="form-label">{{ $roleLabels['DIRECTOR'] }}</label>
            <textarea name="credit_director_names" rows="4" class="form-control" placeholder="Ví dụ: Trấn Thành, Victor Vũ">{{ $creditValues['DIRECTOR'] ?? '' }}</textarea>
            <div class="form-text">Nhập tên cách nhau bằng dấu phẩy. Hệ thống sẽ tự tạo liên kết vào bảng <span class="font-monospace">people</span>.</div>
        </div>

        <div class="col-lg-4">
            <label class="form-label">{{ $roleLabels['WRITER'] }}</label>
            <textarea name="credit_writer_names" rows="4" class="form-control" placeholder="Ví dụ: Charlie Nguyễn, Nguyễn Quang Dũng">{{ $creditValues['WRITER'] ?? '' }}</textarea>
            <div class="form-text">Nên ghi rõ biên kịch/chuyển thể để dữ liệu phim không bị sơ sài.</div>
        </div>

        <div class="col-lg-4">
            <label class="form-label">{{ $roleLabels['CAST'] }}</label>
            <textarea name="credit_cast_names" rows="4" class="form-control" placeholder="Ví dụ: Trường Giang, Mỹ Tâm, POM Nguyễn">{{ $creditValues['CAST'] ?? '' }}</textarea>
            <div class="form-text">Dùng cho dàn diễn viên chính hoặc các nhân sự nổi bật.</div>
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Poster & trailer</h3>
    <p class="section-description">Thay vì nhập link tuỳ tiện, biểu mẫu này giới hạn đúng kiểu URL có thể sử dụng được.</p>

    <div class="row g-4 align-items-start">
        <div class="col-xl-7">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Poster URL</label>
                    <input id="poster_url_input"
                           type="url"
                           name="poster_url"
                           value="{{ old('poster_url', $movie->poster_url) }}"
                           class="form-control"
                           placeholder="https://example.com/poster.jpg">
                    <div class="form-text">Chỉ nên dùng link ảnh trực tiếp có đuôi <span class="font-monospace">.jpg</span>, <span class="font-monospace">.png</span>, <span class="font-monospace">.webp</span>, <span class="font-monospace">.avif</span>.</div>
                </div>

                <div class="col-12">
                    <label class="form-label">Trailer URL</label>
                    <input id="trailer_url_input"
                           type="url"
                           name="trailer_url"
                           value="{{ old('trailer_url', $movie->trailer_url) }}"
                           class="form-control"
                           placeholder="https://www.youtube.com/watch?v=... hoặc https://vimeo.com/...">
                    <div class="form-text">Chỉ hỗ trợ YouTube và Vimeo để tránh nhập các link không thể phát hoặc không đúng định dạng.</div>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="row g-3">
                <div class="col-12">
                    <div class="media-preview-card">
                        <h4><i class="bi bi-image me-1"></i> Xem trước poster</h4>
                        <div id="poster_preview" class="media-preview-empty">Dán Poster URL hợp lệ để xem trước ảnh</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="media-preview-card">
                        <h4><i class="bi bi-play-btn me-1"></i> Xem trước trailer</h4>
                        <div id="trailer_preview" class="media-preview-empty">Dán link YouTube / Vimeo để xem trước trailer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-card">
    <div class="d-flex align-items-start justify-content-between gap-3 flex-wrap">
        <div>
            <h3>Phiên bản phim</h3>
            <p class="section-description mb-0">Quản lý luôn các định dạng chiếu như 2D, 3D, IMAX và ngôn ngữ audio/subtitle ngay trong form phim.</p>
        </div>
        <button class="btn btn-light-soft" type="button" id="add_version_row">
            <i class="bi bi-plus-circle me-1"></i> Thêm phiên bản
        </button>
    </div>

    <div class="hint-box mt-3 mb-3">
        <i class="bi bi-info-circle me-1"></i>
        Các phiên bản đã có sẵn có thể sửa trực tiếp tại đây. Nếu muốn xoá phiên bản đang dùng bởi suất chiếu, bạn nên xử lý ở mục <strong>Phiên bản phim</strong> để kiểm soát an toàn hơn.
    </div>

    <div id="version_rows" class="version-grid">
        @foreach($versionRows as $index => $row)
            @php $isPersistedRow = !empty($row['id']); @endphp
            <div class="version-row" data-version-row>
                <input type="hidden" name="versions[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="fw-bold">
                        Phiên bản #{{ $index + 1 }}
                        @if($isPersistedRow)
                            <span class="badge badge-soft-secondary ms-2">Đã lưu</span>
                        @else
                            <span class="badge badge-soft-primary ms-2">Mới</span>
                        @endif
                    </div>
                    @unless($isPersistedRow)
                        <button type="button" class="btn btn-sm btn-outline-danger" data-remove-version>
                            <i class="bi bi-trash3 me-1"></i> Bỏ dòng
                        </button>
                    @endunless
                </div>

                <div class="row g-3">
                    <div class="col-lg-3">
                        <label class="form-label">Định dạng *</label>
                        <select name="versions[{{ $index }}][format]" class="form-select">
                            <option value="">-- Chọn --</option>
                            @foreach($formatOptions as $formatValue => $formatLabel)
                                <option value="{{ $formatValue }}" @selected(($row['format'] ?? '2D') === $formatValue)>{{ $formatLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Audio *</label>
                        <select name="versions[{{ $index }}][audio_language]" class="form-select">
                            <option value="">-- Chọn --</option>
                            @foreach($languageOptions as $langCode => $langLabel)
                                <option value="{{ $langCode }}" @selected(($row['audio_language'] ?? '') === $langCode)>{{ $langCode }} · {{ $langLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Subtitle</label>
                        <select name="versions[{{ $index }}][subtitle_language]" class="form-select">
                            <option value="">Không có</option>
                            @foreach($languageOptions as $langCode => $langLabel)
                                <option value="{{ $langCode }}" @selected(($row['subtitle_language'] ?? '') === $langCode)>{{ $langCode }} · {{ $langLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">Ghi chú</label>
                        <input name="versions[{{ $index }}][notes]" value="{{ $row['notes'] ?? '' }}" class="form-control" placeholder="Ví dụ: Lồng tiếng Việt">
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo phim' }}
    </button>
    <a href="{{ route('admin.movies.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

<template id="version_row_template">
    <div class="version-row" data-version-row>
        <input type="hidden" name="__NAME__[id]" value="">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="fw-bold">Phiên bản mới <span class="badge badge-soft-primary ms-2">Mới</span></div>
            <button type="button" class="btn btn-sm btn-outline-danger" data-remove-version>
                <i class="bi bi-trash3 me-1"></i> Bỏ dòng
            </button>
        </div>
        <div class="row g-3">
            <div class="col-lg-3">
                <label class="form-label">Định dạng *</label>
                <select name="__NAME__[format]" class="form-select">
                    <option value="">-- Chọn --</option>
                    @foreach($formatOptions as $formatValue => $formatLabel)
                        <option value="{{ $formatValue }}">{{ $formatLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Audio *</label>
                <select name="__NAME__[audio_language]" class="form-select">
                    <option value="">-- Chọn --</option>
                    @foreach($languageOptions as $langCode => $langLabel)
                        <option value="{{ $langCode }}">{{ $langCode }} · {{ $langLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Subtitle</label>
                <select name="__NAME__[subtitle_language]" class="form-select">
                    <option value="">Không có</option>
                    @foreach($languageOptions as $langCode => $langLabel)
                        <option value="{{ $langCode }}">{{ $langCode }} · {{ $langLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3">
                <label class="form-label">Ghi chú</label>
                <input name="__NAME__[notes]" class="form-control" placeholder="Ví dụ: Phụ đề EN">
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script>
    (function () {
        const posterInput = document.getElementById('poster_url_input');
        const trailerInput = document.getElementById('trailer_url_input');
        const posterPreview = document.getElementById('poster_preview');
        const trailerPreview = document.getElementById('trailer_preview');
        const versionRows = document.getElementById('version_rows');
        const addVersionButton = document.getElementById('add_version_row');
        const versionTemplate = document.getElementById('version_row_template');

        const renderPosterPreview = () => {
            const value = posterInput?.value?.trim() ?? '';
            if (!value) {
                posterPreview.innerHTML = 'Dán Poster URL hợp lệ để xem trước ảnh';
                posterPreview.className = 'media-preview-empty';
                return;
            }

            posterPreview.className = '';
            posterPreview.innerHTML = `<img src="${value}" alt="Poster preview" onerror="this.closest('#poster_preview').className='media-preview-empty'; this.closest('#poster_preview').textContent='Poster URL không phải ảnh hợp lệ.';">`;
        };

        const buildTrailerEmbedUrl = (value) => {
            try {
                const url = new URL(value);
                const host = url.hostname.replace('www.', '');

                if (host === 'youtube.com' || host === 'm.youtube.com') {
                    const videoId = url.searchParams.get('v');
                    return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
                }

                if (host === 'youtu.be') {
                    const videoId = url.pathname.replace(/^\//, '');
                    return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
                }

                if (host === 'vimeo.com') {
                    const videoId = url.pathname.replace(/^\//, '');
                    return videoId ? `https://player.vimeo.com/video/${videoId}` : '';
                }
            } catch (error) {
                return '';
            }

            return '';
        };

        const renderTrailerPreview = () => {
            const value = trailerInput?.value?.trim() ?? '';
            const embedUrl = buildTrailerEmbedUrl(value);

            if (!embedUrl) {
                trailerPreview.innerHTML = 'Dán link YouTube / Vimeo để xem trước trailer';
                trailerPreview.className = 'media-preview-empty';
                return;
            }

            trailerPreview.className = '';
            trailerPreview.innerHTML = `<iframe src="${embedUrl}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>`;
        };

        const refreshVersionIndexes = () => {
            [...versionRows.querySelectorAll('[data-version-row]')].forEach((row, index) => {
                row.querySelectorAll('input, select, textarea').forEach((field) => {
                    if (!field.name) return;
                    field.name = field.name.replace(/versions\[\d+\]/g, `versions[${index}]`);
                });
            });
        };

        addVersionButton?.addEventListener('click', () => {
            const index = versionRows.querySelectorAll('[data-version-row]').length;
            const html = versionTemplate.innerHTML.replaceAll('__NAME__', `versions[${index}]`);
            versionRows.insertAdjacentHTML('beforeend', html);
            refreshVersionIndexes();
        });

        versionRows?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-remove-version]');
            if (!button) return;
            button.closest('[data-version-row]')?.remove();
            refreshVersionIndexes();
        });

        posterInput?.addEventListener('input', renderPosterPreview);
        trailerInput?.addEventListener('input', renderTrailerPreview);

        renderPosterPreview();
        renderTrailerPreview();
    })();
</script>
@endpush
