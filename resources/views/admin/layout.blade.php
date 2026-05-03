<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin') - CINEMA</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7f7fb; }
        .sidebar { width: 260px; min-height: 100vh; }
        .sidebar a { text-decoration: none; }
        .sidebar .nav-link.active { background: rgba(13,110,253,.12); color: #0d6efd; }
        .content { min-height: 100vh; }
    </style>
</head>
<body>
<div class="d-flex">
    @include('admin.partials.sidebar')

    <div class="flex-grow-1 content">
        <nav class="navbar navbar-expand-lg bg-white border-bottom">
            <div class="container-fluid">
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">
                    ☰
                </button>
                <span class="navbar-brand ms-2">Admin</span>

                <div class="ms-auto d-flex align-items-center gap-3">
                    <span class="text-muted small">Xin chào, <strong>{{ $adminUser->name ?? 'Admin' }}</strong></span>
                    <form method="POST" action="{{ route('admin.logout') }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-danger">Đăng xuất</button>
                    </form>
                </div>
            </div>
        </nav>

        <main class="container-fluid p-4">
            @include('admin.partials.flash')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
