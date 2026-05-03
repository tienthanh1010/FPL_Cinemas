@php
  $showtimeData = $showtimesByMovie[$movie->id] ?? ['count' => 0, 'groups' => []];
  $groups = collect($showtimeData['groups'] ?? [])->values();
  $firstCinema = collect($groups)->pluck('shows')->flatten(1)->pluck('cinema')->filter()->first();
<<<<<<< HEAD
  $hasLateShow = $groups->pluck('shows')->flatten(1)->contains(fn ($show) => (int) \Carbon\Carbon::createFromFormat('H:i', $show['time'] ?? '00:00')->format('H') >= 22);
@endphp

@once
  @push('styles')
    <style>
      .movie-showtime-modal .modal-dialog {
        max-width: 960px;
      }
      .movie-showtime-modal__content {
        border-radius: 0;
        border: 0;
        background: #f8f8f8;
        box-shadow: 0 30px 60px rgba(15, 23, 42, .18);
      }
      .schedule-modal__header {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(15, 23, 42, .08) !important;
        background: #fff;
      }
      .schedule-modal__header .modal-title {
        font-size: 1rem;
        font-weight: 900;
        letter-spacing: .02em;
        text-transform: uppercase;
        color: #1f2937;
      }
      .schedule-modal__body {
        padding: 1rem 1rem 1.4rem;
        background: #f8f8f8;
      }
      .schedule-modal__cinema {
        text-align: center;
        font-size: clamp(1.8rem, 3vw, 2.4rem);
        font-weight: 900;
        color: #1f2937;
        margin: .4rem 0 1rem;
      }
      .schedule-date-tabs {
        border-bottom: 1px solid rgba(15, 23, 42, .12);
        gap: .6rem;
        flex-wrap: nowrap;
        overflow-x: auto;
        padding-bottom: .1rem;
        margin-bottom: 1rem;
      }
      .schedule-date-tabs .nav-link {
        border: 0;
        background: transparent;
        color: #111827;
        padding: .2rem .5rem .8rem;
        border-radius: 0;
        border-bottom: 4px solid transparent;
        display: inline-flex;
        align-items: flex-end;
        gap: .15rem;
        min-width: 128px;
      }
      .schedule-date-tabs .nav-link.active {
        color: #0b61b0;
        background: transparent;
        border-color: #0b61b0;
      }
      .schedule-date-tabs__day {
        font-size: 3rem;
        font-weight: 900;
        line-height: .86;
      }
      .schedule-date-tabs__meta {
        font-size: 1rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: .26rem;
      }
      .schedule-date-content {
        padding-top: .4rem;
      }
      .schedule-format-block + .schedule-format-block {
        margin-top: 1.4rem;
      }
      .schedule-format-block__title {
        font-size: 2rem;
        font-weight: 900;
        color: #1f2937;
        text-transform: uppercase;
        margin-bottom: .9rem;
      }
      .schedule-show-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
      }
      .schedule-show-chip {
        width: min(100%, 150px);
        background: #e9ecef;
        border-radius: 0;
        padding: .8rem .7rem;
        text-align: center;
        border: 0;
        box-shadow: none;
      }
      .schedule-show-chip.is-on-sale {
        background: #dbe9fb;
      }
      .schedule-show-chip__time {
        font-size: 1.8rem;
        font-weight: 900;
        line-height: 1;
        color: #1f2937;
      }
      .schedule-show-chip__date {
        margin-top: .25rem;
        font-size: .95rem;
        color: #475569;
        font-weight: 700;
      }
      .schedule-show-chip__meta {
        min-height: 2.4em;
        margin-top: .55rem;
        color: #475569;
        font-size: .88rem;
      }
      .schedule-show-chip__action {
        display: inline-flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        margin-top: .7rem;
        padding: .55rem .7rem;
        border-radius: 10px;
        background: #0b61b0;
        color: #fff;
        font-weight: 800;
      }
      .schedule-show-chip__action:hover {
        color: #fff;
        background: #094f90;
      }
      .schedule-show-chip__action.is-secondary {
        background: #d1d5db;
        color: #475569;
      }
      .schedule-show-chip__late-note {
        display: inline-flex;
        margin-top: .55rem;
        padding: .22rem .45rem;
        border-radius: 999px;
        background: rgba(11, 97, 176, .12);
        color: #0b61b0;
        font-size: .72rem;
        font-weight: 800;
      }
      .schedule-modal__legend {
        margin-top: 1rem;
        display: flex;
        align-items: center;
        gap: .55rem;
        color: #475569;
        font-weight: 700;
      }
      .schedule-modal__legend-swatch {
        width: 24px;
        height: 16px;
        background: #dbe9fb;
        border: 1px solid rgba(11, 97, 176, .16);
      }
      @media (max-width: 767.98px) {
        .schedule-modal__cinema {
          font-size: 1.7rem;
        }
        .schedule-date-tabs__day {
          font-size: 2.4rem;
        }
        .schedule-format-block__title {
          font-size: 1.5rem;
        }
      }
    </style>
  @endpush
@endonce

=======
@endphp
>>>>>>> origin/main
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
<<<<<<< HEAD
              <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" id="movie-showtime-pane-{{ $movie->id }}-{{ $index }}" role="tabpanel">
=======
              <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                   id="movie-showtime-pane-{{ $movie->id }}-{{ $index }}"
                   role="tabpanel">
>>>>>>> origin/main
                @foreach($showsByFormat as $format => $formatShows)
                  <section class="schedule-format-block">
                    <h6 class="schedule-format-block__title">{{ $format }}</h6>
                    <div class="schedule-show-grid">
                      @foreach($formatShows as $show)
<<<<<<< HEAD
                        @php($showHour = (int) \Carbon\Carbon::createFromFormat('H:i', $show['time'] ?? '00:00')->format('H'))
=======
>>>>>>> origin/main
                        <div class="schedule-show-chip {{ !empty($show['is_on_sale']) ? 'is-on-sale' : 'is-muted' }}">
                          <div class="schedule-show-chip__time">{{ $show['time'] }}</div>
                          <div class="schedule-show-chip__date">{{ $group['day_number'] ?? '' }}/{{ $group['month_label'] ?? '' }}</div>
                          <div class="schedule-show-chip__meta">{{ $show['auditorium'] }}</div>
<<<<<<< HEAD
                          @if($showHour >= 22)
                            <div class="schedule-show-chip__late-note">Suất chiếu muộn từ 22h00</div>
                          @endif
=======
>>>>>>> origin/main
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
<<<<<<< HEAD

          @if($hasLateShow)
            <div class="schedule-modal__legend">
              <span class="schedule-modal__legend-swatch"></span>
              <span>Suất chiếu muộn từ 22h00</span>
            </div>
          @endif
=======
>>>>>>> origin/main
        @else
          <div class="empty-panel mt-4">Phim này hiện chưa có suất chiếu khả dụng để đặt vé.</div>
        @endif
      </div>
    </div>
  </div>
</div>
