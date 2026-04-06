@extends('frontend.layout')

<<<<<<< HEAD
@section('title', ($appBrand ?? config('app.name', 'FPL Cinemas')) . ' | Trang chủ')

@section('content')
  @php
    $brand = $appBrand ?? config('app.name', 'FPL Cinemas');
    $cinemaName = $primaryCinema?->name ?: $brand;
    $pointAmount = (int) config('loyalty.amount_per_point', 10000);
    $modalMovies = $heroMovies->concat($nowShowing)->concat($comingSoon)->concat($specialMovies)->unique('id')->values();
  @endphp

=======
@section('title', 'Aurora Cinema | Trang chủ')

@section('content')
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD
                  <div class="hero-card__backdrop" style="background-image: url('{{ $hero->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80' }}')"></div>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>{{ $cinemaName }} · Trải nghiệm điện ảnh nổi bật</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 220) : 'Đặt vé gọn gàng hơn, thanh toán rõ ràng hơn, theo dõi lịch sử và điểm tích luỹ ngay trong một tài khoản thành viên.' }}</p>
=======
                  <div class="hero-card__backdrop" style="background-image: linear-gradient(100deg, rgba(5,10,23,.92) 8%, rgba(5,10,23,.58) 48%, rgba(5,10,23,.82) 100%), url('{{ $hero->poster_url ?: 'https://images.unsplash.com/photo-1489599849927-2ee91cede3ba?auto=format&fit=crop&w=1400&q=80' }}')"></div>
                  <div class="row align-items-center g-4 position-relative">
                    <div class="col-lg-7">
                      <div class="hero-copy">
                        <span class="eyebrow"><i class="bi bi-stars me-2"></i>Bộ sưu tập phim nổi bật</span>
                        <h1>{{ $hero->title }}</h1>
                        <p>{{ $hero->synopsis ? \Illuminate\Support\Str::limit($hero->synopsis, 220) : 'Khám phá trải nghiệm xem phim mới với giao diện trẻ trung, hiện đại, khác biệt rõ ràng so với mẫu tham chiếu.' }}</p>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                        <div class="hero-meta">
                          <span><i class="bi bi-clock-history me-2"></i>{{ $hero->duration_minutes }} phút</span>
                          <span><i class="bi bi-calendar-event me-2"></i>{{ optional($hero->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
                          <span><i class="bi bi-badge-hd me-2"></i>{{ $hero->versions->pluck('format')->filter()->unique()->implode(' / ') ?: '2D' }}</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mb-4">
<<<<<<< HEAD
                          @forelse($hero->genres->take(3) as $genre)
                            <span class="genre-chip">{{ $genre->name }}</span>
                          @empty
                            <span class="genre-chip">Phim nổi bật</span>
                          @endforelse
=======
                          @foreach($hero->genres->take(3) as $genre)
                            <span class="genre-chip">{{ $genre->name }}</span>
                          @endforeach
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                        </div>
                        <div class="d-flex flex-wrap gap-3">
                          <a href="{{ route('movies.showtimes', $hero) }}" class="btn btn-cinema-primary">
                            <i class="bi bi-ticket-perforated me-2"></i>Đặt vé ngay
                          </a>
<<<<<<< HEAD
                          <a href="{{ route('news.index') }}" class="btn btn-cinema-secondary">
                            <i class="bi bi-newspaper me-2"></i>Xem tin mới
=======
                          <a href="#movie-sections" class="btn btn-cinema-secondary">
                            <i class="bi bi-collection-play me-2"></i>Xem danh sách phim
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD
=======
                        <div class="hero-floating-note">
                          <strong>Thiết kế mới</strong>
                          <span>Vẫn gợi cảm hứng từ web rạp chiếu phim, nhưng dùng bố cục kính mờ, nền đêm và card bo tròn lớn để khác biệt rõ hơn.</span>
                        </div>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD
                    <span class="eyebrow">{{ $brand }}</span>
                    <h1>Giao diện đặt vé tập trung cho một rạp duy nhất</h1>
                    <p>Thêm dữ liệu phim, tin tức và ưu đãi để trang chủ hiển thị đầy đủ hơn.</p>
=======
                    <span class="eyebrow">Aurora Cinema</span>
                    <h1>Không gian điện ảnh mới cho dự án của bạn</h1>
                    <p>Thêm dữ liệu phim để kích hoạt slider nổi bật.</p>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                  </div>
                </div>
              </div>
            @endforelse
          </div>
        </div>

        <aside class="hero-sidebar" id="booking-widget">
<<<<<<< HEAD
          <div class="glass-panel quick-panel h-100">
            <span class="panel-badge"><i class="bi bi-lightning-charge-fill"></i>Đặt vé nhanh</span>
            <h2>Chọn phim, giữ ghế, thanh toán và tích điểm</h2>
            <p class="mb-4">Hệ thống hiện tối ưu cho mô hình một rạp, giúp khách hàng thao tác nhanh hơn và giảm nhầm lẫn địa điểm.</p>

=======
          <div class="glass-panel quick-panel">
            <span class="panel-badge"><i class="bi bi-lightning-charge-fill"></i>Đặt vé nhanh</span>
            <h2>Chọn bộ phim phù hợp cho hôm nay</h2>
            <p>Thay vì mô phỏng trang tham chiếu quá sát, giao diện mới nhấn mạnh vào cảm giác booking nhanh, trực quan và hiện đại.</p>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            <div class="quick-actions">
              <a href="#movie-sections" class="quick-action-card">
                <i class="bi bi-film"></i>
                <div>
                  <strong>Phim đang chiếu</strong>
<<<<<<< HEAD
                  <span>{{ $stats['movie_count'] }} tựa phim khả dụng</span>
                </div>
              </a>
              <a href="#member-benefits" class="quick-action-card">
                <i class="bi bi-stars"></i>
                <div>
                  <strong>Tài khoản thành viên</strong>
                  <span>Cứ {{ number_format($pointAmount) }}đ = 1 điểm tích luỹ</span>
=======
                  <span>{{ $stats['movie_count'] }} tựa phim đã sẵn sàng</span>
                </div>
              </a>
              <a href="#experience" class="quick-action-card">
                <i class="bi bi-camera-reels"></i>
                <div>
                  <strong>Không gian trải nghiệm</strong>
                  <span>Phòng chiếu, ghế ngồi và ưu đãi</span>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                </div>
              </a>
              <a href="#offers" class="quick-action-card">
                <i class="bi bi-gift"></i>
                <div>
<<<<<<< HEAD
                  <strong>Ưu đãi &amp; tin tức</strong>
                  <span>Xem nhanh khuyến mãi và bài viết mới nhất</span>
                </div>
              </a>
              <a href="{{ route('booking.lookup') }}" class="quick-action-card">
                <i class="bi bi-search"></i>
                <div>
                  <strong>Tra cứu booking</strong>
                  <span>Xem lại đơn vé và tiếp tục thanh toán nếu booking còn hiệu lực</span>
=======
                  <strong>Ưu đãi thành viên</strong>
                  <span>Combo, voucher và quà tặng</span>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                </div>
              </a>
            </div>
          </div>

          <div class="stats-strip">
            <div class="stats-card">
              <span>{{ $stats['movie_count'] }}</span>
<<<<<<< HEAD
              <small>Phim đang hiển thị</small>
=======
              <small>Phim hiển thị</small>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            </div>
            <div class="stats-card">
              <span>{{ $stats['category_count'] }}</span>
              <small>Thể loại</small>
            </div>
            <div class="stats-card">
              <span>{{ $stats['show_count'] }}</span>
<<<<<<< HEAD
              <small>Suất đang mở bán</small>
=======
              <small>Suất mở bán</small>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD
          <span class="section-eyebrow">Phim và lịch chiếu</span>
          <h2>Danh sách phim nổi bật trong ngày</h2>
          <p>Tập trung vào một rạp giúp lịch chiếu rõ ràng hơn, ít bước chọn rạp hơn và luồng đặt vé dễ hiểu hơn cho người dùng.</p>
        </div>
      </div>

      <div class="movie-shell movie-shell--full">
=======
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

>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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
<<<<<<< HEAD
                @forelse($nowShowing as $movie)
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim đang chiếu để hiển thị.</div></div>
                @endforelse
=======
                @foreach($nowShowing as $movie)
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie])
                  </div>
                @endforeach
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
              </div>
            </div>
            <div class="tab-pane fade" id="coming-soon-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($comingSoon as $movie)
<<<<<<< HEAD
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Coming', 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Hiện chưa có phim sắp ra mắt. Bạn có thể thêm release date trong tương lai để phần này nổi bật hơn.</div></div>
=======
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Coming'])
                  </div>
                @empty
                  <div class="col-12">
                    <div class="glass-panel empty-panel">Hiện chưa có dữ liệu phim sắp chiếu. Bạn có thể thêm thêm vài bản ghi release_date trong tương lai để phần này nổi bật hơn.</div>
                  </div>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                @endforelse
              </div>
            </div>
            <div class="tab-pane fade" id="special-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
<<<<<<< HEAD
                @forelse($specialMovies as $movie)
                  <div class="col-md-6 col-xl-4">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Spotlight', 'showtimesByMovie' => $showtimesByMovie])
                  </div>
                @empty
                  <div class="col-12"><div class="glass-panel empty-panel">Thêm phim có dữ liệu phong phú hơn để tạo mục chọn lọc đặc biệt.</div></div>
                @endforelse
=======
                @foreach($specialMovies as $movie)
                  <div class="col-sm-6 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Spotlight'])
                  </div>
                @endforeach
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
              </div>
            </div>
          </div>
        </div>
<<<<<<< HEAD
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
=======

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
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
      </div>
    </div>
  </section>

<<<<<<< HEAD
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
=======
  <section class="section-space section-alt" id="experience">
    <div class="container-fluid app-container">
      <div class="row g-4 align-items-stretch">
        <div class="col-lg-6">
          <div class="glass-panel feature-panel h-100">
<<<<<<< HEAD
            {{-- <span class="section-eyebrow">Thiết kế trang chủ</span> --}}
            {{-- <h2>Điểm khác biệt rõ so với giao diện tham chiếu</h2>
=======
            <span class="section-eyebrow">Thiết kế trang chủ</span>
            <h2>Điểm khác biệt rõ so với giao diện tham chiếu</h2>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
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
<<<<<<< HEAD
            </div> --}}
=======
            </div>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
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
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
      </div>
    </div>
  </section>

<<<<<<< HEAD
  @foreach($modalMovies as $movie)
    @include('frontend.partials.showtime-modal', ['movie' => $movie, 'showtimesByMovie' => $showtimesByMovie])
  @endforeach
=======
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
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
@endsection
