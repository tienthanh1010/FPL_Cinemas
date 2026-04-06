@php
    $isActive = fn(string $pattern) => request()->routeIs($pattern) ? 'active' : '';
    $visibleItems = fn(array $items) => collect($items)->filter(fn($item) => \Illuminate\Support\Facades\Route::has($item['route']))->values()->all();

    $navGroups = [
        [
            'title' => 'Điều hành',
            'items' => [
                ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Tổng quan', 'icon' => 'bi-grid-1x2-fill'],
                ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Báo cáo', 'icon' => 'bi-bar-chart-line'],
            ],
        ],
        [
            'title' => 'Nội dung & lịch chiếu',
            'items' => [
                ['route' => 'admin.movies.index', 'pattern' => 'admin.movies.*', 'label' => 'Phim', 'icon' => 'bi-film'],
                ['route' => 'admin.movie_versions.index', 'pattern' => 'admin.movie_versions.*', 'label' => 'Phiên bản phim', 'icon' => 'bi-collection-play'],
                ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Thể loại', 'icon' => 'bi-tags'],
                ['route' => 'admin.auditoriums.index', 'pattern' => 'admin.auditoriums.*', 'label' => 'Phòng chiếu', 'icon' => 'bi-badge-8k'],
                ['route' => 'admin.shows.index', 'pattern' => 'admin.shows.*', 'label' => 'Suất chiếu', 'icon' => 'bi-calendar2-week'],
                ['route' => 'admin.pricing_profiles.index', 'pattern' => 'admin.pricing_profiles.*', 'label' => 'Hồ sơ giá động', 'icon' => 'bi-cash-coin'],
            ],
        ],
        [
            'title' => 'Bán hàng & marketing',
            'items' => [
                ['route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*', 'label' => 'Booking / đơn vé', 'icon' => 'bi-receipt-cutoff'],
                ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Combo bắp nước', 'icon' => 'bi-cup-straw'],
                ['route' => 'admin.payments.index', 'pattern' => 'admin.payments.*', 'label' => 'Thanh toán', 'icon' => 'bi-credit-card-2-front'],
                ['route' => 'admin.refunds.index', 'pattern' => 'admin.refunds.*', 'label' => 'Hoàn tiền', 'icon' => 'bi-arrow-counterclockwise'],
                ['route' => 'admin.inventory.index', 'pattern' => 'admin.inventory.*', 'label' => 'Tồn kho F&B', 'icon' => 'bi-box-seam'],
                ['route' => 'admin.promotions.index', 'pattern' => 'admin.promotions.*', 'label' => 'Khuyến mãi', 'icon' => 'bi-megaphone'],
                ['route' => 'admin.coupons.index', 'pattern' => 'admin.coupons.*', 'label' => 'Voucher', 'icon' => 'bi-ticket-perforated'],
            ],
        ],
        [
            'title' => 'Khách hàng & nhân sự',
            'items' => [
                ['route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'label' => 'Khách hàng', 'icon' => 'bi-people'],
                ['route' => 'admin.staff.index', 'pattern' => 'admin.staff.*', 'label' => 'Nhân sự', 'icon' => 'bi-person-badge'],
                ['route' => 'admin.staff_shifts.index', 'pattern' => 'admin.staff_shifts.*', 'label' => 'Ca làm', 'icon' => 'bi-calendar3'],
            ],
        ],
        [
            'title' => 'Vận hành rạp',
            'items' => [
                ['route' => 'admin.cinemas.index', 'pattern' => 'admin.cinemas.*', 'label' => 'Rạp', 'icon' => 'bi-buildings'],
                ['route' => 'admin.equipment.index', 'pattern' => 'admin.equipment.*', 'label' => 'Thiết bị', 'icon' => 'bi-tools'],
                ['route' => 'admin.maintenance_requests.index', 'pattern' => 'admin.maintenance_requests.*', 'label' => 'Bảo trì', 'icon' => 'bi-wrench-adjustable-circle'],
            ],
        ],
    ];
@endphp

<aside class="admin-sidebar d-none d-xl-flex flex-column">
    <a href="{{ route('admin.dashboard') }}" class="admin-brand">
        <span class="admin-brand-badge"><i class="bi bi-camera-reels-fill"></i></span>
        <span>
            <small>FPL Cinemas</small>
            <strong>Admin Studio</strong>
        </span>
    </a>

    <div class="sidebar-groups">
        @foreach($navGroups as $group)
            @php($items = $visibleItems($group['items']))
            @continue(empty($items))
            <div class="sidebar-group-card">
                <div class="sidebar-section-title">{{ $group['title'] }}</div>
                <ul class="nav flex-column">
                    @foreach($items as $item)
                        <li>
                            <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive($item['pattern']) }}">
                                <span class="nav-icon-wrap"><i class="bi {{ $item['icon'] }}"></i></span>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
</aside>

<div class="offcanvas offcanvas-start text-bg-dark border-0" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
    <div class="offcanvas-header px-4 pt-4">
        <h5 class="offcanvas-title" id="adminSidebarLabel">FPL Cinemas Admin</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body px-3 pb-4">
        <div class="sidebar-groups">
            @foreach($navGroups as $group)
                @php($items = $visibleItems($group['items']))
                @continue(empty($items))
                <div class="sidebar-group-card">
                    <div class="sidebar-section-title">{{ $group['title'] }}</div>
                    <ul class="nav flex-column">
                        @foreach($items as $item)
                            <li>
                                <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive($item['pattern']) }}">
                                    <span class="nav-icon-wrap"><i class="bi {{ $item['icon'] }}"></i></span>
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
</div>
