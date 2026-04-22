<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') · FPL Cinemas</title>
    <title>@yield('title', 'Admin') · FPL Cinemas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --admin-bg: #f4f7fb;
            --admin-surface: rgba(255, 255, 255, .92);
            --admin-surface-strong: #ffffff;
            --admin-border: rgba(15, 23, 42, .08);
            --admin-text: #0f172a;
            --admin-muted: #64748b;
            --admin-primary: #2563eb;
            --admin-primary-strong: #1d4ed8;
            --admin-success: #059669;
            --admin-warning: #d97706;
            --admin-danger: #dc2626;
            --admin-shadow: 0 18px 55px rgba(15, 23, 42, .08);
            --admin-shadow-soft: 0 10px 30px rgba(15, 23, 42, .05);
            --admin-radius: 24px;
            --admin-radius-sm: 18px;
        }

        body.admin-body {
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--admin-text);
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .14), transparent 26%),
                radial-gradient(circle at top right, rgba(14, 165, 233, .1), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, var(--admin-bg) 100%);
        }

        a { text-decoration: none; }

        .admin-shell {
            min-height: 100vh;
            display: flex;
        }

        .admin-sidebar {
            width: 300px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0f172a 0%, #172554 100%);
            color: #e2e8f0;
            padding: 28px 20px;
            position: sticky;
            top: 0;
            align-self: flex-start;
            box-shadow: 16px 0 48px rgba(15, 23, 42, .18);
        }

        .admin-brand {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            color: #fff;
        }

        .admin-brand-badge {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: linear-gradient(135deg, rgba(59, 130, 246, .95), rgba(14, 165, 233, .9));
            box-shadow: 0 16px 35px rgba(59, 130, 246, .28);
            font-size: 1.25rem;
        }

        .admin-brand small {
            display: block;
            color: rgba(226, 232, 240, .7);
            letter-spacing: .06em;
            text-transform: uppercase;
            font-size: .72rem;
        }

        .admin-brand strong {
            display: block;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -.02em;
        }

        .sidebar-groups {
            display: grid;
            gap: 14px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .sidebar-group-card {
            border-radius: 18px;
            padding: 8px 8px 10px;
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255, 255, 255, .06);
        }

        .sidebar-section-title {
            margin: 10px 12px 8px;
            color: rgba(226, 232, 240, .62);
            text-transform: uppercase;
            letter-spacing: .08em;
            font-size: .68rem;
            font-weight: 800;
        }

        .admin-sidebar .nav {
            gap: 6px;
        }

        .admin-sidebar .nav-link {
            color: rgba(226, 232, 240, .8);
            border-radius: 16px;
            padding: 13px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
            transition: all .2s ease;
        }

        .admin-sidebar .nav-link i {
            font-size: 1rem;
            opacity: .9;
        }

        .nav-icon-wrap {
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, .08);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.05);
        }

        .offcanvas .nav-link {
            color: rgba(226, 232, 240, .85);
            border-radius: 16px;
            padding: 13px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 600;
        }

        .offcanvas .sidebar-group-card {
            background: rgba(255, 255, 255, .04);
            border: 1px solid rgba(255,255,255,.06);
        }

        .offcanvas .nav-link.active,
        .offcanvas .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, .1);
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            color: #fff;
            background: rgba(255, 255, 255, .1);
            transform: translateX(3px);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.08);
        }

        .admin-sidebar-footer {
            margin-top: auto;
            padding: 18px 16px;
            border-radius: 20px;
            background: rgba(255, 255, 255, .06);
            color: rgba(226, 232, 240, .75);
            font-size: .9rem;
        }

        .admin-content {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 22px 28px;
            position: sticky;
            top: 0;
            z-index: 1010;
            backdrop-filter: blur(18px);
            background: rgba(244, 247, 251, .82);
            border-bottom: 1px solid rgba(15, 23, 42, .06);
        }

        .topbar-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .topbar-subtitle {
            margin: 0 0 4px;
            color: var(--admin-muted);
            font-size: .88rem;
        }

        .topbar-meta {
            display: flex;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .admin-user-chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            border-radius: 18px;
            background: rgba(255,255,255,.82);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow-soft);
        }

        .admin-user-chip .avatar {
            width: 38px;
            height: 38px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .page-body {
            padding: 28px;
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
            margin-bottom: 22px;
        }

        .page-header .eyebrow {
            margin: 0 0 6px;
            color: var(--admin-primary);
            font-weight: 700;
            font-size: .8rem;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .page-header h2,
        .page-header h1 {
            margin: 0;
            font-size: 1.55rem;
            font-weight: 800;
            letter-spacing: -.03em;
        }

        .page-header p {
            margin: 6px 0 0;
            color: var(--admin-muted);
        }

        .card,
        .panel-card,
        .soft-card {
            border: 1px solid rgba(255,255,255,.6);
            background: var(--admin-surface);
            border-radius: var(--admin-radius);
            box-shadow: var(--admin-shadow);
            overflow: hidden;
        }

        .soft-card {
            background: linear-gradient(180deg, rgba(255,255,255,.95), rgba(255,255,255,.82));
        }

        .card-header,
        .panel-card-header {
            background: transparent;
            border-bottom: 1px solid var(--admin-border);
            padding: 20px 24px;
        }

        .card-body,
        .panel-card-body {
            padding: 24px;
        }

        .metric-card {
            position: relative;
            overflow: hidden;
        }

        .metric-card::after {
            content: "";
            position: absolute;
            inset: auto -60px -60px auto;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: rgba(37, 99, 235, .09);
        }

        .metric-label {
            color: var(--admin-muted);
            font-weight: 700;
            font-size: .82rem;
            text-transform: uppercase;
            letter-spacing: .07em;
        }

        .metric-value {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -.04em;
            margin-top: 8px;
        }

        .metric-caption {
            margin-top: 6px;
            color: var(--admin-muted);
            font-size: .92rem;
        }

        .toolbar-card {
            margin-bottom: 18px;
        }

        .toolbar-card .form-control,
        .toolbar-card .form-select {
            background: #fff;
        }

        .table-responsive {
            overflow: auto;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-hover-bg: rgba(37, 99, 235, .03);
            margin-bottom: 0;
        }

        .table > :not(caption) > * > * {
            padding: 1rem 1.05rem;
            border-bottom-color: rgba(15, 23, 42, .06);
            vertical-align: middle;
        }

        .table thead th {
            font-size: .76rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--admin-muted);
            font-weight: 800;
            background: rgba(248, 250, 252, .82);
            border-bottom-width: 1px;
        }

        .table tbody tr:hover {
            transform: translateY(-1px);
        }

        .list-primary {
            font-weight: 700;
            color: var(--admin-text);
        }

        .list-secondary {
            color: var(--admin-muted);
            font-size: .92rem;
            margin-top: 2px;
        }

        .poster-thumb {
            width: 56px;
            height: 82px;
            border-radius: 16px;
            object-fit: cover;
            background: linear-gradient(135deg, #dbeafe, #e2e8f0);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .12);
        }

        .inline-chip,
        .badge {
            border-radius: 999px;
            padding: .48rem .8rem;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .badge-soft-primary {
            background: rgba(37, 99, 235, .1);
            color: var(--admin-primary);
        }

        .badge-soft-success {
            background: rgba(5, 150, 105, .12);
            color: var(--admin-success);
        }

        .badge-soft-warning {
            background: rgba(217, 119, 6, .12);
            color: var(--admin-warning);
        }

        .badge-soft-danger {
            background: rgba(220, 38, 38, .12);
            color: var(--admin-danger);
        }

        .badge-soft-secondary {
            background: rgba(100, 116, 139, .12);
            color: #475569;
        }

        .form-label {
            font-weight: 700;
            margin-bottom: .55rem;
            color: #1e293b;
        }

        .form-control,
        .form-select {
            min-height: 50px;
            border-radius: 16px;
            border: 1px solid #dbe3ee;
            background: rgba(255,255,255,.88);
            box-shadow: inset 0 1px 1px rgba(15,23,42,.02);
            padding-left: 14px;
            padding-right: 14px;
        }

        .form-control:focus,
        .form-select:focus,
        .form-check-input:focus {
            border-color: rgba(37, 99, 235, .45);
            box-shadow: 0 0 0 .22rem rgba(37, 99, 235, .13);
        }

        textarea.form-control {
            min-height: 120px;
        }

        select[multiple].form-select {
            min-height: 170px;
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .form-text {
            color: var(--admin-muted);
        }

        .form-check-input {
            width: 1.15rem;
            height: 1.15rem;
            border-radius: .4rem;
        }

        .section-card {
            border: 1px solid var(--admin-border);
            border-radius: 22px;
            background: rgba(248, 250, 252, .55);
            padding: 22px;
            margin-bottom: 18px;
        }

        .section-card h3 {
            font-size: 1.05rem;
            font-weight: 800;
            margin-bottom: .35rem;
        }

        .section-card p.section-description {
            color: var(--admin-muted);
            margin-bottom: 18px;
        }

        .hint-box {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(37, 99, 235, .07);
            color: #1d4ed8;
            border: 1px solid rgba(37, 99, 235, .08);
        }

        .media-preview-card {
            border: 1px dashed rgba(37, 99, 235, .28);
            border-radius: 22px;
            padding: 18px;
            background: linear-gradient(180deg, rgba(239,246,255,.8), rgba(255,255,255,.9));
            height: 100%;
        }

        .media-preview-card h4 {
            font-size: .92rem;
            font-weight: 800;
            margin-bottom: 14px;
        }

        .media-preview-empty {
            color: var(--admin-muted);
            min-height: 120px;
            display: grid;
            place-items: center;
            text-align: center;
            border-radius: 18px;
            background: rgba(255,255,255,.7);
            border: 1px dashed rgba(148, 163, 184, .4);
        }

        .media-preview-card img {
            width: 100%;
            max-width: 100%;
            border-radius: 18px;
            object-fit: cover;
            max-height: 360px;
        }

        .media-preview-card iframe {
            width: 100%;
            min-height: 220px;
            border: 0;
            border-radius: 18px;
            background: #0f172a;
        }

        .version-grid {
            display: grid;
            gap: 14px;
        }

        .version-row {
            padding: 16px;
            border-radius: 20px;
            border: 1px solid rgba(15, 23, 42, .08);
            background: rgba(255,255,255,.9);
        }

        .btn {
            border-radius: 14px;
            font-weight: 700;
            padding: .72rem 1rem;
        }

        .btn-sm {
            border-radius: 12px;
            padding: .48rem .78rem;
            font-weight: 700;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--admin-primary), #38bdf8);
            border: none;
            box-shadow: 0 14px 26px rgba(37, 99, 235, .18);
        }

        .btn-outline-secondary,
        .btn-outline-primary,
        .btn-outline-danger,
        .btn-outline-dark {
            border-color: rgba(15, 23, 42, .1);
        }

        .btn-light-soft {
            background: rgba(255,255,255,.9);
            border: 1px solid var(--admin-border);
            box-shadow: var(--admin-shadow-soft);
        }

        .alert {
            border: none;
            border-radius: 18px;
            box-shadow: var(--admin-shadow-soft);
            padding: 16px 18px;
        }

        .empty-state {
            padding: 36px 18px;
            text-align: center;
            color: var(--admin-muted);
        }

        .pagination {
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 0;
        }

        .page-link {
            border-radius: 12px !important;
            border: 1px solid rgba(15, 23, 42, .08);
            color: #334155;
            padding: .56rem .82rem;
        }

        .page-item.active .page-link {
            background: linear-gradient(135deg, var(--admin-primary), #38bdf8);
            border-color: transparent;
        }

        @media (max-width: 1199.98px) {
            .admin-sidebar {
                display: none;
            }
        }

        @media (max-width: 767.98px) {
            .topbar,
            .page-body {
                padding-left: 16px;
                padding-right: 16px;
            }

            .page-header h2,
            .page-header h1,
            .topbar-title {
                font-size: 1.25rem;
            }
        }
    </style>
    @stack('styles')
    @stack('styles')
</head>
<body class="admin-body">
<div class="admin-shell">
<body class="admin-body">
<div class="admin-shell">
    @include('admin.partials.sidebar')

    <div class="admin-content">
        <header class="topbar">
            <div class="d-flex align-items-start gap-3">
                <button class="btn btn-light-soft d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="bi bi-list"></i>
    <div class="admin-content">
        <header class="topbar">
            <div class="d-flex align-items-start gap-3">
                <button class="btn btn-light-soft d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    <i class="bi bi-list"></i>
                </button>
                <div>
                    <p class="topbar-subtitle">Bảng điều khiển quản trị rạp phim</p>
                    <h1 class="topbar-title">@yield('title', 'Admin')</h1>
                <div>
                    <p class="topbar-subtitle">Bảng điều khiển quản trị rạp phim</p>
                    <h1 class="topbar-title">@yield('title', 'Admin')</h1>
                </div>
            </div>

            <div class="topbar-meta">
                <div class="admin-user-chip">
                    <span class="avatar">{{ strtoupper(mb_substr($adminUser->name ?? 'A', 0, 1, 'UTF-8')) }}</span>
                    <div>
                        <div class="fw-semibold">{{ $adminUser->name ?? 'Admin' }}</div>
                        <div class="small text-secondary">{{ now()->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger" type="submit">
                        <i class="bi bi-box-arrow-right me-1"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </header>

        <main class="page-body">
            @include('admin.partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
@stack('scripts')
</body>
</html>
