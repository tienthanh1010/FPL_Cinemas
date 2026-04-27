@extends('frontend.layout')

@section('title', $movie->title . ' | Lịch chiếu')

@push('styles')
<style>
  .schedule-page-hero {
    display: grid;
    grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    gap: 1.5rem;
    align-items: stretch;
  }
  .schedule-page-hero__poster {
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.08);
    min-height: 380px;
  }
  .schedule-page-hero__poster img {
    width: 100%; height: 100%; object-fit: cover; display: block;
  }
  .schedule-page-hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1rem;
  }
  .schedule-page-hero__copy h1 { margin: 0; }
  .schedule-page-hero__copy p { margin: 0; color: var(--muted); }

  .schedule-board {
    --schedule-surface: linear-gradient(180deg, rgba(8, 15, 30, .96) 0%, rgba(10, 20, 38, .94) 100%);
    --schedule-text: #edf3ff;
    --schedule-muted: #9fb0ca;
    --schedule-line: rgba(255,255,255,.10);
    --schedule-card: rgba(255,255,255,.06);
    --schedule-card-strong: linear-gradient(180deg, rgba(26, 72, 137, .42) 0%, rgba(19, 46, 84, .64) 100%);
    --schedule-card-border: rgba(255,255,255,.10);
    --schedule-chip: rgba(255,255,255,.08);
    --schedule-chip-text: #dbe7ff;
    background: var(--schedule-surface);
    color: var(--schedule-text);
    border-radius: 26px;
    padding: 1.5rem;
    border: 1px solid var(--schedule-line);
    box-shadow: 0 18px 46px rgba(15,23,42,.18);
  }
  html[data-theme='light'] .schedule-board {
    --schedule-surface: #f6f6f6;
    --schedule-text: #1f2937;
    --schedule-muted: #475569;
    --schedule-line: rgba(17,24,39,.10);
    --schedule-card: #d7e3f4;
    --schedule-card-strong: linear-gradient(180deg, #e9f1ff 0%, #cfe0ff 100%);
    --schedule-card-border: rgba(15,90,166,.18);
    --schedule-chip: rgba(15,23,42,.08);
    --schedule-chip-text: #334155;
    box-shadow: 0 18px 46px rgba(15,23,42,.08);
  }
  .schedule-board__heading {
    text-align: center;
    font-size: clamp(1.8rem, 3vw, 2.7rem);
    font-weight: 800;
    margin: .4rem 0 1.5rem;
    color: var(--schedule-text);
  }
  .schedule-board .nav-tabs {
    border-bottom: 1px solid var(--schedule-line);
    gap: .8rem;
    flex-wrap: nowrap;
    overflow: auto;
    padding-bottom: .2rem;
  }
  .schedule-board .nav-tabs .nav-link {
    border: 0;
    background: transparent;
    color: var(--schedule-text);
    padding: .35rem .4rem .85rem;
    border-radius: 0;
    border-bottom: 3px solid transparent;
    display: inline-flex;
    align-items: flex-end;
    gap: .15rem;
    min-width: 138px;
  }
  .schedule-board .nav-tabs .nav-link.active {
    color: var(--secondary);
    background: transparent;
    border-color: var(--secondary);
  }
  .schedule-board .nav-tabs .nav-link:hover { border-color: color-mix(in srgb, var(--secondary) 45%, transparent); }
  .schedule-date-number { font-size: 3rem; font-weight: 900; line-height: .9; }
  .schedule-date-meta { font-size: 1.1rem; font-weight: 700; line-height: 1.1; margin-bottom: .2rem; }

  .schedule-pane { padding-top: 2rem; }
  .schedule-format-group + .schedule-format-group { margin-top: 1.75rem; }
  .schedule-format-title {
    font-size: 2rem;
    font-weight: 900;
    color: var(--schedule-text);
    margin-bottom: 1rem;
    text-transform: uppercase;
  }
  .schedule-show-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
  }
  .schedule-show-card {
    width: min(100%, 180px);
    background: var(--schedule-card);
    border-radius: 18px;
    padding: 1rem;
    text-align: center;
    box-shadow: inset 0 -6px 0 rgba(15,23,42,.10);
    border: 1px solid var(--schedule-card-border);
  }
  .schedule-show-card.is-on-sale {
    background: var(--schedule-card-strong);
    border-color: var(--schedule-card-border);
  }
  .schedule-show-card__time {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    color: var(--schedule-text);
  }
  .schedule-show-card__date {
    margin-top: .4rem;
    font-size: 1rem;
    font-weight: 700;
    color: var(--schedule-muted);
  }
  .schedule-show-card__meta {
    margin-top: .65rem;
    font-size: .9rem;
    color: var(--schedule-muted);
    min-height: 2.6em;
  }
  .schedule-show-card__status {
    display: inline-flex;
    margin-top: .7rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: var(--schedule-chip);
    font-size: .78rem;
    font-weight: 800;
    color: var(--schedule-muted);
  }
  .schedule-show-card__footer { margin-top: .9rem; }
  .schedule-show-card__button {
    display: inline-flex;
    width: 100%;
    justify-content: center;
    align-items: center;
    padding: .75rem .8rem;
    border-radius: 12px;
    font-weight: 800;
    background: var(--primary);
    color: var(--on-primary, #fff);
  }
  .schedule-show-card__button:hover { color: var(--on-primary, #fff); background: var(--primary-strong); }
  .schedule-show-card__button.is-disabled {
    background: var(--schedule-chip);
    color: var(--schedule-muted);
    pointer-events: none;
  }

  @media (max-width: 991.98px) {
    .schedule-page-hero { grid-template-columns: 1fr; }
    .schedule-page-hero__poster { max-width: 280px; }
  }
</style>
@endpush

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel mb-4 mb-xl-5">
        <div class="schedule-page-hero">
          <div class="schedule-page-hero__poster">
            @if($movie->poster_url)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
            @else
              <div class="poster-fallback poster-fallback--showtime"><span>{{ $movie->title }}</span></div>
            @endif
          </div>
          <div class="schedule-page-hero__copy">
            <span class="section-eyebrow">Lịch chiếu chi tiết</span>
            <h1>{{ $movie->title }}</h1>
            <p>{{ $movie->synopsis ? \Illuminate\Support\Str::limit($movie->synopsis, 220) : 'Chọn ngày và suất chiếu phù hợp để chuyển sang trang đặt vé riêng, nơi bạn có thể chọn ghế trực quan trên sơ đồ lớn.' }}</p>
            <div class="hero-meta hero-meta--compact">
              <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
              <span><i class="bi bi-calendar-event me-2"></i>{{ optional($movie->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
              <span><i class="bi bi-tags me-2"></i>{{ $movie->genres->pluck('name')->implode(' · ') ?: 'Chưa gán thể loại' }}</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-arrow-left me-2"></i>Trở lại trang chủ</a>
            </div>
          </div>
        </div>
      </div>

      <div class="schedule-board">
        <div class="section-eyebrow text-uppercase mb-2">Lịch chiếu của phim</div>
        <div class="schedule-board__heading">{{ $movie->title }}</div>

        @if($showsByDate->isNotEmpty())
          <ul class="nav nav-tabs" id="showDateTab" role="tablist">
            @foreach($showsByDate as $date => $dateShows)
              @php($dateCarbon = \Carbon\Carbon::parse($date))
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="show-date-tab-{{ $loop->index }}" data-bs-toggle="tab" data-bs-target="#show-date-pane-{{ $loop->index }}" type="button" role="tab">
                  <span class="schedule-date-number">{{ $dateCarbon->format('d') }}</span>
                  <span class="schedule-date-meta">/{{ $dateCarbon->format('m') }} - {{ mb_strtoupper($dateCarbon->translatedFormat('D')) }}</span>
                </button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content">
            @foreach($showsByDate as $date => $dateShows)
              @php($showsByFormat = $dateShows->groupBy(fn ($show) => $show->movieVersion?->format ?: '2D'))
              <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }} schedule-pane" id="show-date-pane-{{ $loop->index }}" role="tabpanel">
                @foreach($showsByFormat as $format => $formatShows)
                  <div class="schedule-format-group">
                    <div class="schedule-format-title">{{ $format }}</div>
                    <div class="schedule-show-grid">
                      @foreach($formatShows as $show)
                        <div class="schedule-show-card {{ $show->isOnSaleNow() ? 'is-on-sale' : '' }}">
                          <div class="schedule-show-card__time">{{ $show->start_time->format('H:i') }}</div>
                          <div class="schedule-show-card__date">{{ $show->start_time->format('d/m') }}</div>
                          <div class="schedule-show-card__meta">{{ $show->auditorium->name }} · {{ $show->auditorium->cinema->name }}</div>
                          <div class="schedule-show-card__status">{{ $show->frontendStatusLabel() }}</div>
                          <div class="schedule-show-card__footer">
                            @if($show->isOnSaleNow())
                              <a href="{{ route('shows.book', $show) }}" class="schedule-show-card__button">Đặt vé</a>
                            @else
                              <span class="schedule-show-card__button is-disabled">{{ $show->frontendStatusLabel() }}</span>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
            @endforeach
          </div>
        @else
          <div class="empty-panel">Chưa có suất chiếu nào cho phim này.</div>
        @endif
      </div>
    </div>
  </section>
@endsection
