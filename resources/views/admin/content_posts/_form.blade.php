@php($isEdit = $contentPost->exists)

<div class="section-card">
    <h3>Thông tin cơ bản</h3>
    <p class="section-description">Nội dung có thể là bài viết tin tức hoặc chương trình ưu đãi hiển thị ngoài giao diện người dùng.</p>

    <div class="row g-3">
        <div class="col-lg-3">
            <label class="form-label">Loại nội dung *</label>
            <select name="type" class="form-select" required>
                @foreach($typeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('type', $contentPost->type) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $contentPost->status) === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-3">
            <label class="form-label">Sort order</label>
            <input type="number" min="0" name="sort_order" value="{{ old('sort_order', $contentPost->sort_order ?? 0) }}" class="form-control">
        </div>
        <div class="col-lg-3 d-flex align-items-end">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $contentPost->is_featured))>
                <label class="form-check-label" for="is_featured">Đánh dấu nổi bật</label>
            </div>
        </div>

        <div class="col-12">
            <label class="form-label">Tiêu đề *</label>
            <input type="text" name="title" value="{{ old('title', $contentPost->title) }}" class="form-control" placeholder="Ví dụ: Khuyến mãi cuối tuần / Tin tức phim mới" required>
        </div>

        <div class="col-lg-8">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" value="{{ old('slug', $contentPost->slug) }}" class="form-control" placeholder="de-trong-de-tu-dong-tao-tu-tieu-de">
            <div class="form-text">Để trống để hệ thống tự tạo slug từ tiêu đề.</div>
        </div>
        <div class="col-lg-4">
            <label class="form-label">Nhãn badge</label>
            <input type="text" name="badge_label" value="{{ old('badge_label', $contentPost->badge_label) }}" class="form-control" placeholder="Hot / Thành viên / Ưu đãi mới">
        </div>

        <div class="col-12">
            <label class="form-label">Ảnh bìa</label>
            <input type="url" name="cover_image_url" value="{{ old('cover_image_url', $contentPost->cover_image_url) }}" class="form-control" placeholder="https://example.com/banner.jpg">
        </div>

        <div class="col-12">
            <label class="form-label">Mô tả ngắn</label>
            <textarea name="excerpt" rows="3" class="form-control" placeholder="Đoạn mô tả ngắn để hiển thị trên trang danh sách">{{ old('excerpt', $contentPost->excerpt) }}</textarea>
        </div>

        <div class="col-12">
            <label class="form-label">Nội dung chi tiết</label>
            <p class="section-description mb-3">Dùng các khối dưới đây để đăng bài nhanh: văn bản → ảnh → văn bản. Hệ thống sẽ tự ghép nội dung và hiển thị ảnh đúng thứ tự ngoài website.</p>

            <input type="hidden" name="content" id="contentPayload" value="{{ old('content', $contentPost->content) }}">

            <div id="contentBuilder" class="d-grid gap-3"></div>

            <div class="d-flex gap-2 flex-wrap mt-3">
                <button type="button" class="btn btn-outline-primary btn-sm" id="addTextBlock"><i class="bi bi-text-paragraph me-1"></i>Thêm đoạn text</button>
                <button type="button" class="btn btn-outline-secondary btn-sm" id="addImageBlock"><i class="bi bi-image me-1"></i>Thêm ảnh</button>
            </div>

            <details class="mt-3">
                <summary class="small text-secondary">Xem/sửa nội dung thô</summary>
                <textarea id="rawContentEditor" rows="8" class="form-control mt-2" placeholder="Nội dung thô sẽ được tự đồng bộ từ các khối phía trên">{{ old('content', $contentPost->content) }}</textarea>
            </details>
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Lịch hiển thị</h3>
    <p class="section-description">Thiết lập thời điểm xuất bản và khoảng thời gian hiển thị cho nội dung.</p>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Published at</label>
            <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($contentPost->published_at)->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
        <div class="col-lg-4">
            <label class="form-label">Starts at</label>
            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', optional($contentPost->starts_at)->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
        <div class="col-lg-4">
            <label class="form-label">Ends at</label>
            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', optional($contentPost->ends_at)->format('Y-m-d\TH:i')) }}" class="form-control">
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit"><i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo nội dung' }}</button>
    <a href="{{ route('admin.content_posts.index') }}" class="btn btn-light-soft"><i class="bi bi-arrow-left me-1"></i> Quay lại danh sách</a>
</div>


<script>
(function () {
    const payload = document.getElementById('contentPayload');
    const rawEditor = document.getElementById('rawContentEditor');
    const builder = document.getElementById('contentBuilder');
    const addText = document.getElementById('addTextBlock');
    const addImage = document.getElementById('addImageBlock');

    function blockTemplate(type, value = '') {
        const wrapper = document.createElement('div');
        wrapper.className = 'content-builder-block rounded-4 p-3';
        wrapper.style.border = '1px solid rgba(148,163,184,.28)';
        wrapper.style.background = 'rgba(15,23,42,.025)';
        wrapper.dataset.type = type;

        if (type === 'image') {
            wrapper.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><i class="bi bi-image me-1"></i>Khối ảnh</strong>
                    <button type="button" class="btn btn-sm btn-light-soft remove-block">Xóa</button>
                </div>
                <input type="url" class="form-control block-value" value="${escapeHtml(value)}" placeholder="https://example.com/image.jpg">
                <div class="form-text">Dán URL ảnh. Khi hiển thị ngoài website, ảnh sẽ nằm giữa các đoạn text.</div>
            `;
        } else {
            wrapper.innerHTML = `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong><i class="bi bi-text-paragraph me-1"></i>Khối văn bản</strong>
                    <button type="button" class="btn btn-sm btn-light-soft remove-block">Xóa</button>
                </div>
                <textarea class="form-control block-value" rows="5" placeholder="Nhập đoạn nội dung...">${escapeHtml(value)}</textarea>
            `;
        }

        wrapper.querySelector('.remove-block').addEventListener('click', function () {
            wrapper.remove();
            syncRawFromBlocks();
        });
        wrapper.querySelector('.block-value').addEventListener('input', syncRawFromBlocks);

        return wrapper;
    }

    function escapeHtml(value) {
        return String(value || '').replace(/[&<>"]/g, function (char) {
            return ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[char]);
        });
    }

    function parseRaw(raw) {
        const lines = String(raw || '').split(/\r?\n/);
        const blocks = [];
        let textBuffer = [];

        function flushText() {
            const text = textBuffer.join('\n').trim();
            if (text) blocks.push({ type: 'text', value: text });
            textBuffer = [];
        }

        lines.forEach(line => {
            const match = line.trim().match(/^!\[[^\]]*\]\((https?:\/\/[^)]+)\)$/i);
            if (match) {
                flushText();
                blocks.push({ type: 'image', value: match[1] });
            } else {
                textBuffer.push(line);
            }
        });
        flushText();

        return blocks.length ? blocks : [{ type: 'text', value: '' }, { type: 'image', value: '' }, { type: 'text', value: '' }];
    }

    function syncRawFromBlocks() {
        const chunks = Array.from(builder.querySelectorAll('.content-builder-block')).map(block => {
            const value = block.querySelector('.block-value').value.trim();
            if (!value) return '';
            return block.dataset.type === 'image' ? `![Ảnh minh họa](${value})` : value;
        }).filter(Boolean);

        const raw = chunks.join('\n\n');
        payload.value = raw;
        rawEditor.value = raw;
    }

    function renderBlocks(raw) {
        builder.innerHTML = '';
        parseRaw(raw).forEach(block => builder.appendChild(blockTemplate(block.type, block.value)));
        syncRawFromBlocks();
    }

    addText.addEventListener('click', function () {
        builder.appendChild(blockTemplate('text'));
        syncRawFromBlocks();
    });
    addImage.addEventListener('click', function () {
        builder.appendChild(blockTemplate('image'));
        syncRawFromBlocks();
    });
    rawEditor.addEventListener('input', function () {
        payload.value = rawEditor.value;
    });

    document.addEventListener('DOMContentLoaded', function () {
        renderBlocks(payload.value);
    });
    const form = payload.closest('form');
    if (form) form.addEventListener('submit', syncRawFromBlocks);
})();
</script>
