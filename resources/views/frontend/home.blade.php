@extends('frontend.layout')

@section('title', ($appBrand ?? config('app.name', 'FPL Cinema')) . ' | Trang chủ')

@section('content')
  @php
    $brand = $appBrand ?? config('app.name', 'FPL Cinema');
    $cinemaName = $primaryCinema?->name ?: $brand;
    $pointAmount = (int) config('loyalty.amount_per_point', 10000);
    $modalMovies = $heroMovies->concat($nowShowing)->concat($comingSoon)->concat($specialMovies)->unique('id')->values();
  @endphp

  <section class="hero-section section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="hero-shell hero-shell--compact">
        <div id="homeHeroCarousel" class="carousel slide hero-carousel" data-bs-ride="carousel">
          <div class="carousel-indicators hero-indicators">
            @foreach($heroMovies as $index => $hero)
              <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" aria-label="Slide {{ $index + 1 }}"></button>
            @endforeach
          </div>
          <div class="carousel-inner">
            @forelse($heroMovies as $index => $hero)
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
              <a href="{{ route('booking.lookup') }}" class="quick-shortcut">
                <i class="bi bi-search"></i>
                <div>
                  <strong>Tra cứu vé</strong>
                  <small>Xem trạng thái booking hoặc tiếp tục thanh toán nếu đơn còn hiệu lực</small>
                </div>
              </a>
              <a href="{{ route('offers.index') }}" class="quick-shortcut">
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
      <div class="section-heading">
        <div>
          <span class="section-eyebrow">Phim và lịch chiếu</span>
          <h2>Chọn phim đang phù hợp với bạn</h2>
        </div>
      </div>

      <div class="movie-shell movie-shell--full">
        <div class="movie-center">
          <ul class="nav movie-tabs" id="movieTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="now-showing-tab" data-bs-toggle="tab" data-bs-target="#now-showing-pane" type="button" role="tab">Đang chiếu</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="coming-soon-tab" data-bs-toggle="tab" data-bs-target="#coming-soon-pane" type="button" role="tab">Sắp ra mắt</button>
            </li>
          </ul>

          <div class="tab-content pt-4">
            <div class="tab-pane fade show active" id="now-showing-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($nowShowing as $movie)
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim đang chiếu để hiển thị.</div></div>
                @endforelse
              </div>
            </div>
            <div class="tab-pane fade" id="coming-soon-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($comingSoon as $movie)
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Coming', 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim sắp ra mắt.</div></div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-space section-alt" id="member-benefits">
    <div class="container-fluid app-container">
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-6">
          <div class="glass-panel feature-panel h-100">
            {{-- <span class="section-eyebrow">Tài khoản thành viên</span> --}}
            {{-- <h2>Đặt vé bằng tài khoản để theo dõi lịch sử và tích điểm</h2> --}}
            {{-- <div class="feature-list mt-4">
              <div>
                <i class="bi bi-person-check"></i>
                <div>
                  <strong>Phân luồng đăng nhập rõ ràng</strong>
                  <p>Tài khoản admin được chuyển thẳng sang quản trị. Tài khoản thành viên ở lại giao diện người dùng và trang cá nhân.</p>
                </div>
              </div>
              <div>
                <i class="bi bi-piggy-bank"></i>
                <div>
                  <strong>Tích điểm sau mỗi đơn thành công</strong>
                  <p>Cứ {{ number_format($pointAmount) }}đ thanh toán thành công sẽ nhận 1 điểm. Ví dụ đơn 500.000đ sẽ nhận khoảng {{ number_format((int) floor(500000 / max(1, $pointAmount))) }} điểm.</p>
                </div>
              </div>
              <div>
                <i class="bi bi-receipt"></i>
                <div>
                  <strong>Lưu lịch sử booking tập trung</strong>
                  <p>Người dùng có thể xem trạng thái booking, lịch sử thanh toán và điểm hiện có ngay trong mục tài khoản.</p>
                </div>
              </div>
            </div> --}}
          </div>
        </div>
        <div class="col-lg-6">
          <div class="glass-panel compact-panel h-100">
            <span class="section-eyebrow">Khám phá nhanh</span>
            <h2>Thể loại đang có tại {{ $cinemaName }}</h2>

            <div class="category-pill-grid mt-4">
              @forelse($categories->take(8) as $category)
                <a href="{{ route('category.show', $category) }}" class="category-pill">
                  <span>{{ $category->name }}</span>
                  <small>{{ $category->movies_count }} phim</small>
                </a>
              @empty
                <div class="empty-panel w-100">Hiện chưa có thể loại nào để hiển thị.</div>
              @endforelse
            </div>

            <div class="compact-note mt-4">
              <i class="bi bi-info-circle"></i>
              <span>Nếu cần xem thêm nội dung, bạn có thể chuyển sang mục Tin tức, Ưu đãi hoặc Tra cứu vé ở thanh điều hướng phía trên.</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-space" id="offers">
    <div class="container-fluid app-container">
      <div class="section-heading">
        <div>
          <span class="section-eyebrow">Ưu đãi đang áp dụng</span>
          <h2>Khuyến mãi và quyền lợi dành cho khách hàng</h2>
        </div>
        <a href="{{ route('offers.index') }}" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
      </div>

      <div class="content-grid">
        @forelse($latestOfferPosts as $post)
          <article class="content-card h-100">
            <a href="{{ route('offers.show', $post) }}" class="content-card__cover">
              @if($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
              @else
                <div class="content-card__fallback"><i class="bi bi-gift"></i></div>
              @endif
            </a>
            <div class="content-card__body">
              <div class="content-card__meta">
                <span class="content-tag">{{ $post->badge_label ?: 'Ưu đãi' }}</span>
                <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y') }}</span>
              </div>
              <h3><a href="{{ route('offers.show', $post) }}">{{ $post->title }}</a></h3>
              <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 120) }}</p>
              <a href="{{ route('offers.show', $post) }}" class="content-card__link">Xem chi tiết <i class="bi bi-arrow-right-short"></i></a>
            </div>
          </article>
        @empty
          <div class="glass-panel empty-panel w-100">Hiện chưa có ưu đãi nào được xuất bản. Bạn có thể thêm ở admin &gt; Tin tức &amp; ưu đãi.</div>
        @endforelse
      </div>
    </div>
  </section>

  <section class="section-space pt-0" id="news">
    <div class="container-fluid app-container">
      <div class="section-heading">
        <div>
          <span class="section-eyebrow">Tin tức mới nhất</span>
          <h2>Cập nhật điện ảnh và thông báo từ rạp</h2>
        </div>
        <a href="{{ route('news.index') }}" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
      </div>

      <div class="content-grid">
        @forelse($latestNewsPosts as $post)
          <article class="content-card h-100">
            <a href="{{ route('news.show', $post) }}" class="content-card__cover">
              @if($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
              @else
                <div class="content-card__fallback"><i class="bi bi-newspaper"></i></div>
              @endif
            </a>
            <div class="content-card__body">
              <div class="content-card__meta">
                <span class="content-tag">{{ $post->badge_label ?: 'Tin tức' }}</span>
                <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y') }}</span>
              </div>
              <h3><a href="{{ route('news.show', $post) }}">{{ $post->title }}</a></h3>
              <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 130) }}</p>
              <a href="{{ route('news.show', $post) }}" class="content-card__link">Đọc bài viết <i class="bi bi-arrow-right-short"></i></a>
            </div>
          </article>
        @empty
          <div class="glass-panel empty-panel w-100">Hiện chưa có bài viết nào được xuất bản.</div>
        @endforelse
      </div>
    </div>
  </section>

  @foreach($modalMovies as $movie)
    @include('frontend.partials.showtime-modal', ['movie' => $movie, 'showtimesByMovie' => $showtimesByMovie])
  @endforeach
@endsection
