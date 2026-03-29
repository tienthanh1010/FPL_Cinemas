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
              <a href="#upcoming" class="quick-action-card">
                <i class="bi bi-calendar-event"></i>
                <div>
                  <strong>Phim sắp chiếu</strong>
                  <span>Danh sách phim mới chuẩn bị ra mắt</span>
                </div>
              </a>
              <a href="/uu-dai" class="quick-action-card">
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
            <li class="nav-item ms-auto">
              <a href="{{ route('movies.now_showing') }}" class="nav-link btn-see-all" role="button">
                <i class="bi bi-arrow-right me-2"></i>Xem tất cả phim
              </a>
            </li>
          </ul>

          <div class="tab-content pt-4">
            <div class="tab-pane fade show active" id="now-showing-pane" role="tabpanel" tabindex="0">
              <!-- Now Showing Filters & Sort -->
              <div class="now-showing-controls mb-4">
                <form method="GET" action="#now-showing-pane" class="now-showing-filters">
                  <div class="filters-row">
                    <div class="filter-group">
                      <label for="filterGenre" class="filter-label"><i class="bi bi-tag"></i> Thể loại</label>
                      <select id="filterGenre" name="genre" class="filter-select" onchange="this.form.submit()">
                        <option value="">Tất cả thể loại</option>
                        @foreach($categories as $category)
                          <option value="{{ $category->id }}" @selected($filterGenre == $category->id)>
                            {{ $category->name }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    
                    @if($formats && count($formats) > 0)
                      <div class="filter-group">
                        <label for="filterFormat" class="filter-label"><i class="bi bi-projector"></i> Định dạng</label>
                        <select id="filterFormat" name="format" class="filter-select" onchange="this.form.submit()">
                          <option value="">Tất cả định dạng</option>
                          @foreach($formats as $format)
                            <option value="{{ $format['value'] }}" @selected($filterFormat == $format['value'])>
                              {{ $format['label'] }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    @endif
                    
                    @if($cinemas && count($cinemas) > 0)
                      <div class="filter-group">
                        <label for="filterCinema" class="filter-label"><i class="bi bi-building"></i> Rạp chiếu</label>
                        <select id="filterCinema" name="cinema" class="filter-select" onchange="this.form.submit()">
                          <option value="">Tất cả rạp</option>
                          @foreach($cinemas as $cinema)
                            <option value="{{ $cinema['id'] }}" @selected($filterCinema == $cinema['id'])>
                              {{ $cinema['name'] }}
                            </option>
                          @endforeach
                        </select>
                      </div>
                    @endif
                    
                    <div class="filter-group">
                      <label for="sortBy" class="filter-label"><i class="bi bi-arrow-down-up"></i> Sắp xếp</label>
                      <select id="sortBy" name="sort" class="filter-select" onchange="this.form.submit()">
                        <option value="release_date" @selected($sortBy == 'release_date')>Mới nhất phát hành</option>
                        <option value="title" @selected($sortBy == 'title')>Tên phim (A-Z)</option>
                        <option value="popular" @selected($sortBy == 'popular')>Suất chiếu nhiều nhất</option>
                      </select>
                    </div>
                    
                    @if($filterGenre || $filterFormat || $filterCinema)
                      <div class="filter-group">
                        <a href="{{ route('home') }}" class="btn btn-sm btn-outline-secondary">
                          <i class="bi bi-x"></i> Xoá bộ lọc
                        </a>
                      </div>
                    @endif
                  </div>
                </form>
              </div>

              <!-- Movies Grid -->
              <div class="row g-4">
                @forelse($nowShowing as $movie)
                  <div class="col-sm-6 col-lg-4 col-xl-3">
                    @include('frontend.partials.movie-card', ['movie' => $movie, 'showNextShowtime' => true])
                  </div>
                @empty
                  <div class="col-12">
                    <div class="glass-panel empty-panel">
                      <i class="bi bi-search"></i>
                      <p>Không có kết quả phù hợp. Hãy thử thay đổi bộ lọc.</p>
                    </div>
                  </div>
                @endforelse
              </div>
            </div>
            
            <div class="tab-pane fade" id="coming-soon-pane" role="tabpanel" tabindex="0">
              <div class="row g-4">
                @forelse($comingSoon as $movie)
                  <div class="col-sm-6 col-lg-4 col-xl-3">
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
                  <div class="col-sm-6 col-lg-4 col-xl-3">
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

  <section class="section-space section-alt" id="upcoming">
    <div class="container-fluid app-container">
      <div class="row gy-4">
        <div class="col-12 mb-3">
          <h2 class="section-title">Phim sắp chiếu</h2>
          <p class="text-muted">Các tựa phim chuẩn bị ra rạp trong thời gian tới.</p>
        </div>

        @forelse($comingSoon as $movie)
          <div class="col-sm-6 col-lg-3">
            @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Sắp chiếu'])
          </div>
        @empty
          <div class="col-12">
            <div class="glass-panel empty-panel">Hiện chưa có dữ liệu phim sắp chiếu. Bạn có thể thêm dữ liệu release_date trong tương lai để phần này nổi bật hơn.</div>
          </div>
        @endforelse
      </div>
    </div>
  </section>


      <div class="promotion-toolbar mb-4 d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <span class="text-white fw-bold">Lọc ưu đãi:</span>
          <select id="offer-kind-filter" class="form-select form-select-sm" style="width: 170px;">
            <option value="all">Tất cả</option>
            <option value="coupon">Thẻ</option>
            <option value="combo">Combo</option>
            <option value="price">Giá/tặng</option>
          </select>

          <select id="offer-type-filter" class="form-select form-select-sm" style="width: 170px;">
            <option value="all">Tất cả loại</option>
            @foreach($promotionTypes as $code => $label)
              <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div>
          <a href="{{ route('movies.now_showing') }}" class="btn btn-cinema-secondary me-2">Phim đang chiếu</a>
          <a href="{{ route('movies.coming_soon') }}" class="btn btn-cinema-primary">Phim sắp chiếu</a>
        </div>
      </div>

      <div class="offer-carousel d-flex gap-3 overflow-auto pb-2" id="offerCarousel">
        @forelse($promotions as $promo)
          <article class="offer-panel card flex-shrink-0" data-kind="{{ $promo->applies_to == 'ORDER' ? 'price' : (strtolower($promo->applies_to)=='ticket' ? 'ticket' : 'product') }}" data-type="{{ $promo->applies_to }}">
            <div class="card-body">
              <div class="offer-badge">{{ $promo->code }}</div>
              <h5 class="card-title">{{ $promo->name }}</h5>
              <p class="card-text text-truncate">{{ $promo->description ?: 'Ưu đãi hấp dẫn dành cho thành viên Aurora.' }}</p>
              <div class="small text-muted mb-2">Giảm {{ $promo->promo_type == 'PERCENT' ? $promo->discount_value . '%' : number_format($promo->discount_value, 0, ',', '.') . 'đ' }} · Áp dụng: {{ $promotionTypes[$promo->applies_to] ?? $promo->applies_to }}</div>
              <div class="d-flex gap-1 flex-wrap">
                @if($promo->cinemas->isNotEmpty())
                  <span class="badge text-bg-info">{{ $promo->cinemas->count() }} rạp</span>
                @endif
                @if($promo->movies->isNotEmpty())
                  <span class="badge text-bg-warning">{{ $promo->movies->count() }} phim</span>
                @endif
              </div>
              <a href="#" class="btn btn-cinema-primary btn-sm mt-2">Xem chi tiết</a>
            </div>
          </article>
        @empty
          <div class="glass-panel empty-panel w-100">Hiện không có ưu đãi nào đang hoạt động.</div>
        @endforelse
      </div>

      <script>
        document.getElementById('offer-kind-filter').addEventListener('change', function() {
          updateOfferFilters();
        });
        document.getElementById('offer-type-filter').addEventListener('change', function() {
          updateOfferFilters();
        });

        function updateOfferFilters() {
          const kind = document.getElementById('offer-kind-filter').value;
          const type = document.getElementById('offer-type-filter').value;

          document.querySelectorAll('#offerCarousel .offer-panel').forEach(panel => {
            const panelKind = panel.dataset.kind || 'price';
            const panelType = panel.dataset.type || 'ORDER';
            const matchKind = (kind === 'all') || (panelKind === kind);
            const matchType = (type === 'all') || (panelType === type);
            panel.style.display = (matchKind && matchType) ? '' : 'none';
          });
        }
      </script>
    </div>
  </section>
@endsection
