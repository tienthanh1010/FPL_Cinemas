@php
  $showtimeData = $showtimesByMovie[$movie->id] ?? ['count' => 0, 'groups' => []];
  $groups = collect($showtimeData['groups'] ?? [])->values();
  $firstCinema = collect($groups)->pluck('shows')->flatten(1)->pluck('cinema')->filter()->first();
@endphp
<div class="modal fade movie-showtime-modal" id="movieShowtimesModal-{{ $movie->id }}" tabindex="-1" aria-labelledby="movieShowtimesModalLabel-{{ $movie->id }}" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
    <div class="modal-content movie-showtime-modal__content schedule-modal__content">
      <div class="modal-header schedule-modal__header border-0">
        <div>
          <h5 class="modal-title" id="movieShowtimesModalLabel-{{ $movie->id }}">LỊCH CHIẾU - {{ $movie->title }}</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
      </div>

      <div class="modal-body schedule-modal__body">
        <div class="schedule-modal__cinema">{{ $firstCinema ? 'Rạp ' . $firstCinema : 'Lịch chiếu tại rạp' }}</div>

        @if($groups->isNotEmpty())
          <ul class="nav schedule-date-tabs" id="movieShowtimeTab-{{ $movie->id }}" role="tablist">
            @foreach($groups as $index => $group)
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                        id="movie-showtime-tab-{{ $movie->id }}-{{ $index }}"
                        data-bs-toggle="tab"
                        data-bs-target="#movie-showtime-pane-{{ $movie->id }}-{{ $index }}"
                        type="button"
                        role="tab">
                  <span class="schedule-date-tabs__day">{{ $group['day_number'] ?? \Carbon\Carbon::parse($group['date_key'] ?? now())->format('d') }}</span>
                  <span class="schedule-date-tabs__meta">/{{ $group['month_label'] ?? \Carbon\Carbon::parse($group['date_key'] ?? now())->format('m') }} - {{ $group['weekday_short'] ?? '' }}</span>
                </button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content schedule-date-content" id="movieShowtimeTabContent-{{ $movie->id }}">
            @foreach($groups as $index => $group)
              @php
                $showsByFormat = collect($group['shows'] ?? [])->groupBy(fn ($show) => $show['format'] ?: '2D');
              @endphp
              <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                   id="movie-showtime-pane-{{ $movie->id }}-{{ $index }}"
                   role="tabpanel">
                @foreach($showsByFormat as $format => $formatShows)
                  <section class="schedule-format-block">
                    <h6 class="schedule-format-block__title">{{ $format }}</h6>
                    <div class="schedule-show-grid">
                      @foreach($formatShows as $show)
                        <div class="schedule-show-chip {{ !empty($show['is_on_sale']) ? 'is-on-sale' : 'is-muted' }}">
                          <div class="schedule-show-chip__time">{{ $show['time'] }}</div>
                          <div class="schedule-show-chip__date">{{ $group['day_number'] ?? '' }}/{{ $group['month_label'] ?? '' }}</div>
                          <div class="schedule-show-chip__meta">{{ $show['auditorium'] }}</div>
                          @if(!empty($show['is_on_sale']))
                            <a href="{{ route('shows.book', $show['id']) }}" class="schedule-show-chip__action">Đặt vé</a>
                          @else
                            <a href="{{ route('movies.showtimes', $movie) }}" class="schedule-show-chip__action is-secondary">{{ $show['status_label'] ?? 'Xem lịch' }}</a>
                          @endif
                        </div>
                      @endforeach
                    </div>
                  </section>
                @endforeach
              </div>
            @endforeach
          </div>
        @else
          <div class="empty-panel mt-4">Phim này hiện chưa có suất chiếu khả dụng để đặt vé.</div>
        @endif
      </div>
    </div>
  </div>
</div>
