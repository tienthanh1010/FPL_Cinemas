@php
    $isActive = fn(string $pattern) => request()->routeIs($pattern) ? 'active' : '';
@endphp

<div class="sidebar d-none d-lg-flex flex-column p-3 bg-white border-end">
    <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark">
        <span class="fs-5 fw-semibold">🎬 CINEMA</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $isActive('admin.dashboard') }}">
                Dashboard
            </a>
        </li>
        <li>
            <a href="{{ route('admin.movies.index') }}" class="nav-link {{ $isActive('admin.movies.*') }}">
                Phim
            </a>
        </li>
        <li>
            <a href="{{ route('admin.movie_versions.index') }}" class="nav-link {{ $isActive('admin.movie_versions.*') }}">
                Phiên bản phim
            </a>
        </li>
        <li>
            <a href="{{ route('admin.chains.index') }}" class="nav-link {{ $isActive('admin.chains.*') }}">
                Chuỗi rạp
            </a>
        </li>
        <li>
            <a href="{{ route('admin.cinemas.index') }}" class="nav-link {{ $isActive('admin.cinemas.*') }}">
                Rạp
            </a>
        </li>
        <li>
            <a href="{{ route('admin.auditoriums.index') }}" class="nav-link {{ $isActive('admin.auditoriums.*') }}">
                Phòng chiếu
            </a>
        </li>
        <li>
            <a href="{{ route('admin.shows.index') }}" class="nav-link {{ $isActive('admin.shows.*') }}">
                Suất chiếu
            </a>
        </li>
    </ul>
    <hr>
    <div class="small text-muted">
        Laravel 12 • PHP 8.2
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="adminSidebarLabel">🎬 CINEMA</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="nav nav-pills flex-column mb-auto gap-1">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $isActive('admin.dashboard') }}">
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.movies.index') }}" class="nav-link {{ $isActive('admin.movies.*') }}">
                    Phim
                </a>
            </li>
            <li>
                <a href="{{ route('admin.movie_versions.index') }}" class="nav-link {{ $isActive('admin.movie_versions.*') }}">
                    Phiên bản phim
                </a>
            </li>
            <li>
                <a href="{{ route('admin.chains.index') }}" class="nav-link {{ $isActive('admin.chains.*') }}">
                    Chuỗi rạp
                </a>
            </li>
            <li>
                <a href="{{ route('admin.cinemas.index') }}" class="nav-link {{ $isActive('admin.cinemas.*') }}">
                    Rạp
                </a>
            </li>
            <li>
                <a href="{{ route('admin.auditoriums.index') }}" class="nav-link {{ $isActive('admin.auditoriums.*') }}">
                    Phòng chiếu
                </a>
            </li>
            <li>
                <a href="{{ route('admin.shows.index') }}" class="nav-link {{ $isActive('admin.shows.*') }}">
                    Suất chiếu
                </a>
            </li>
        </ul>
    </div>
</div>
