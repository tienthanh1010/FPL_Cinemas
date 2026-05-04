@php
    $isEdit = $show->exists;
    $selectedMovieId = old('movie_id', $selectedMovieId ?? null);
    $selectedMovieVersionId = old('movie_version_id', $selectedMovieVersionId ?? $show->movie_version_id);
    $dateValue = old('show_date', optional($show->start_time)->format('Y-m-d'));
    $dateFromValue = old('show_date_from', $dateValue);
    $dateToValue = old('show_date_to', $dateFromValue);
    $timeValue = old('start_clock', optional($show->start_time)->format('H:i'));
    $existingShows = collect($existingShows ?? [])->values();
@endphp

@push('styles')
<style>
    .show-version-hint,
    .room-schedule-empty {
        color: #6b7280;
        font-size: .875rem;
    }

    .room-schedule-panel {
        border: 1px solid rgba(148, 163, 184, .28);
        border-radius: 18px;
        background: rgba(248, 250, 252, .72);
        padding: 1rem;
    }

    .room-schedule-list {
        display: grid;
        gap: .65rem;
        margin-top: .85rem;
    }

    .room-schedule-item,
    .schedule-preview-line {
        display: flex;
        justify-content: space-between;
        gap: .85rem;
        align-items: center;
        border-radius: 14px;
        padding: .72rem .85rem;
        background: #fff;
        border: 1px solid rgba(148, 163, 184, .28);
    }

    .room-schedule-time,
    .schedule-preview-time {
        font-weight: 800;
        white-space: nowrap;
    }

    .room-schedule-movie,
    .schedule-preview-title {
        min-width: 0;
        color: #374151;
        font-size: .9rem;
    }

    .schedule-preview-line.is-conflict,
    .room-schedule-item.is-conflict {
        border-color: #ef4444;
        background: #fef2f2;
        color: #991b1b;
    }

    .schedule-preview-line.is-conflict .room-schedule-movie,
    .schedule-preview-line.is-conflict .schedule-preview-title,
    .room-schedule-item.is-conflict .room-schedule-movie {
        color: #991b1b;
    }

    .show-conflict-alert {
        display: none;
        margin-top: .75rem;
        border-radius: 14px;
        border: 1px solid #fecaca;
        background: #fef2f2;
        color: #991b1b;
        padding: .75rem .85rem;
        font-weight: 700;
    }

    .show-conflict-alert.is-visible {
        display: block;
    }
</style>
@endpush

<div class="section-card">
    <h3>Thông tin cơ bản của suất chiếu</h3>

    <div class="row g-3">
        <div class="col-lg-4">
            <label class="form-label">Phim *</label>
            <select id="movie_id" name="movie_id" class="form-select @error('movie_id') is-invalid @enderror" required>
                <option value="">-- Chọn phim --</option>
                @foreach($movies as $movie)
                    <option value="{{ $movie->id }}" data-duration="{{ $movie->duration_minutes }}" @selected((string) $selectedMovieId === (string) $movie->id)>
                        {{ $movie->title }} ({{ $movie->duration_minutes }} phút)
                    </option>
                @endforeach
            </select>
            @error('movie_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-4">
            <label class="form-label">Phiên bản phim *</label>
            <select id="movie_version_id" name="movie_version_id" class="form-select @error('movie_version_id') is-invalid @enderror" required>
                <option value="">-- Chọn phiên bản --</option>
                @foreach($movies as $movie)
                    @foreach($movie->versions as $version)
                        <option value="{{ $version->id }}"
                                data-movie-id="{{ $movie->id }}"
                                data-duration="{{ $movie->duration_minutes }}"
                                data-format="{{ $version->format }}"
                                @selected((string) $selectedMovieVersionId === (string) $version->id)>
                            {{ $version->format }} · Âm thanh {{ $version->audio_language }}{{ $version->subtitle_language ? ' · Phụ đề ' . $version->subtitle_language : '' }}
                        </option>
                    @endforeach
                @endforeach
            </select>
            @error('movie_version_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            <div class="form-text show-version-hint">Chọn đúng bản 2D/3D trước khi tạo suất chiếu.</div>
        </div>

        <div class="col-lg-4">
            <label class="form-label">Phòng chiếu *</label>
            <select id="auditorium_id" name="auditorium_id" class="form-select @error('auditorium_id') is-invalid @enderror" required>
                <option value="">-- Chọn phòng --</option>
                @foreach($auditoriums as $auditorium)
                    <option value="{{ $auditorium->id }}" @selected((string) old('auditorium_id', $show->auditorium_id) === (string) $auditorium->id)>
                        {{ $auditorium->name }} ({{ $auditorium->auditorium_code }})
                    </option>
                @endforeach
            </select>
            @error('auditorium_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-lg-6">
            <label class="form-label">Giá Vé *</label>
            <select name="pricing_profile_id" class="form-select @error('pricing_profile_id') is-invalid @enderror" required>
                <option value="">-- Chọn giá vé --</option>
                @foreach($profiles as $profile)
                    <option value="{{ $profile->id }}" @selected((string) old('pricing_profile_id', $show->pricing_profile_id) === (string) $profile->id)>
                        {{ $profile->name }} ({{ $profile->code }})
                    </option>
                @endforeach
            </select>
            @error('pricing_profile_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            {{-- <div class="form-text">Giá Vé chỉ dùng để sinh giá cho khách mua mới. Vé đã tạo trước đó không bị đổi giá theo.</div> --}}
        </div>

        @if($isEdit)
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Ngày chiếu *</label>
                <input id="show_date" type="date" name="show_date" value="{{ $dateValue }}" class="form-control @error('show_date') is-invalid @enderror" required>
                @error('show_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        @else
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Từ ngày *</label>
                <input id="show_date" type="date" name="show_date_from" value="{{ $dateFromValue }}" class="form-control @error('show_date_from') is-invalid @enderror" required>
                @error('show_date_from')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Đến ngày *</label>
                <input id="show_date_to" type="date" name="show_date_to" value="{{ $dateToValue }}" class="form-control @error('show_date_to') is-invalid @enderror" required>
                @error('show_date_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <div class="form-text">Mỗi ngày trong khoảng này sẽ được sinh lịch giống nhau.</div>
            </div>
        @endif

        <div class="col-md-4 col-lg-3">
            <label class="form-label">Giờ bắt đầu *</label>
            <input id="start_clock" type="time" name="start_clock" value="{{ $timeValue }}" class="form-control @error('start_clock') is-invalid @enderror" required>
            @error('start_clock')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-4 col-lg-3">
            <label class="form-label">Giờ kết thúc (tự tính)</label>
            <input id="end_clock_preview" type="text" class="form-control" value="{{ optional($show->end_time)->format('H:i') }}" readonly>
        </div>

        <div class="col-md-4 col-lg-3">
            <label class="form-label">Trạng thái *</label>
            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('status', $show->status ?? 'ON_SALE') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

@if(! $isEdit)
<div class="section-card">
    <h3>Tạo nhiều suất chiếu trong nhiều ngày</h3>

    <div class="row g-3 align-items-stretch">
        <div class="col-lg-4">
            <label class="form-label">Số suất mỗi ngày *</label>
            <input id="show_count" type="number" name="show_count" min="1" max="12" value="{{ old('show_count', 1) }}" class="form-control @error('show_count') is-invalid @enderror" required>
            @error('show_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-lg-4">
            <label class="form-label">Phút nghỉ giữa các suất</label>
            <input id="break_minutes" type="number" name="break_minutes" min="0" max="180" value="{{ old('break_minutes', 20) }}" class="form-control @error('break_minutes') is-invalid @enderror">
            @error('break_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            {{-- <div class="form-text">Gộp thời gian dọn phòng, trailer, quảng cáo.</div> --}}
        </div>
        <div class="col-lg-4">
            <div class="soft-card h-100 p-3">
                <div class="fw-semibold mb-1">Xem nhanh lịch sinh tự động theo ngày</div>
                <div class="small text-muted" id="schedule_preview">Chọn phim, phòng, khoảng ngày và giờ bắt đầu để xem lịch.</div>
                <div class="show-conflict-alert" id="schedule_conflict_alert">Có suất chiếu bị trùng phòng/giờ trong một hoặc nhiều ngày. Các dòng trùng đã được bôi đỏ.</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="section-card">
    <h3>Suất chiếu hiện đang có trong phòng</h3>
    <p class="section-description">Chọn phòng và khoảng ngày để xem các khung giờ đã có. Nếu lịch mới bị trùng phòng/giờ, hệ thống sẽ bôi đỏ để bạn tránh hoặc sửa trước khi lưu.</p>
    <div class="room-schedule-panel">
        <div class="fw-semibold" id="room_schedule_title">Lịch phòng theo ngày</div>
        <div id="room_schedule_list" class="room-schedule-list">
            <div class="room-schedule-empty">Chọn phòng chiếu và ngày chiếu để xem lịch hiện có.</div>
        </div>
    </div>
</div>

<div class="section-card">
    <h3>Giá vé theo suất</h3>
    <p class="section-description">Giá vé theo suất được sinh từ giá vé cố định theo ghế và điều chỉnh tăng/giảm theo phòng chiếu.</p>

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
        const versionSelect = document.getElementById('movie_version_id');
        const auditoriumSelect = document.getElementById('auditorium_id');
        const showDate = document.getElementById('show_date');
        const showDateTo = document.getElementById('show_date_to');
        const startClock = document.getElementById('start_clock');
        const preview = document.getElementById('end_clock_preview');
        const countInput = document.getElementById('show_count');
        const breakInput = document.getElementById('break_minutes');
        const schedulePreview = document.getElementById('schedule_preview');
        const conflictAlert = document.getElementById('schedule_conflict_alert');
        const roomScheduleTitle = document.getElementById('room_schedule_title');
        const roomScheduleList = document.getElementById('room_schedule_list');
        const existingShows = @json($existingShows);

        const toClock = (totalMinutes) => {
            const normalized = ((totalMinutes % (24 * 60)) + (24 * 60)) % (24 * 60);
            const hours = Math.floor(normalized / 60);
            const minutes = normalized % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        };

        const toMinutes = (clock) => {
            if (!clock || !clock.includes(':')) return null;
            const [h, m] = clock.split(':').map(Number);
            if (Number.isNaN(h) || Number.isNaN(m)) return null;
            return h * 60 + m;
        };

        const escapeHtml = (value) => String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

        const dateToText = (date) => {
            if (!date) return '';
            const [year, month, day] = date.split('-');
            return `${day}/${month}/${year}`;
        };

        const addDays = (date, days) => {
            const clone = new Date(date.getTime());
            clone.setDate(clone.getDate() + days);
            return clone;
        };

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        };

        const selectedDates = () => {
            const fromValue = showDate?.value || '';
            const toValue = showDateTo?.value || fromValue;
            if (!fromValue) return [];

            const from = new Date(`${fromValue}T00:00:00`);
            const to = new Date(`${toValue}T00:00:00`);
            if (Number.isNaN(from.getTime()) || Number.isNaN(to.getTime()) || to < from) {
                return [fromValue];
            }

            const dates = [];
            let cursor = from;
            while (cursor <= to && dates.length < 370) {
                dates.push(formatDate(cursor));
                cursor = addDays(cursor, 1);
            }
            return dates;
        };

        const versionOptions = Array.from(versionSelect?.options || []).filter((option) => option.value);

        const filterVersions = () => {
            const movieId = movieSelect?.value || '';
            let firstVisibleValue = '';
            let selectedStillVisible = false;

            versionOptions.forEach((option) => {
                const isVisible = option.dataset.movieId === movieId;
                option.hidden = !isVisible;
                option.disabled = !isVisible;

                if (isVisible && !firstVisibleValue) firstVisibleValue = option.value;
                if (isVisible && option.selected) selectedStillVisible = true;
            });

            if (!movieId) {
                versionSelect.value = '';
                return;
            }

            if (!selectedStillVisible) {
                versionSelect.value = firstVisibleValue;
            }
        };

        const selectedDuration = () => {
            const versionOption = versionSelect?.selectedOptions?.[0];
            const movieOption = movieSelect?.selectedOptions?.[0];
            return Number(versionOption?.dataset?.duration || movieOption?.dataset?.duration || 0);
        };

        const roomShowsForSelectedDates = () => {
            const roomId = Number(auditoriumSelect?.value || 0);
            const dateSet = new Set(selectedDates());
            if (!roomId || !dateSet.size) return [];

            return existingShows
                .filter((show) => Number(show.auditorium_id) === roomId && dateSet.has(show.date))
                .sort((a, b) => String(a.date + a.start_clock).localeCompare(String(b.date + b.start_clock)));
        };

        const overlaps = (startA, endA, startB, endB) => startA < endB && endA > startB;

        const plannedSlots = () => {
            const duration = selectedDuration();
            const startValue = startClock?.value;
            const startMinutes = toMinutes(startValue);
            const dates = selectedDates();

            if (!duration || startMinutes === null || !dates.length) return [];

            const count = Math.max(1, Number(countInput?.value || 1));
            const gap = Math.max(0, Number(breakInput?.value || 0));
            const slots = [];

            dates.forEach((date) => {
                let cursor = startMinutes;
                for (let i = 0; i < count; i++) {
                    const slotEnd = cursor + duration;
                    slots.push({date, index: i + 1, start: cursor, end: slotEnd});
                    cursor = slotEnd + gap;
                }
            });

            return slots;
        };

        const conflictForSlot = (slot, roomShows) => {
            return roomShows.find((show) => {
                if (show.date !== slot.date) return false;
                const showStart = toMinutes(show.start_clock);
                const showEnd = toMinutes(show.end_clock);
                return showStart !== null && showEnd !== null && overlaps(slot.start, slot.end, showStart, showEnd);
            });
        };

        const renderRoomSchedule = () => {
            if (!roomScheduleList) return;

            const roomId = auditoriumSelect?.value || '';
            const dates = selectedDates();
            const roomText = auditoriumSelect?.selectedOptions?.[0]?.textContent?.trim() || 'phòng đã chọn';

            if (roomScheduleTitle) {
                roomScheduleTitle.textContent = roomId && dates.length
                    ? `Lịch ${roomText} từ ${dateToText(dates[0])}${dates.length > 1 ? ' đến ' + dateToText(dates[dates.length - 1]) : ''}`
                    : 'Lịch phòng theo ngày';
            }

            if (!roomId || !dates.length) {
                roomScheduleList.innerHTML = '<div class="room-schedule-empty">Chọn phòng chiếu và khoảng ngày để xem lịch hiện có.</div>';
                return;
            }

            const roomShows = roomShowsForSelectedDates();
            const slots = plannedSlots();

            if (!roomShows.length) {
                roomScheduleList.innerHTML = '<div class="room-schedule-empty">Khoảng ngày này phòng chưa có suất chiếu nào.</div>';
                return;
            }

            let currentDate = '';
            const html = [];
            roomShows.forEach((show) => {
                if (show.date !== currentDate) {
                    currentDate = show.date;
                    html.push(`<div class="fw-bold mt-2">${escapeHtml(dateToText(show.date))}</div>`);
                }

                const showStart = toMinutes(show.start_clock);
                const showEnd = toMinutes(show.end_clock);
                const hasConflict = slots.some((slot) => slot.date === show.date && showStart !== null && showEnd !== null && overlaps(slot.start, slot.end, showStart, showEnd));

                html.push(`
                    <div class="room-schedule-item ${hasConflict ? 'is-conflict' : ''}">
                        <div>
                            <div class="room-schedule-time">${escapeHtml(show.start_clock)} - ${escapeHtml(show.end_clock)}</div>
                            <div class="room-schedule-movie">${escapeHtml(show.movie_title)} · ${escapeHtml(show.format)}</div>
                        </div>
                        <span class="badge ${hasConflict ? 'badge-soft-danger' : 'badge-soft-secondary'}">${hasConflict ? 'Bị trùng' : escapeHtml(show.status)}</span>
                    </div>
                `);
            });

            roomScheduleList.innerHTML = html.join('');
        };

        const updateEndTime = () => {
            filterVersions();

            if (showDateTo && showDate?.value && (!showDateTo.value || showDateTo.value < showDate.value)) {
                showDateTo.value = showDate.value;
            }

            const duration = selectedDuration();
            const startValue = startClock?.value;
            const startMinutes = toMinutes(startValue);

            if (!duration || startMinutes === null) {
                if (preview) preview.value = '';
                if (schedulePreview) schedulePreview.textContent = 'Chọn phim, phiên bản, phòng, khoảng ngày và giờ bắt đầu để xem lịch.';
                conflictAlert?.classList.remove('is-visible');
                startClock?.classList.remove('is-invalid');
                renderRoomSchedule();
                return;
            }

            const endMinutes = startMinutes + duration;
            if (preview) preview.value = toClock(endMinutes);

            const slots = plannedSlots();
            const roomShows = roomShowsForSelectedDates();
            let hasConflict = false;
            const grouped = new Map();

            slots.forEach((slot) => {
                const conflict = conflictForSlot(slot, roomShows);
                const overBusinessHour = slot.end > 23 * 60;
                const isConflict = Boolean(conflict || overBusinessHour);
                hasConflict = hasConflict || isConflict;
                const note = overBusinessHour
                    ? 'Vượt quá 23:00'
                    : (conflict ? `Trùng với ${conflict.start_clock} - ${conflict.end_clock} (${conflict.movie_title} · ${conflict.format})` : 'Hợp lệ');

                if (!grouped.has(slot.date)) grouped.set(slot.date, []);
                grouped.get(slot.date).push(`
                    <div class="schedule-preview-line ${isConflict ? 'is-conflict' : ''}">
                        <div>
                            <div class="schedule-preview-time">Suất ${slot.index}: ${toClock(slot.start)} - ${toClock(slot.end)}</div>
                            <div class="schedule-preview-title">${escapeHtml(note)}</div>
                        </div>
                    </div>
                `);
            });

            if (schedulePreview) {
                const parts = [];
                grouped.forEach((lines, date) => {
                    parts.push(`<div class="fw-bold mt-2 mb-1">${escapeHtml(dateToText(date))}</div>`);
                    parts.push(lines.join(''));
                });
                schedulePreview.innerHTML = parts.join('') || 'Chọn phim, phòng, khoảng ngày và giờ bắt đầu để xem lịch.';
            }

            conflictAlert?.classList.toggle('is-visible', hasConflict);
            startClock?.classList.toggle('is-invalid', hasConflict);
            renderRoomSchedule();
        };

        movieSelect?.addEventListener('change', updateEndTime);
        versionSelect?.addEventListener('change', updateEndTime);
        auditoriumSelect?.addEventListener('change', updateEndTime);
        showDate?.addEventListener('input', updateEndTime);
        showDateTo?.addEventListener('input', updateEndTime);
        startClock?.addEventListener('input', updateEndTime);
        countInput?.addEventListener('input', updateEndTime);
        breakInput?.addEventListener('input', updateEndTime);
        updateEndTime();
    })();
</script>
@endpush
