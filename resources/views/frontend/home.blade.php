@extends('frontend.layout')

@section('title', 'Aurora Cinema | Trang chủ')

@section('content')
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
                  <div class="hero-card__backdrop" style="background-image: linear-gradient(100deg, rgba(5,10,23,.92) 8%, rgba(5,10,23,.58) 48%, rgba(5,10,23,.82) 100%), url('{{ $hero->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80' }}')"></div>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>Bộ sưu tập phim nổi bật</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 220) : 'Khám phá trải nghiệm xem phim mới với giao diện trẻ trung, hiện đại, khác biệt rõ ràng so với mẫu tham chiếu.' }}</p>
                        <div class="hero-meta">
                          <span><i class="bi bi-clock-history me-2"></i>{{ $hero->duration_minutes }} phút</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ optional($hero->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
                          <span><i class="bi bi-badge-hd me-2"></i>{{ $hero->versions->pluck('format')->filter()->unique()->implode(' / ') ?: '2D' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
                          @foreach($hero->genres->take(3) as $genre)
                            <span class="genre-chip">{{ $genre->name }}</span>
                          @endforeach
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                          <a href="{{ route('movies.showtimes', $hero) }}" class="btn btn-cinema-primary">
                            <i class="bi bi-ticket-perforated me-2"></i>Đặt vé ngay
                          </a>
                          <a href="#movie-sections" class="btn btn-cinema-secondary">
                            <i class="bi bi-collection-play me-2"></i>Xem danh sách phim
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
                        <div class="hero-floating-note">
                          <strong>Thiết kế mới</strong>
                          <span>Vẫn gợi cảm hứng từ web rạp chiếu phim, nhưng dùng bố cục kính mờ, nền đêm và card bo tròn lớn để khác biệt rõ hơn.</span>
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
                    <span class="eyebrow">Aurora Cinema</span>
                    <h1>Không gian điện ảnh mới cho dự án của bạn</h1>
                    <p>Thêm dữ liệu phim để kích hoạt slider nổi bật.</p>
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>

        <aside class="hero-sidebar" id="booking-widget">
          <div class="glass-panel quick-panel">
            <span class="panel-badge"><i class="bi bi-lightning-charge-fill"></i>Đặt vé nhanh</span>
            <h2>Chọn bộ phim phù hợp cho hôm nay</h2>
            <p>Thay vì mô phỏng trang tham chiếu quá sát, giao diện mới nhấn mạnh vào cảm giác booking nhanh, trực quan và hiện đại.</p>
            <div class="quick-actions">
              <a href="#movie-sections" class="quick-action-card">
                <i class="bi bi-film"></i>
                <div>
                  <strong>Phim đang chiếu</strong>
                  <span>{{ $stats['movie_count'] }} tựa phim đã sẵn sàng</span>
                </div>
              </a>
              <a href="#experience" class="quick-action-card">
                <i class="bi bi-camera-reels"></i>
                <div>
                  <strong>Không gian trải nghiệm</strong>
                  <span>Phòng chiếu, ghế ngồi và ưu đãi</span>
                </div>
              </a>
              <a href="#offers" class="quick-action-card">
                <i class="bi bi-gift"></i>
                <div>
                  <strong>Ưu đãi thành viên</strong>
                  <span>Combo, voucher và quà tặng</span>
                </div>
              </a>
            </div>
          </div>

          <div class="stats-strip">
            <div class="stats-card">
              <span>{{ $stats['movie_count'] }}</span>
              <small>Phim hiển thị</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['category_count'] }}</span>
              <small>Thể loại</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['show_count'] }}</span>
              <small>Suất mở bán</small>
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
          <span class="section-eyebrow">Khám phá lịch chiếu</span>
          <h2>Tuyển tập phim cho tuần này</h2>
        </div>
        <p>Giữ tinh thần website rạp chiếu phim với banner lớn, tab nội dung và card phim đậm tính thương mại, nhưng được tái thiết kế bằng tỷ lệ thoáng hơn và màu sắc khác biệt rõ rệt.</p>
      </div>

      <div class="movie-shell">
        <div class="promo-rail promo-rail--left">
          <div class="promo-card promo-card--glow">
            <span>Thẻ thành viên</span>
            <strong>Tích điểm cho mỗi giao dịch, nhận voucher sau mỗi mốc mới.</strong>
          </div>
          <div class="promo-card">
            <span>Khuyến mãi giữa tuần</span>
            <strong>Giảm tới 20% cho suất chiếu ban ngày và combo snack.</strong>
          </div>
        </div>

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
                @foreach($nowShowing as $movie)
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie])
                  </div>
                @endforeach
              </div>
            </div>
            <div class="tab-pane fade" id="coming-soon-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($comingSoon as $movie)
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Coming'])
                  </div>
                @empty
                  <div class="col-12">
                    <div class="glass-panel empty-panel">Hiện chưa có dữ liệu phim sắp chiếu. Bạn có thể thêm thêm vài bản ghi release_date trong tương lai để phần này nổi bật hơn.</div>
                  </div>
                @endforelse
              </div>
            </div>
            <div class="tab-pane fade" id="special-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @foreach($specialMovies as $movie)
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Spotlight'])
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="promo-rail promo-rail--right">
          <div class="promo-card promo-card--accent">
            <span>Không gian khác biệt</span>
            <strong>Phần quảng bá được chuyển từ poster đứng sang card nội dung để tổng thể hiện đại và ít “na ná” hơn mẫu tham chiếu.</strong>
          </div>
          <div class="promo-card">
            <span>Suất chiếu linh hoạt</span>
            <strong>Kết hợp giá động, ưu đãi hội viên và combo F&amp;B ngay tại trang đặt vé.</strong>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-space section-alt" id="experience">
    <div class="container-fluid app-container">
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-6">
          <div class="glass-panel feature-panel h-100">
            <span class="section-eyebrow">Thiết kế trang chủ</span>
            <h2>Điểm khác biệt rõ so với giao diện tham chiếu</h2>
            <div class="feature-list">
              <div>
                <i class="bi bi-layout-text-window"></i>
                <div>
                  <strong>Bố cục rộng, thoáng</strong>
                  <p>Hero chia 2 cột rõ ràng, khu đặt vé nhanh nằm ngay đầu trang thay vì chỉ bám menu trên.</p>
                </div>
              </div>
              <div>
                <i class="bi bi-palette2"></i>
                <div>
                  <strong>Màu sắc khác biệt</strong>
                  <p>Tông xanh đêm, tím than và cam đồng giúp giao diện giữ vibe điện ảnh nhưng không bị giống thương hiệu tham chiếu.</p>
                </div>
              </div>
              <div>
                <i class="bi bi-grid-1x2"></i>
                <div>
                  <strong>Card & glassmorphism</strong>
                  <p>Sử dụng card bo tròn lớn, nền kính mờ và lớp bóng mềm để tạo cảm giác cao cấp hơn.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="experience-grid h-100">
            @foreach($categories->take(6) as $category)
              <a href="{{ route('category.show', $category) }}" class="experience-card">
                <span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                <strong>{{ $category->name }}</strong>
                <small>{{ $category->movies_count }} phim phù hợp</small>
              </a>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-space" id="offers">
    <div class="container-fluid app-container">
      <div class="offers-banner">
        <div>
          <span class="section-eyebrow">Ưu đãi & thành viên</span>
          <h2>Thiết kế sẵn chỗ cho combo, voucher và khuyến mãi</h2>
          <p>Phần UI này hợp với các module bạn đang xây: combo bắp nước, giá động, voucher và quản lý thành viên.</p>
        </div>
        <div class="offers-actions">
          <a href="#movie-sections" class="btn btn-cinema-primary">Xem phim nổi bật</a>
          <a href="{{ route('admin.login') }}" class="btn btn-cinema-secondary">Vào trang quản trị</a>
        </div>
      </div>
    </div>
  </section>
@endsection
