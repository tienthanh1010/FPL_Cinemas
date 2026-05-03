@extends('frontend.layout')

@section('title', ($appBrand ?? config('app.name', 'FPL Cinemas')) . ' | Trang chủ')

@section('content')
  @php
    $brand = $appBrand ?? config('app.name', 'FPL Cinemas');
    $cinemaName = $primaryCinema?->name ?: $brand;
    $pointAmount = (int) config('loyalty.amount_per_point', 10000);
    $modalMovies = $heroMovies->concat($nowShowing)->concat($comingSoon)->concat($specialMovies)->unique('id')->values();
  @endphp

  <section class="hero-section section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="hero-shell">
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
                  <div class="hero-card__backdrop" style="background-image: url('{{ $hero->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80' }}')"></div>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>{{ $cinemaName }} · Một rạp, một luồng đặt vé gọn gàng</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 190) : 'Luồng xem lịch chiếu, chọn ghế, thanh toán và quản lý tài khoản đã được tinh gọn để khách hàng thao tác nhanh, dễ hiểu và ít bị rối hơn.' }}</p>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>{{ $cinemaName }} · Trải nghiệm điện ảnh nổi bật</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 220) : 'Đặt vé gọn gàng hơn, thanh toán rõ ràng hơn, theo dõi lịch sử và điểm tích luỹ ngay trong một tài khoản thành viên.' }}</p>
                        <div class="hero-meta">
                          <span><i class="bi bi-clock-history me-2"></i>{{ $hero->duration_minutes }} phút</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ optional($hero->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
                          <span><i class="bi bi-badge-hd me-2"></i>{{ $hero->versions->pluck('format')->filter()->unique()->implode(' / ') ?: '2D' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                          @forelse($hero->genres->take(3) as $genre)
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
                            <span class="genre-chip">{{ $genre->name }}</span>
                          @empty
                            <span class="genre-chip">Phim nổi bật</span>
                          @endforelse
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                          <a href="{{ route('movies.showtimes', $hero) }}" class="btn btn-cinema-primary">
                            <i class="bi bi-ticket-perforated me-2"></i>Đặt vé ngay
                          </a>
                          <a href="{{ route('news.index') }}" class="btn btn-cinema-secondary">
                            <i class="bi bi-newspaper me-2"></i>Xem tin mới
                          </a>
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
                    <h1>Giao diện đặt vé tập trung cho một rạp duy nhất</h1>
                    <p>Thêm dữ liệu phim, tin tức và ưu đãi để trang chủ hiển thị đầy đủ hơn.</p>
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>

        <aside class="hero-sidebar" id="booking-widget">
          <div class="glass-panel quick-panel h-100">
            <span class="panel-badge"><i class="bi bi-lightning-charge-fill"></i>Đặt vé nhanh</span>
            <h2>Chọn phim, giữ ghế, thanh toán và tích điểm</h2>
            <p class="mb-4">Hệ thống hiện tối ưu cho mô hình một rạp, giúp khách hàng thao tác nhanh hơn và giảm nhầm lẫn địa điểm.</p>

            <div class="quick-actions">
              <a href="#movie-sections" class="quick-action-card">
                <i class="bi bi-film"></i>
                <div>
                  <strong>Phim đang chiếu</strong>
                  <span>{{ $stats['movie_count'] }} tựa phim khả dụng</span>
                </div>
              </a>
              <a href="#member-benefits" class="quick-action-card">
                <i class="bi bi-stars"></i>
                <div>
                  <strong>Tài khoản thành viên</strong>
                  <span>Cứ {{ number_format($pointAmount) }}đ = 1 điểm tích luỹ</span>
                </div>
              </a>
              <a href="#offers" class="quick-action-card">
                <i class="bi bi-gift"></i>
                <div>
                  <strong>Ưu đãi &amp; tin tức</strong>
                  <span>Xem nhanh khuyến mãi và bài viết mới nhất</span>
                </div>
              </a>
              <a href="{{ route('booking.lookup') }}" class="quick-action-card">
                <i class="bi bi-search"></i>
                <div>
                  <strong>Tra cứu booking</strong>
                  <span>Xem lại đơn vé và tiếp tục thanh toán nếu booking còn hiệu lực</span>
                </div>
              </a>
            </div>
          </div>

          <div class="stats-strip">
            <div class="stats-card">
              <span>{{ $stats['movie_count'] }}</span>
              <small>Phim đang hiển thị</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['category_count'] }}</span>
              <small>Thể loại</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['show_count'] }}</span>
              <small>Suất đang mở bán</small>
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
          <h2>Danh sách phim nổi bật trong ngày</h2>
          <p>Tập trung vào một rạp giúp lịch chiếu rõ ràng hơn, ít bước chọn rạp hơn và luồng đặt vé dễ hiểu hơn cho người dùng.</p>
        </div>
      </div>

      <div class="movie-shell movie-shell--full">
        <div class="movie-center">
          <ul class="nav movie-tabs" id="movieTab" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="now-showing-tab" data-bs-toggle="tab" data-bs-target="#now-showing-pane" type="button" role="tab">Đang chiếu nổi bật</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="coming-soon-tab" data-bs-toggle="tab" data-bs-target="#coming-soon-pane" type="button" role="tab">Sắp ra mắt</button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="special-tab" data-bs-toggle="tab" data-bs-target="#special-pane" type="button" role="tab">Chọn lọc đặc biệt</button>
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
                  <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim sắp ra mắt. Bạn có thể thêm release date trong tương lai để phần này nổi bật hơn.</div></div>
                @endforelse
              </div>
            </div>
            <div class="tab-pane fade" id="special-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($specialMovies as $movie)
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Spotlight', 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Thêm phim có dữ liệu phong phú hơn để tạo mục chọn lọc đặc biệt.</div></div>
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
            <span class="section-eyebrow">Tài khoản thành viên</span>
            <h2>Đặt vé bằng tài khoản để theo dõi lịch sử và tích điểm</h2>
            <div class="feature-list mt-4">
              <div>
                <i class="bi bi-person-check"></i>
                <div>
                  <strong>Phân luồng đăng nhập rõ ràng</strong>
                  <p>Tài khoản admin được chuyển thẳng vào trang quản trị. Tài khoản thành viên đi vào giao diện người dùng và trang tài khoản cá nhân.</p>
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
                  <p>Người dùng có thể xem trạng thái booking, lịch sử thanh toán và số điểm hiện có ngay trong mục tài khoản.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="experience-grid h-100">
            @forelse($categories->take(6) as $category)
              <a href="{{ route('category.show', $category) }}" class="experience-card">
                <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <strong>{{ $category->name }}</strong>
                <small>{{ $category->movies_count }} phim phù hợp</small>
              </a>
            @empty
              <div class="experience-card">
                <span>01</span>
                <strong>Danh mục phim</strong>
                <small>Bổ sung thể loại để tăng khả năng khám phá nội dung.</small>
              </div>
            @endforelse
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
          <p>Nội dung ưu đãi giờ có thể được quản lý riêng ở admin và hiển thị lại ngoài giao diện người dùng.</p>
        </div>
        <a href="{{ route('offers.index') }}" class="btn btn-cinema-secondary">Xem tất cả ưu đãi</a>
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
          <p>Phần tin tức được bổ sung để người dùng theo dõi các bài viết, thông báo và lịch hoạt động dễ dàng hơn.</p>
        </div>
        <a href="{{ route('news.index') }}" class="btn btn-cinema-secondary">Xem tất cả tin tức</a>
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
