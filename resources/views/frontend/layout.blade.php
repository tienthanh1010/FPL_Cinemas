@php
  $brand = $appBrand ?? $primaryCinema?->name ?? config('app.name', 'FPL Cinema');
  $cinemaName = $primaryCinema?->name ?: $brand;
  $cinemaHotline = $primaryCinema?->phone ?: '1900 6868';
  $cinemaEmail = $primaryCinema?->email ?: 'support@fplcinema.local';
  $cinemaAddress = collect([
      $primaryCinema?->address_line,
      $primaryCinema?->ward,
      $primaryCinema?->district,
      $primaryCinema?->province,
  ])->filter()->implode(', ');
  $cinemaAddress = $cinemaAddress !== '' ? $cinemaAddress : 'Hà Nội, Việt Nam';
  $memberPoints = (int) ($authCustomer?->loyaltyAccount?->points_balance ?? 0);
  $scheduleLink = request()->routeIs('home') ? '#movie-sections' : route('home') . '#movie-sections';
  $scheduleActive = request()->routeIs('home', 'movies.showtimes', 'shows.book', 'booking.payment', 'booking.success');
@endphp
<!doctype html>
<html lang="vi" data-theme="dark">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
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
        var systemPrefersLight = window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches;
        var theme = savedTheme === 'light' || savedTheme === 'dark'
          ? savedTheme
          : (systemPrefersLight ? 'light' : 'dark');
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
        </div>
        <div class="d-flex align-items-center gap-3 small topbar-links flex-wrap">
          <a href="{{ route('support.index') }}"><i class="bi bi-life-preserver me-1"></i>Hỗ trợ</a>
        </div>
      </div>
    </div>

    <header class="main-header sticky-top">
      <div class="container-fluid app-container">
        <nav class="navbar navbar-expand-lg py-3">
          <a class="navbar-brand brand-mark" href="{{ route('home') }}" aria-label="{{ $brand }}">
            <span class="brand-mark__icon"><i class="bi bi-play-circle-fill"></i></span>
            <span>
              <strong>{{ $brand }}</strong>
              <small>{{ $cinemaAddress }}</small>
            </span>
          </a>

          <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavbar" aria-controls="siteNavbar" aria-expanded="false" aria-label="Mở menu">
            <i class="bi bi-list fs-2"></i>
          </button>

          <div class="collapse navbar-collapse" id="siteNavbar">
            <ul class="navbar-nav mx-auto align-items-lg-center gap-lg-2">
              <li class="nav-item"><a class="nav-link {{ $scheduleActive ? 'active' : '' }}" href="{{ $scheduleLink }}">Lịch chiếu</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}">Tin tức</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('offers.*') ? 'active' : '' }}" href="{{ route('offers.index') }}">Ưu đãi</a></li>
              <li class="nav-item"><a class="nav-link {{ request()->routeIs('booking.lookup') ? 'active' : '' }}" href="{{ route('booking.lookup') }}">Tra cứu vé</a></li>
            </ul>

            <div class="header-actions d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
              <button type="button"
                      class="btn btn-theme-toggle"
                      id="themeToggle"
                      aria-label="Chuyển giao diện"
                      aria-pressed="false"
                      title="Chuyển giao diện">
                <i class="bi bi-moon-stars-fill theme-icon-dark" aria-hidden="true"></i>
                <i class="bi bi-brightness-high-fill theme-icon-light" aria-hidden="true"></i>
                <span class="visually-hidden">Chuyển giao diện sáng tối</span>
              </button>

              @auth
                <a class="account-chip" href="{{ route('member.account') }}">
                  <span class="account-chip__avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                  <span class="account-chip__info">
                    <strong>{{ auth()->user()->name }}</strong>
                    <small>{{ number_format($memberPoints) }} điểm</small>
                  </span>
                </a>
                <form method="POST" action="{{ route('member.logout') }}" class="m-0">
                  @csrf
                  <button class="btn btn-ghost header-icon-btn" type="submit" title="Đăng xuất" aria-label="Đăng xuất">
                    <i class="bi bi-box-arrow-right"></i>
                  </button>
                </form>
              @else
                <a class="btn btn-primary-soft header-account-btn" href="{{ route('login') }}">
                  <i class="bi bi-person-circle me-2"></i>Tài khoản
                </a>
              @endauth
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
            </ul>
          </div>
          <div>
            <h3>Liên hệ</h3>
            <ul>
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

      function setButtonState(theme) {
        var nextThemeLabel = theme === 'light' ? 'Chuyển sang chế độ tối' : 'Chuyển sang chế độ sáng';
        toggle.setAttribute('aria-pressed', theme === 'light' ? 'true' : 'false');
        toggle.setAttribute('title', nextThemeLabel);
        toggle.setAttribute('aria-label', nextThemeLabel);
      }

      function applyTheme(theme, persist) {
        root.setAttribute('data-theme', theme);
        setButtonState(theme);
        if (persist) {
          try {
            localStorage.setItem('fpl-theme', theme);
          } catch (e) {}
        }
      }

      applyTheme(root.getAttribute('data-theme') === 'light' ? 'light' : 'dark', false);

      toggle.addEventListener('click', function () {
        var nextTheme = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        applyTheme(nextTheme, true);
      });
    })();
  </script>
  @stack('scripts')
</body>
</html>
