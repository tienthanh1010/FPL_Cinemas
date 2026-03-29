@php($badgeLabel = $badge ?? 'Hot')
<a href="{{ route('movies.showtimes', $movie) }}" class="movie-card-link h-100">
  <div class="movie-card h-100">
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
        <h3>{{ $movie->title }}</h3>
        <span class="age-badge">{{ $movie->contentRating?->code ?? 'P' }}</span>
      </div>
      <p class="movie-meta">{{ $movie->genres->pluck('name')->take(3)->implode(' · ') ?: 'Đang cập nhật thể loại' }}</p>
      <div class="movie-specs">
        <span><i class="bi bi-clock"></i>{{ $movie->duration_minutes }} phút</span>
        <span><i class="bi bi-calendar3"></i>{{ optional($movie->release_date)->format('d.m.Y') ?: 'N/A' }}</span>
      </div>
      
      <div class="btn btn-card-action">
        <i class="bi bi-ticket-detailed me-2"></i>Mua vé
      </div>
    </div>
  </div>
</a>
