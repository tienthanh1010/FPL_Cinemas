<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'Aurora Cinema')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('frontend/cinema-ui.css') }}" rel="stylesheet">
  @stack('styles')
</head>
<body>
  <div class="site-shell">
    <div class="topbar">
      <div class="container-fluid app-container d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3 text-white-50 small">
          <span><i class="bi bi-telephone-outbound me-1"></i>Hotline: 1900 6868</span>
          <span><i class="bi bi-stars me-1"></i>Không gian điện ảnh hiện đại</span>
        </div>
        <div class="d-flex align-items-center gap-3 small text-white-50">
          <a href="#movie-sections">Lịch chiếu</a>
          <a href="#offers">Ưu đãi</a>
          <a href="{{ route('admin.login') }}">Quản trị</a>
        </div>
      </div>
    </div>

    <header class="main-header sticky-top">
      <div class="container-fluid app-container">
        <nav class="navbar navbar-expand-xl py-3">
          <a class="navbar-brand brand-mark" href="{{ route('home') }}">
            <span class="brand-mark__icon"><i class="bi bi-play-circle-fill"></i></span>
            <span>
              <strong>Aurora</strong>
              <small>Cinema Studio</small>
            </span>
          </a>

          <button class="navbar-toggler border-0 shadow-none text-white" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
          </button>

          <div class="collapse navbar-collapse" id="siteNavbar">
            <ul class="navbar-nav mx-auto align-items-xl-center gap-xl-2">
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link" href="#movie-sections">Phim đang chiếu</a></li>
              <li class="nav-item"><a class="nav-link" href="#experience">Trải nghiệm rạp</a></li>
              <li class="nav-item"><a class="nav-link" href="#offers">Ưu đãi thành viên</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('category.show') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Thể loại</a>
                <ul class="dropdown-menu dropdown-menu-dark glass-dropdown border-0 shadow-lg mt-2">
                  @foreach(($categories ?? \App\Models\Category::query()->withCount('movies')->orderBy('name')->limit(8)->get()) as $navCategory)
                    <li>
                      <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('category.show', $navCategory) }}">
                        <span>{{ $navCategory->name }}</span>
                        <span class="badge rounded-pill text-bg-light-subtle text-dark">{{ $navCategory->movies_count ?? $navCategory->active_movies_count ?? 0 }}</span>
                      </a>
                    </li>
                  @endforeach
                </ul>
              </li>
            </ul>

            <div class="header-actions d-flex flex-wrap gap-2">
              <a class="btn btn-ghost" href="#movie-sections"><i class="bi bi-calendar-week me-2"></i>Lịch hôm nay</a>
              <a class="btn btn-primary-soft" href="#booking-widget"><i class="bi bi-ticket-detailed me-2"></i>Đặt vé nhanh</a>
            </div>
          </div>
        </nav>
      </div>
    </header>

    <main>
      @if(session('success'))
        <div class="container-fluid app-container pt-4">
          <div class="alert app-alert app-alert--success">{{ session('success') }}</div>
        </div>
      @endif
      @if(session('error'))
        <div class="container-fluid app-container pt-4">
          <div class="alert app-alert app-alert--error">{{ session('error') }}</div>
        </div>
      @endif
      @yield('content')
    </main>

    <footer class="site-footer">
      <div class="container-fluid app-container">
        <div class="footer-grid">
          <div>
            <div class="brand-mark brand-mark--footer mb-3">
              <span class="brand-mark__icon"><i class="bi bi-play-circle-fill"></i></span>
              <span>
                <strong>Aurora</strong>
                <small>Cinema Studio</small>
              </span>
            </div>
            <p class="footer-copy">Giao diện lấy cảm hứng từ website rạp chiếu phim hiện đại, nhưng được thiết kế lại theo hướng tối giản, sang hơn và có điểm nhấn màu cam - xanh đêm.</p>
            <div class="footer-socials d-flex gap-2 mt-3">
              <a href="#"><i class="bi bi-facebook"></i></a>
              <a href="#"><i class="bi bi-instagram"></i></a>
              <a href="#"><i class="bi bi-youtube"></i></a>
              <a href="#"><i class="bi bi-tiktok"></i></a>
            </div>
          </div>
          <div>
            <h3>Khám phá</h3>
            <ul>
              <li><a href="#movie-sections">Lịch chiếu nổi bật</a></li>
              <li><a href="#experience">Không gian rạp</a></li>
              <li><a href="#offers">Ưu đãi thành viên</a></li>
              <li><a href="{{ route('admin.login') }}">Khu quản trị</a></li>
            </ul>
          </div>
          <div>
            <h3>Hỗ trợ khách hàng</h3>
            <ul>
              <li><a href="#">Câu hỏi thường gặp</a></li>
              <li><a href="#">Chính sách đổi trả</a></li>
              <li><a href="#">Điều khoản thành viên</a></li>
              <li><a href="#">Hướng dẫn đặt vé</a></li>
            </ul>
          </div>
          <div>
            <h3>Liên hệ</h3>
            <ul>
              <li><i class="bi bi-geo-alt me-2"></i>Hà Nội, Việt Nam</li>
              <li><i class="bi bi-telephone me-2"></i>1900 2307</li>
              <li><i class="bi bi-envelope me-2"></i>support@auroracinema.vn</li>
              <li><i class="bi bi-clock me-2"></i>07:00 - 23:00 mỗi ngày</li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  @stack('scripts')
</body>
</html>
