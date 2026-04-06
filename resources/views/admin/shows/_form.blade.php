@php
    $isEdit = $show->exists;
    $selectedMovieId = old('movie_id', $selectedMovieId ?? null);
    $dateValue = old('show_date', optional($show->start_time)->format('Y-m-d'));
    $timeValue = old('start_clock', optional($show->start_time)->format('H:i'));
@endphp

<div class="section-card">
    <h3>Thông tin cơ bản của suất chiếu</h3>
<<<<<<< HEAD
=======
    <p class="section-description">Chọn phim, phòng, ngày chiếu và giờ bắt đầu. Giờ kết thúc sẽ tự tính theo thời lượng phim.</p>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

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
<<<<<<< HEAD
            {{-- <div class="form-text">Hồ sơ giá chỉ dùng để sinh giá cho khách mua mới. Vé đã tạo trước đó không bị đổi giá theo.</div> --}}
=======
            <div class="form-text">Bạn có thể tạo/sửa hồ sơ giá ở menu Hồ sơ giá động.</div>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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

<<<<<<< HEAD
@if(! $isEdit)
<div class="section-card">
    <h3>Tạo nhiều suất chiếu liên tiếp</h3>

    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label">Số suất cần tạo *</label>
            <input id="show_count" type="number" name="show_count" min="1" max="12" value="{{ old('show_count', 1) }}" class="form-control" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Phút nghỉ giữa các suất</label>
            <input id="break_minutes" type="number" name="break_minutes" min="0" max="180" value="{{ old('break_minutes', 20) }}" class="form-control">
            {{-- <div class="form-text">Gộp thời gian dọn phòng, trailer, quảng cáo.</div> --}}
        </div>
        <div class="col-md-4">
            <div class="soft-card h-100 p-3">
                <div class="fw-semibold mb-1">Xem nhanh lịch sinh tự động</div>
                <div class="small text-muted" id="schedule_preview">Chọn phim, giờ bắt đầu và số suất để xem lịch.</div>
=======
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
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
@endif

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
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

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
<<<<<<< HEAD
        const countInput = document.getElementById('show_count');
        const breakInput = document.getElementById('break_minutes');
        const schedulePreview = document.getElementById('schedule_preview');

        const toClock = (totalMinutes) => {
            const hours = Math.floor(totalMinutes / 60);
            const minutes = totalMinutes % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        };
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

        const updateEndTime = () => {
            const option = movieSelect?.selectedOptions?.[0];
            const duration = Number(option?.dataset?.duration || 0);
            const startValue = startClock?.value;
            if (!duration || !startValue) {
<<<<<<< HEAD
                if (preview) preview.value = '';
                if (schedulePreview) schedulePreview.textContent = 'Chọn phim, giờ bắt đầu và số suất để xem lịch.';
=======
                preview.value = '';
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                return;
            }

            const [h, m] = startValue.split(':').map(Number);
<<<<<<< HEAD
            const startMinutes = h * 60 + m;
            const endMinutes = startMinutes + duration;
            if (preview) preview.value = toClock(endMinutes % (24 * 60));

            if (!schedulePreview || !countInput) {
                return;
            }

            const count = Math.max(1, Number(countInput.value || 1));
            const gap = Math.max(0, Number(breakInput?.value || 0));
            const parts = [];
            let cursor = startMinutes;

            for (let i = 0; i < count; i++) {
                const slotEnd = cursor + duration;
                if (slotEnd > 23 * 60) {
                    parts.push(`Suất ${i + 1}: vượt quá 23:00`);
                    break;
                }
                parts.push(`Suất ${i + 1}: ${toClock(cursor)} - ${toClock(slotEnd)}`);
                cursor = slotEnd + gap;
            }

            schedulePreview.innerHTML = parts.join('<br>');
=======
            const total = h * 60 + m + duration;
            const endH = String(Math.floor((total % (24 * 60)) / 60)).padStart(2, '0');
            const endM = String(total % 60).padStart(2, '0');
            preview.value = `${endH}:${endM}`;
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        };

        movieSelect?.addEventListener('change', updateEndTime);
        startClock?.addEventListener('input', updateEndTime);
<<<<<<< HEAD
        countInput?.addEventListener('input', updateEndTime);
        breakInput?.addEventListener('input', updateEndTime);
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        updateEndTime();
    })();
</script>
@endpush
