@php
  $brand = $appBrand ?? config('app.name', 'FPL Cinemas');
  $cinemaName = $primaryCinema?->name ?: $brand;
  $cinemaHotline = $primaryCinema?->phone ?: '1900 6868';
  $cinemaEmail = $primaryCinema?->email ?: 'support@fplcinemas.local';
  $cinemaAddress = collect([
      $primaryCinema?->address_line,
      $primaryCinema?->ward,
      $primaryCinema?->district,
      $primaryCinema?->province,
  ])->filter()->implode(', ');
  $cinemaAddress = $cinemaAddress !== '' ? $cinemaAddress : 'Hà Nội, Việt Nam';
  $memberPoints = (int) ($authCustomer?->loyaltyAccount?->points_balance ?? 0);
  $navCategories = ($globalCategories ?? collect())->take(8);
@endphp
<!doctype html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', $brand)</title>
  <title>@yield('title', $brand)</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('frontend/cinema-ui.css') }}" rel="stylesheet">
  <script>
    (function () {
      try {
        var savedTheme = localStorage.getItem('fpl-theme');
        var theme = savedTheme === 'light' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', theme);
      } catch (e) {
        document.documentElement.setAttribute('data-theme', 'dark');
      }
    })();
  </script>

  @stack('styles')
</head>
<body>
  <div class="site-shell">
    <div class="topbar">
      <div class="container-fluid app-container d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-3 small topbar-meta flex-wrap">
          <span><i class="bi bi-geo-alt me-1"></i>{{ $cinemaName }}</span>
          <span><i class="bi bi-telephone-outbound me-1"></i>{{ $cinemaHotline }}</span>
          <span><i class="bi bi-stars me-1"></i>Tích điểm sau mỗi đơn thanh toán thành công</span>
        </div>
        <div class="d-flex align-items-center gap-3 small topbar-links flex-wrap">
          <a href="{{ route('news.index') }}">Tin tức</a>
          <a href="{{ route('offers.index') }}">Ưu đãi</a>
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
              <strong>{{ $brand }}</strong>
              <small>{{ $cinemaName }}</small>
            </span>
          </a>

          <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list fs-2"></i>
          </button>

          <div class="collapse navbar-collapse" id="siteNavbar">
            <ul class="navbar-nav mx-auto align-items-xl-center gap-xl-2">
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Trang chủ</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('home', 'movies.showtimes', 'shows.book') ? 'active' : '' }}" href="{{ request()->routeIs('home') ? '#movie-sections' : route('home') . '#movie-sections' }}">Phim &amp; lịch chiếu</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">Tin tức</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">Ưu đãi</a></li>
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle {{ request()->routeIs('category.show') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Thể loại</a>
                <ul class="dropdown-menu glass-dropdown border-0 shadow-lg mt-2">
                  @forelse($navCategories as $navCategory)
                    <li>
                      <a class="dropdown-item d-flex justify-content-between align-items-center" href="{{ route('category.show', $navCategory) }}">
                        <span>{{ $navCategory->name }}</span>
                        <span class="badge rounded-pill category-count-badge">{{ $navCategory->movies_count ?? $navCategory->active_movies_count ?? 0 }}</span>
                      </a>
                    </li>
                  @empty
                    <li><span class="dropdown-item text-muted">Chưa có thể loại</span></li>
                  @endforelse
                </ul>
              </li>
            </ul>

            <div class="header-actions d-flex flex-wrap gap-2 align-items-center justify-content-xl-end">
              <button type="button" class="btn btn-theme-toggle" id="themeToggle" aria-label="Chuyển chế độ sáng tối" title="Chuyển chế độ sáng tối">
                <i class="bi bi-moon-stars-fill theme-icon-dark"></i>
                <i class="bi bi-sun-fill theme-icon-light"></i>
                <span class="theme-toggle__label">Sáng/Tối</span>
              </button>

              @auth
                <a class="account-chip" href="{{ route('member.account') }}">
                  <span class="account-chip__avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                  <span>
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ number_format($memberPoints) }} điểm</small>
                  </span>
                </a>
                <form method="POST" action="{{ route('member.logout') }}" class="m-0">
                  @csrf
                  <button class="btn btn-ghost" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Đăng xuất</button>
                </form>
              @else
                <a class="btn btn-ghost" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập</a>
                <a class="btn btn-primary-soft" href="{{ route('member.register') }}"><i class="bi bi-person-plus me-2"></i>Đăng ký</a>
              @endauth

              <a class="btn btn-cinema-primary" href="{{ request()->routeIs('home') ? '#booking-widget' : route('home') . '#booking-widget' }}"><i class="bi bi-ticket-detailed me-2"></i>Đặt vé nhanh</a>
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
      @if($errors->any())
        <div class="container-fluid app-container pt-4">
          <div class="alert app-alert app-alert--error">{{ $errors->first() }}</div>
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
                <strong>{{ $brand }}</strong>
                <small>{{ $cinemaName }}</small>
                <strong>{{ $brand }}</strong>
                <small>{{ $cinemaName }}</small>
              </span>
            </div>
            <div class="footer-socials d-flex gap-2 mt-3">
              <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
              <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
              <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
              <a href="#" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>
            </div>
          </div>
          <div>
            <h3>Đi nhanh</h3>
            <ul>
              <li><a href="{{ $scheduleLink }}">Lịch chiếu hôm nay</a></li>
              <li><a href="{{ route('booking.lookup') }}">Tra cứu booking</a></li>
              <li><a href="{{ route('offers.index') }}">Ưu đãi thành viên</a></li>
              <li><a href="{{ route('news.index') }}">Tin tức điện ảnh</a></li>
            </ul>
          </div>
          <div>
            <h3>Khách hàng</h3>
            <ul>
              <li><a href="{{ route('login') }}">Đăng nhập tài khoản</a></li>
              <li><a href="{{ route('member.register') }}">Đăng ký thành viên</a></li>
              <li><a href="{{ route('cinema.info') }}">Thông tin FPL Cinema</a></li>
              <li><a href="{{ route('support.index') }}">FAQ & hỗ trợ</a></li>
              </span>
            </div>
            <p class="footer-copy">Trải nghiệm đặt vé một rạp tập trung, rõ ràng, dễ thao tác hơn cho cả khách hàng và quản trị viên.</p>
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
              <li><a href="{{ route('home') }}#movie-sections">Lịch chiếu nổi bật</a></li>
              <li><a href="{{ route('news.index') }}">Tin tức điện ảnh</a></li>
              <li><a href="{{ route('offers.index') }}">Ưu đãi thành viên</a></li>
              <li><a href="{{ route('member.account') }}">Tài khoản của tôi</a></li>
            </ul>
          </div>
          <div>
            <h3>Khách hàng thành viên</h3>
            <ul>
              <li>Tích điểm tự động sau khi thanh toán</li>
              <li>Cứ {{ number_format((int) config('loyalty.amount_per_point', 10000)) }}đ = 1 điểm</li>
              <li>Theo dõi lịch sử booking trong tài khoản</li>
              <li>Ưu đãi và tin tức được cập nhật tại một nơi</li>
            </ul>
          </div>
          <div>
            <h3>Liên hệ</h3>
            <ul>
              <li><i class="bi bi-geo-alt me-2"></i>{{ $cinemaAddress }}</li>
              <li><i class="bi bi-telephone me-2"></i>{{ $cinemaHotline }}</li>
              <li><i class="bi bi-envelope me-2"></i>{{ $cinemaEmail }}</li>
              <li><i class="bi bi-geo-alt me-2"></i>{{ $cinemaAddress }}</li>
              <li><i class="bi bi-telephone me-2"></i>{{ $cinemaHotline }}</li>
              <li><i class="bi bi-envelope me-2"></i>{{ $cinemaEmail }}</li>

              <li><i class="bi bi-clock me-2"></i>07:00 - 23:00 mỗi ngày</li>
            </ul>
          </div>
        </div>
      </div>
    </footer>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    (function () {
      var toggle = document.getElementById('themeToggle');
      if (!toggle) return;

      var root = document.documentElement;
      var label = toggle.querySelector('.theme-toggle__label');

      function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        if (label) {
          label.textContent = theme === 'light' ? 'Chế độ sáng' : 'Chế độ tối';
        }
      }

      applyTheme(root.getAttribute('data-theme') === 'light' ? 'light' : 'dark');

      toggle.addEventListener('click', function () {
        var nextTheme = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme);
        try {
          localStorage.setItem('fpl-theme', nextTheme);
        } catch (e) {}
      });
    })();
  </script>

  @stack('scripts')
</body>
</html>
