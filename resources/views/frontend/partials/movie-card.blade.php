@php
  $badgeLabel = $badge ?? 'Hot';
  $showtimeData = $showtimesByMovie[$movie->id] ?? ['count' => 0, 'groups' => [], 'first_show_at' => null];
  $showtimeCount = (int) ($showtimeData['count'] ?? 0);
  $firstShowAt = $showtimeData['first_show_at'] ?? null;
  $hasUpcomingShows = $showtimeCount > 0;
  $isComingSoon = ($badge ?? null) === 'Coming';
  $ctaLabel = $hasUpcomingShows ? 'Mua vé' : ($isComingSoon ? 'Sắp mở bán' : 'Xem lịch chiếu');
@endphp
<div class="movie-card movie-card--interactive h-100">

  <div class="movie-poster-wrap">
    <span class="movie-badge">{{ $badgeLabel }}</span>
    @if($movie->poster_url)
      <img src="{{ $movie->poster_url }}" class="movie-poster" alt="{{ $movie->title }}">
    @else
      <div class="poster-fallback">
        <span>{{ \Illuminate\Support\Str::limit($movie->title, 28) }}</span>
      </div>
    @endif
  </div>


  <div class="movie-card__body">
    <div class="movie-title-row">
      <div>
        <span class="movie-kicker">Phim</span>
        <h3>{{ $movie->title }}</h3>
      </div>
      <span class="age-badge">{{ $movie->contentRating?->code ?? 'P' }}</span>
    </div>

    <p class="movie-meta">{{ $movie->genres->pluck('name')->take(3)->implode(' · ') ?: 'Đang cập nhật thể loại' }}</p>

    <div class="movie-specs movie-specs--compact">
      <span><i class="bi bi-clock"></i>{{ $movie->duration_minutes }} phút</span>
      <span><i class="bi bi-calendar3"></i>{{ optional($movie->release_date)->format('d/m/Y') ?: 'N/A' }}</span>
    </div>

    <div class="movie-card__summary">
      <div class="movie-summary-chip">
        <small>Suất chiếu</small>
        <strong>{{ $showtimeCount > 0 ? $showtimeCount . ' suất sắp tới' : 'Chưa có suất chiếu' }}</strong>
      </div>
      <div class="movie-summary-chip">
        <small>Khởi chiếu gần nhất</small>
        <strong>{{ $firstShowAt ?: 'Đang cập nhật' }}</strong>
      </div>
    </div>

    <div class="movie-card__actions">
      @if($hasUpcomingShows)
        <button type="button"
                class="btn btn-card-action"
                data-bs-toggle="modal"
                data-bs-target="#movieShowtimesModal-{{ $movie->id }}">
          <i class="bi bi-ticket-detailed me-2"></i>{{ $ctaLabel }}
        </button>
      @else
        <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-card-action">
          <i class="bi bi-film me-2"></i>{{ $ctaLabel }}
        </a>
      @endif
    </div>
  <div class="movie-card__body">
    <div class="movie-title-row">
      <div>
        <span class="movie-kicker">Phim</span>
        <h3>{{ $movie->title }}</h3>
      </div>
      <span class="age-badge">{{ $movie->contentRating?->code ?? 'P' }}</span>
    </div>

    <p class="movie-meta">{{ $movie->genres->pluck('name')->take(3)->implode(' · ') ?: 'Đang cập nhật thể loại' }}</p>

    <div class="movie-specs movie-specs--compact">
      <span><i class="bi bi-clock"></i>{{ $movie->duration_minutes }} phút</span>
      <span><i class="bi bi-calendar3"></i>{{ optional($movie->release_date)->format('d/m/Y') ?: 'N/A' }}</span>
    </div>

    <div class="movie-card__summary">
      <div class="movie-summary-chip">
        <small>Suất chiếu</small>
        <strong>{{ $showtimeCount > 0 ? $showtimeCount . ' suất sắp tới' : 'Chưa có suất chiếu' }}</strong>
      </div>
      <div class="movie-summary-chip">
        <small>Khởi chiếu gần nhất</small>
        <strong>{{ $firstShowAt ?: 'Đang cập nhật' }}</strong>
      </div>
    </div>

    <div class="movie-card__actions">
      @if($hasUpcomingShows)
        <button type="button"
                class="btn btn-card-action"
                data-bs-toggle="modal"
                data-bs-target="#movieShowtimesModal-{{ $movie->id }}">
          <i class="bi bi-ticket-detailed me-2"></i>{{ $ctaLabel }}
        </button>
      @else
        <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-card-action">
          <i class="bi bi-film me-2"></i>{{ $ctaLabel }}
        </a>
      @endif
    </div>

  </div>
</div>
