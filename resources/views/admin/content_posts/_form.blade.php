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
            <textarea name="content" rows="12" class="form-control" placeholder="Nhập nội dung chi tiết, thể lệ ưu đãi hoặc thông báo cần hiển thị ngoài website">{{ old('content', $contentPost->content) }}</textarea>
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
