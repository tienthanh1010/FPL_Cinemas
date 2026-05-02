@php
    $isActive = fn(string $pattern) => request()->routeIs($pattern) ? 'active' : '';
    $canViewItem = function (array $item) use ($adminUser) {
        if (!empty($item['permission']) && !$adminUser->hasPermission($item['permission'])) {
            return false;
        }

        return \Illuminate\Support\Facades\Route::has($item['route']);
    };

    $visibleItems = fn(array $items) => collect($items)->filter(fn($item) => $canViewItem($item))->values()->all();

    $navGroups = [
        [
            'title' => 'Điều hành',
            'items' => [
                ['route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'label' => 'Tổng quan', 'icon' => 'bi-grid-1x2-fill', 'permission' => 'dashboard.view'],
                ['route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'label' => 'Báo cáo', 'icon' => 'bi-bar-chart-line', 'permission' => 'reports.view'],
            ],
        ],
        [
            'title' => 'Nội dung & lịch chiếu',
            'items' => [
                ['route' => 'admin.movies.index', 'pattern' => 'admin.movies.*', 'label' => 'Phim', 'icon' => 'bi-film', 'permission' => 'catalog.manage'],
                ['route' => 'admin.movie_versions.index', 'pattern' => 'admin.movie_versions.*', 'label' => 'Phiên bản phim', 'icon' => 'bi-collection-play', 'permission' => 'catalog.manage'],
                ['route' => 'admin.categories.index', 'pattern' => 'admin.categories.*', 'label' => 'Thể loại', 'icon' => 'bi-tags', 'permission' => 'catalog.manage'],
                ['route' => 'admin.auditoriums.index', 'pattern' => 'admin.auditoriums.*', 'label' => 'Phòng chiếu', 'icon' => 'bi-badge-8k', 'permission' => 'showtimes.manage'],
                ['route' => 'admin.shows.index', 'pattern' => 'admin.shows.*', 'label' => 'Suất chiếu', 'icon' => 'bi-calendar2-week', 'permission' => 'showtimes.manage'],
                ['route' => 'admin.pricing_profiles.index', 'pattern' => 'admin.pricing_profiles.*', 'label' => 'Hồ sơ giá động', 'icon' => 'bi-cash-coin', 'permission' => 'showtimes.manage'],
            ],
        ],
        [
            'title' => 'Bán hàng & marketing',
            'items' => [
                ['route' => 'admin.bookings.index', 'pattern' => 'admin.bookings.*', 'label' => 'Booking / đơn vé', 'icon' => 'bi-receipt-cutoff', 'permission' => 'bookings.manage'],
                ['route' => 'admin.tickets.index', 'pattern' => 'admin.tickets.*', 'label' => 'Soát vé / check-in', 'icon' => 'bi-qr-code-scan', 'permission' => 'tickets.checkin'],
                ['route' => 'admin.products.index', 'pattern' => 'admin.products.*', 'label' => 'Combo bắp nước', 'icon' => 'bi-cup-straw', 'permission' => 'fnb.manage'],
                ['route' => 'admin.payments.index', 'pattern' => 'admin.payments.*', 'label' => 'Thanh toán', 'icon' => 'bi-credit-card-2-front', 'permission' => 'payments.manage'],
                ['route' => 'admin.refunds.index', 'pattern' => 'admin.refunds.*', 'label' => 'Hoàn tiền', 'icon' => 'bi-arrow-counterclockwise', 'permission' => 'refunds.manage'],
                ['route' => 'admin.inventory.index', 'pattern' => 'admin.inventory.*', 'label' => 'Tồn kho F&B', 'icon' => 'bi-box-seam', 'permission' => 'fnb.manage'],
                ['route' => 'admin.inventory.movements', 'pattern' => 'admin.inventory.movements', 'label' => 'Lịch sử nhập/xuất', 'icon' => 'bi-arrow-left-right', 'permission' => 'fnb.manage'],
                ['route' => 'admin.purchase_orders.index', 'pattern' => 'admin.purchase_orders.*', 'label' => 'Nhập hàng F&B', 'icon' => 'bi-box-arrow-in-down', 'permission' => 'fnb.manage'],
                ['route' => 'admin.suppliers.index', 'pattern' => 'admin.suppliers.*', 'label' => 'Nhà cung cấp', 'icon' => 'bi-truck', 'permission' => 'fnb.manage'],
                ['route' => 'admin.promotions.index', 'pattern' => 'admin.promotions.*', 'label' => 'Khuyến mãi', 'icon' => 'bi-megaphone', 'permission' => 'marketing.manage'],
                ['route' => 'admin.coupons.index', 'pattern' => 'admin.coupons.*', 'label' => 'Voucher', 'icon' => 'bi-ticket-perforated', 'permission' => 'marketing.manage'],
                ['route' => 'admin.content_posts.index', 'pattern' => 'admin.content_posts.*', 'label' => 'Tin tức & ưu đãi', 'icon' => 'bi-newspaper', 'permission' => 'marketing.manage'],
            ],
        ],
        [
            'title' => 'Khách hàng & nhân sự',
            'items' => [
                ['route' => 'admin.customers.index', 'pattern' => 'admin.customers.*', 'label' => 'Khách hàng', 'icon' => 'bi-people', 'permission' => 'customers.manage'],
                ['route' => 'admin.staff.index', 'pattern' => 'admin.staff.*', 'label' => 'Nhân sự', 'icon' => 'bi-person-badge', 'permission' => 'staff.manage'],
                ['route' => 'admin.staff_shifts.index', 'pattern' => 'admin.staff_shifts.*', 'label' => 'Ca làm', 'icon' => 'bi-calendar3', 'permission' => 'staff.manage'],
                ['route' => 'admin.admin_users.index', 'pattern' => 'admin.admin_users.*', 'label' => 'Tài khoản admin', 'icon' => 'bi-shield-lock', 'permission' => 'admin_users.manage'],
            ],
        ],
        [
            'title' => 'Vận hành rạp',
            'items' => [
                ['route' => 'admin.cinemas.index', 'pattern' => 'admin.cinemas.*', 'label' => (($singleCinemaMode ?? false) ? 'Thông tin rạp' : 'Rạp'), 'icon' => 'bi-buildings', 'permission' => 'showtimes.manage'],
                ['route' => 'admin.equipment.index', 'pattern' => 'admin.equipment.*', 'label' => 'Thiết bị', 'icon' => 'bi-tools', 'permission' => 'operations.manage'],
                ['route' => 'admin.maintenance_requests.index', 'pattern' => 'admin.maintenance_requests.*', 'label' => 'Bảo trì', 'icon' => 'bi-wrench-adjustable-circle', 'permission' => 'operations.manage'],
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
