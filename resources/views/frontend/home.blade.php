@extends('frontend.layout')

@section('title', ($appBrand ?? config('app.name', 'FPL Cinemas')) . ' | Trang chủ')

@section('content')
@php
  $brand = $appBrand ?? config('app.name', 'FPL Cinemas');
  $cinemaName = $primaryCinema->name ?? $brand;
  $pointAmount = (int) config('loyalty.amount_per_point', 10000);

  $modalMovies = $heroMovies
    ->concat($nowShowing)
    ->concat($comingSoon)
    ->concat($specialMovies)
    ->unique('id')
    ->values();
@endphp

<section class="hero-section section-space pt-4 pt-lg-5">
  <div class="container-fluid app-container">
    <div class="hero-shell">

      <!-- HERO CAROUSEL -->
      <div id="homeHeroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">

        <!-- indicators -->
        <div class="carousel-indicators hero-indicators">
          @foreach($heroMovies as $index => $hero)
            <button type="button"
              data-bs-target="#homeHeroCarousel"
              data-bs-slide-to="{{ $index }}"
              class="{{ $index === 0 ? 'active' : '' }}">
            </button>
          @endforeach
        </div>

        <!-- slides -->
        <div class="carousel-inner">

          @forelse($heroMovies as $index => $hero)
          @php
            $movie = $hero;
            $title = $movie->title ?? 'Phim';
            $poster = $movie->poster_url ?? 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80';
            $duration = $movie->duration_minutes ?? '—';
            $release = optional($movie->release_date)->format('d.m.Y') ?? 'Đang cập nhật';
            $formats = $movie->versions?->pluck('format')->filter()->unique()->implode(' / ') ?: '2D';
          @endphp

          <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
            <div class="hero-card">

              <div class="hero-card__backdrop" style="background-image:url('{{ $poster }}')"></div>

              <div class="row align-items-center g-4 position-relative">

                <!-- LEFT -->
                <div class="col-lg-7">
                  <div class="hero-copy">

                    <span class="eyebrow">
                      <i class="bi bi-stars me-2"></i>
                      {{ $cinemaName }} · Trải nghiệm điện ảnh nổi bật
                    </span>

                    <h1>{{ $title }}</h1>

                    <p>
                      {{ $movie->synopsis
                        ? \Illuminate\Support\Str::limit($movie->synopsis, 200)
                        : 'Đặt vé nhanh, chọn ghế trực quan, thanh toán rõ ràng và quản lý lịch sử ngay trong tài khoản.' }}
                    </p>

                    <!-- meta -->
                    <div class="hero-meta">
                      <span><i class="bi bi-clock me-1"></i>{{ $duration }} phút</span>
                      <span><i class="bi bi-calendar me-1"></i>{{ $release }}</span>
                      <span><i class="bi bi-badge-hd me-1"></i>{{ $formats }}</span>
                    </div>

                    <!-- genres -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      @forelse($movie->genres?->take(3) ?? [] as $genre)
                        <span class="genre-chip">{{ $genre->name }}</span>
                      @empty
                        <span class="genre-chip">Phim nổi bật</span>
                      @endforelse
                    </div>

                    <!-- actions -->
                    <div class="d-flex flex-wrap gap-3">
                      <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-cinema-primary">
                        <i class="bi bi-ticket-perforated me-2"></i>Đặt vé
                      </a>

                      @if($movie->trailer_url)
                        <a href="{{ $movie->trailer_url }}" target="_blank" class="btn btn-cinema-secondary">
                          Xem trailer
                        </a>
                      @endif
                    </div>

                  </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-5">
                  <div class="hero-poster-card">
                    @if($poster)
                      <img src="{{ $poster }}" alt="{{ $title }}">
                    @else
                      <div class="poster-fallback">{{ $title }}</div>
                    @endif
                  </div>
                </div>

              </div>
            </div>
          </div>

          @empty
          <div class="carousel-item active">
            <div class="hero-card">
              <div class="hero-copy">
                <h1>{{ $brand }}</h1>
                <p>Chưa có dữ liệu phim để hiển thị.</p>
              </div>
            </div>
          </div>
          @endforelse

        </div>
      </div>

      <!-- SIDEBAR -->
      <aside class="hero-sidebar">
        <div class="glass-panel quick-panel h-100">
          <h2>Đặt vé nhanh</h2>
          <p>Cứ {{ number_format($pointAmount) }}đ = 1 điểm</p>

          <div class="quick-actions">
            <a href="#movie-sections" class="quick-action-card">
              🎬 {{ $stats['movie_count'] }} phim
            </a>

            <a href="#offers" class="quick-action-card">
              🎁 Ưu đãi
            </a>
          </div>
        </div>
      </aside>

    </div>
  </div>
</section>

<!-- MODALS -->
@foreach($modalMovies as $movie)
  @include('frontend.partials.showtime-modal', [
    'movie' => $movie,
    'showtimesByMovie' => $showtimesByMovie
  ])
@endforeach

@endsection