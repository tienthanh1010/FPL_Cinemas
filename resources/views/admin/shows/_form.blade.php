@php
    $isEdit = $show->exists;
    $selectedMovieId = old('movie_id', $selectedMovieId ?? null);
    $dateValue = old('show_date', optional($show->start_time)->format('Y-m-d'));
    $timeValue = old('start_clock', optional($show->start_time)->format('H:i'));
    $selectedMovieId = old('movie_id', $selectedMovieId ?? null);
    $dateValue = old('show_date', optional($show->start_time)->format('Y-m-d'));
    $timeValue = old('start_clock', optional($show->start_time)->format('H:i'));
@endphp

<div class="section-card">
    <h3>Thông tin cơ bản của suất chiếu</h3>
    <p class="section-description">Chọn phim, phòng, ngày chiếu và giờ bắt đầu. Giờ kết thúc sẽ tự tính theo thời lượng phim.</p>

    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label">Phim *</label>
            <select id="movie_id" name="movie_id" class="form-select" required>
                <option value="">-- Chọn phim --</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" data-duration="{{ $movie->duration_minutes }}" @selected((string) $selectedMovieId === (string) $movie->id)>
                        {{ $movie->title }} ({{ $movie->duration_minutes }} phút)
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Phòng chiếu *</label>
            <select name="auditorium_id" class="form-select" required>
                <option value="">-- Chọn phòng --</option>
                @foreach($auditoriums as $auditorium)
                    <option value="{{ $auditorium->id }}" @selected((string) old('auditorium_id', $show->auditorium_id) === (string) $auditorium->id)>
                        {{ $auditorium->name }} ({{ $auditorium->auditorium_code }})
                    </option>
                @endforeach
            </select>
        </div>
    <div class="row g-3">
        <div class="col-lg-6">
            <label class="form-label">Phim *</label>
            <select id="movie_id" name="movie_id" class="form-select" required>
                <option value="">-- Chọn phim --</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" data-duration="{{ $movie->duration_minutes }}" @selected((string) $selectedMovieId === (string) $movie->id)>
                        {{ $movie->title }} ({{ $movie->duration_minutes }} phút)
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Phòng chiếu *</label>
            <select name="auditorium_id" class="form-select" required>
                <option value="">-- Chọn phòng --</option>
                @foreach($auditoriums as $auditorium)
                    <option value="{{ $auditorium->id }}" @selected((string) old('auditorium_id', $show->auditorium_id) === (string) $auditorium->id)>
                        {{ $auditorium->name }} ({{ $auditorium->auditorium_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-6">
            <label class="form-label">Hồ sơ giá *</label>
            <select name="pricing_profile_id" class="form-select" required>
                <option value="">-- Chọn hồ sơ giá --</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}" @selected((string) old('pricing_profile_id', $show->pricing_profile_id) === (string) $profile->id)>
                        {{ $profile->name }} ({{ $profile->code }})
                    </option>
                @endforeach
            </select>
            <div class="form-text">Bạn có thể tạo/sửa hồ sơ giá ở menu Hồ sơ giá động.</div>
        </div>

        <div class="col-md-4">
            <label class="form-label">Ngày chiếu *</label>
            <input id="show_date" type="date" name="show_date" value="{{ $dateValue }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Ngày chiếu *</label>
            <input id="show_date" type="date" name="show_date" value="{{ $dateValue }}" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Giờ bắt đầu *</label>
            <input id="start_clock" type="time" name="start_clock" value="{{ $timeValue }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Giờ bắt đầu *</label>
            <input id="start_clock" type="time" name="start_clock" value="{{ $timeValue }}" class="form-control" required>
        </div>

        <div class="col-md-4">
            <label class="form-label">Giờ kết thúc (tự tính)</label>
            <input id="end_clock_preview" type="text" class="form-control" value="{{ optional($show->end_time)->format('H:i') }}" readonly>
        </div>
        <div class="col-md-4">
            <label class="form-label">Giờ kết thúc (tự tính)</label>
            <input id="end_clock_preview" type="text" class="form-control" value="{{ optional($show->end_time)->format('H:i') }}" readonly>
        </div>

        <div class="col-md-4">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select" required>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $show->status ?? 'ON_SALE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Quy tắc giá động</h3>
    <p class="section-description">Giá vé theo suất sẽ được sinh tự động từ hồ sơ giá đã chọn, bao gồm ghế thường/VIP/couple, người lớn/HSSV/trẻ em, suất tối, cuối tuần và các rule ngày đặc biệt.</p>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="soft-card h-100 p-3">
                <div class="fw-semibold mb-1">Theo ngày trong tuần</div>
                <div class="text-muted small">Rule có thể áp cho Thứ 2 → Chủ nhật riêng biệt.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="soft-card h-100 p-3">
                <div class="fw-semibold mb-1">Theo khung giờ</div>
                <div class="text-muted small">Hỗ trợ suất sáng rẻ, suất tối đắt hơn.</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="soft-card h-100 p-3">
                <div class="fw-semibold mb-1">Theo ghế & đối tượng vé</div>
                <div class="text-muted small">Từng loại ghế và ticket type sẽ có giá riêng.</div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4 flex-wrap">
    <button class="btn btn-primary" type="submit">
        <i class="bi bi-check2-circle me-1"></i> {{ $isEdit ? 'Lưu thay đổi' : 'Tạo suất chiếu' }}
    </button>
    <a href="{{ route('admin.shows.index') }}" class="btn btn-light-soft">
        <i class="bi bi-arrow-left me-1"></i> Quay lại danh sách
    </a>
</div>

@push('scripts')
<script>
    (function () {
        const movieSelect = document.getElementById('movie_id');
        const startClock = document.getElementById('start_clock');
        const preview = document.getElementById('end_clock_preview');

        const updateEndTime = () => {
            const option = movieSelect?.selectedOptions?.[0];
            const duration = Number(option?.dataset?.duration || 0);
            const startValue = startClock?.value;
            if (!duration || !startValue) {
                preview.value = '';
                return;
            }

            const [h, m] = startValue.split(':').map(Number);
            const total = h * 60 + m + duration;
            const endH = String(Math.floor((total % (24 * 60)) / 60)).padStart(2, '0');
            const endM = String(total % 60).padStart(2, '0');
            preview.value = `${endH}:${endM}`;
        };

        movieSelect?.addEventListener('change', updateEndTime);
        startClock?.addEventListener('input', updateEndTime);
        updateEndTime();
    })();
</script>
@endpush
