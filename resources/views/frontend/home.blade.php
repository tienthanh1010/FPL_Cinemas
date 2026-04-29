@extends('frontend.layout')

@section('title', ($appBrand ?? config('app.name', 'FPL Cinema')) . ' | Trang chủ')

@section('content')
  @php
    $search = $search ?? '';
    $genreFilter = $genreFilter ?? 0;
    $sectionFilter = $sectionFilter ?? 'all';
    $selectedGenre = $selectedGenre ?? null;
    $ratingFilter = $ratingFilter ?? 0;
    $selectedRating = $selectedRating ?? null;
  @endphp
  @php
    $brand = $appBrand ?? config('app.name', 'FPL Cinema');
    $cinemaName = $primaryCinema?->name ?: $brand;
    $pointAmount = (int) config('loyalty.amount_per_point', 10000);
    $modalMovies = $sliderMovies->concat($hotMovies)->concat($nowShowing)->concat($comingSoon)->unique('id')->values();
  @endphp

  <section class="hero-section section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="hero-shell hero-shell--compact">
        <div id="homeHeroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
          <div class="carousel-indicators hero-indicators">
            @foreach($sliderMovies as $index => $hero)
              <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
          </div>
          <div class="carousel-inner">
            @forelse($sliderMovies as $index => $hero)
              <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                <div class="hero-card">
                  <div class="hero-card__backdrop" style="background-image: url('{{ $hero->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80' }}')"></div>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>{{ $cinemaName }} · Một rạp, một luồng đặt vé gọn gàng</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 190) : 'Luồng xem lịch chiếu, chọn ghế, thanh toán và quản lý tài khoản đã được tinh gọn để khách hàng thao tác nhanh, dễ hiểu và ít bị rối hơn.' }}</p>
                        <div class="hero-meta">
                          <span><i class="bi bi-clock-history me-2"></i>{{ $hero->duration_minutes }} phút</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ optional($hero->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
                          <span><i class="bi bi-badge-hd me-2"></i>{{ $hero->versions->pluck('format')->filter()->unique()->implode(' / ') ?: '2D' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                          @forelse($hero->genres->take(3) as $genre)
                            <span class="genre-chip">{{ $genre->name }}</span>
                          @empty
                            <span class="genre-chip">Phim nổi bật</span>
                          @endforelse
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-3">
                          <a href="{{ route('movies.showtimes', $hero) }}" class="btn btn-cinema-primary">
                            <i class="bi bi-ticket-perforated me-2"></i>Xem lịch chiếu
                          </a>
                          @if($hero->trailer_url)
                            <a href="{{ $hero->trailer_url }}" target="_blank" rel="noopener" class="section-link">
                              Xem trailer <i class="bi bi-arrow-up-right"></i>
                            </a>
                          @else
                            <span class="hero-inline-note">Đăng nhập để lưu lịch sử booking và tích điểm tự động.</span>
                          @endif
                        </div>
                      </div>
                    </div>
                    <div class="col-lg-5">
                      <div class="hero-poster-stack">
                        <div class="hero-poster-card">
                          @if($hero->poster_url)
                            <img src="{{ $hero->poster_url }}" alt="{{ $hero->title }}">
                          @else
                            <div class="poster-fallback poster-fallback--hero">
                              <span>{{ $hero->title }}</span>
                            </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            @empty
              <div class="carousel-item active">
                <div class="hero-card hero-card--empty">
                  <div class="hero-card__backdrop"></div>
                  <div class="hero-copy position-relative">
                    <span class="eyebrow">{{ $brand }}</span>
                    <h1>Giao diện đặt vé đang được tối ưu lại cho FPL Cinema</h1>
                    <p>Thêm dữ liệu phim, poster và lịch chiếu để trang chủ hiển thị nổi bật hơn.</p>
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>

        <aside class="hero-sidebar" id="booking-widget">
          <div class="glass-panel quick-panel quick-panel--compact h-100">
            <span class="panel-badge"><i class="bi bi-lightning-charge-fill"></i>Đặt vé nhanh</span>
            <h2>Đi thẳng tới thao tác bạn cần</h2>

            <div class="quick-shortcuts">
              <a href="#movie-sections" class="quick-shortcut">
                <i class="bi bi-film"></i>
                <div>
                  <strong>Lịch chiếu hôm nay</strong>
                  <small>{{ $stats['show_count'] }} suất đang mở bán tại {{ $cinemaName }}</small>
                </div>
              </a>
              <a href="{{ auth()->check() ? route('member.account') : route('member.login') }}" class="quick-shortcut">
                <i class="bi bi-person-circle"></i>
                <div>
                  <strong>{{ auth()->check() ? 'Tài khoản của bạn' : 'Đăng nhập thành viên' }}</strong>
                  <small>{{ auth()->check() ? 'Tra cứu vé, xem lịch sử booking và tiếp tục thanh toán trong một nơi.' : 'Đăng nhập để quản lý booking, tra cứu vé và nhận thông báo.' }}</small>
                </div>
              </a>
              <a href="{{ route('content.hub') }}" class="quick-shortcut">
                <i class="bi bi-gift"></i>
                <div>
                  <strong>Ưu đãi thành viên</strong>
                  <small>Cứ {{ number_format($pointAmount) }}đ thanh toán thành công = 1 điểm tích luỹ</small>
                </div>
              </a>
            </div>
          </div>

          <div class="stats-strip stats-strip--compact">
            <div class="stats-card">
              <span>{{ $stats['movie_count'] }}</span>
              <small>Phim</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['category_count'] }}</span>
              <small>Thể loại</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['show_count'] }}</span>
              <small>Suất chiếu</small>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </section>

  <section class="section-space pt-0" id="movie-sections">
    <div class="container-fluid app-container">
      <div class="section-heading section-heading--stacked">
        <div>
          <span class="section-eyebrow">Phim và lịch chiếu</span>
          <h2>Khám phá phim theo từng nhóm rõ ràng hơn</h2>
          <p>Trang chủ ưu tiên phim Hot trước, sau đó tới phim đang chiếu và cuối cùng là phim sắp chiếu.</p>
        </div>
        @if($search !== '' || $genreFilter > 0 || $ratingFilter > 0 || $sectionFilter !== 'all')
          <div class="movie-filter-summary">
            <strong>Bộ lọc đang áp dụng</strong>
            <div class="movie-filter-summary__chips">
              @if($search !== '')
                <span class="filter-chip"><i class="bi bi-search"></i>{{ $search }}</span>
              @endif
              @if($selectedGenre)
                <span class="filter-chip"><i class="bi bi-tags"></i>{{ $selectedGenre->name }}</span>
              @endif
              @if($selectedRating)
                <span class="filter-chip"><i class="bi bi-shield-check"></i>{{ $selectedRating->name }}</span>
              @endif
              @if($sectionFilter !== 'all')
                <span class="filter-chip"><i class="bi bi-funnel"></i>{{ ['hot' => 'Phim Hot', 'now' => 'Đang chiếu', 'coming' => 'Sắp chiếu'][$sectionFilter] ?? 'Tất cả phim' }}</span>
              @endif
              <a href="{{ route('home') }}#movie-sections" class="filter-chip filter-chip--reset">Xoá lọc</a>
            </div>
          </div>
        @endif
      </div>

      @if($sectionFilter === 'all' || $sectionFilter === 'hot')
        <div class="movie-section-block">
          <div class="section-heading mb-3">
            <div>
              <span class="section-eyebrow">Admin gắn nổi bật</span>
              <h3 class="h4 mb-0">Phim Hot</h3>
            </div>
          </div>
          <div class="row g-4">
            @forelse($hotMovies as $movie)
              <div class="col-md-6 col-xl-4">
                @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Hot', 'showtimesByMovie' => $showtimesByMovie])
              </div>
            @empty
              <div class="col-12"><div class="glass-panel empty-panel">Không có phim Hot nào khớp với bộ lọc hiện tại.</div></div>
            @endforelse
          </div>
        </div>
      @endif

      @if($sectionFilter === 'all' || $sectionFilter === 'now')
        <div class="movie-section-block">
          <div class="section-heading mb-3">
            <div>
              <span class="section-eyebrow">Đã có suất chiếu</span>
              <h3 class="h4 mb-0">Phim đang chiếu</h3>
            </div>
          </div>
          <div class="row g-4">
            @forelse($nowShowing as $movie)
              <div class="col-md-6 col-xl-4">
                @include('frontend.partials.movie-card', ['movie' => $movie, 'showtimesByMovie' => $showtimesByMovie])
              </div>
            @empty
              <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim đang chiếu nào khớp với bộ lọc hiện tại.</div></div>
            @endforelse
          </div>
        </div>
      @endif

      @if($sectionFilter === 'all' || $sectionFilter === 'coming')
        <div class="movie-section-block">
          <div class="section-heading mb-3">
            <div>
              <span class="section-eyebrow">Đã có phim nhưng chưa có suất</span>
              <h3 class="h4 mb-0">Phim sắp chiếu</h3>
            </div>
          </div>
          <div class="row g-4">
            @forelse($comingSoon as $movie)
              <div class="col-md-6 col-xl-4">
                @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Sắp chiếu', 'showtimesByMovie' => $showtimesByMovie])
              </div>
            @empty
              <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim sắp chiếu nào khớp với bộ lọc hiện tại.</div></div>
            @endforelse
          </div>
        </div>
      @endif
    </div>
  </section>


