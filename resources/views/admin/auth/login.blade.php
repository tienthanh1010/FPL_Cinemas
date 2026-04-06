<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập Admin · FPL Cinemas</title>
    <title>Đăng nhập Admin · FPL Cinemas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(14,165,233,.16), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #172554 55%, #0f172a 100%);
            color: #fff;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .login-shell {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 30px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            box-shadow: 0 30px 70px rgba(15,23,42,.35);
        }
        .login-aside {
            padding: 42px;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
        }
        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
            color: #bfdbfe;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .login-title {
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -.04em;
            margin-bottom: 14px;
        }
        .login-copy {
            color: rgba(226,232,240,.86);
            font-size: 1rem;
            max-width: 420px;
            margin-bottom: 30px;
        }
        .feature-list {
            display: grid;
            gap: 14px;
        }
        .feature-item {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.08);
            color: rgba(255,255,255,.88);
        }
        .login-form-panel {
            background: rgba(255,255,255,.94);
            color: #0f172a;
            padding: 42px;
        }
        .login-form-panel .form-control {
            min-height: 52px;
            border-radius: 16px;
            border: 1px solid #dbe3ee;
        }
        .login-form-panel .form-control:focus {
            border-color: rgba(37,99,235,.5);
            box-shadow: 0 0 0 .22rem rgba(37,99,235,.13);
        }
        .login-form-panel .btn-primary {
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            box-shadow: 0 18px 30px rgba(37,99,235,.18);
        }
        .demo-credentials {
            padding: 16px 18px;
            border-radius: 18px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            color: #1d4ed8;
        }
        @media (max-width: 900px) {
            .login-shell { grid-template-columns: 1fr; }
            .login-aside { display: none; }
            .login-form-panel { padding: 28px 22px; }
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37,99,235,.18), transparent 30%),
                radial-gradient(circle at bottom right, rgba(14,165,233,.16), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #172554 55%, #0f172a 100%);
            color: #fff;
            display: grid;
            place-items: center;
            padding: 24px;
        }
        .login-shell {
            width: min(1040px, 100%);
            display: grid;
            grid-template-columns: 1.1fr .9fr;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 30px;
            overflow: hidden;
            backdrop-filter: blur(20px);
            box-shadow: 0 30px 70px rgba(15,23,42,.35);
        }
        .login-aside {
            padding: 42px;
            background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.02));
        }
        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.12);
            color: #bfdbfe;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .login-title {
            font-size: clamp(2rem, 4vw, 3.2rem);
            line-height: 1.05;
            font-weight: 800;
            letter-spacing: -.04em;
            margin-bottom: 14px;
        }
        .login-copy {
            color: rgba(226,232,240,.86);
            font-size: 1rem;
            max-width: 420px;
            margin-bottom: 30px;
        }
        .feature-list {
            display: grid;
            gap: 14px;
        }
        .feature-item {
            padding: 16px 18px;
            border-radius: 18px;
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.08);
            color: rgba(255,255,255,.88);
        }
        .login-form-panel {
            background: rgba(255,255,255,.94);
            color: #0f172a;
            padding: 42px;
        }
        .login-form-panel .form-control {
            min-height: 52px;
            border-radius: 16px;
            border: 1px solid #dbe3ee;
        }
        .login-form-panel .form-control:focus {
            border-color: rgba(37,99,235,.5);
            box-shadow: 0 0 0 .22rem rgba(37,99,235,.13);
        }
        .login-form-panel .btn-primary {
            min-height: 52px;
            border: 0;
            border-radius: 16px;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb, #0ea5e9);
            box-shadow: 0 18px 30px rgba(37,99,235,.18);
        }
        .demo-credentials {
            padding: 16px 18px;
            border-radius: 18px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
            color: #1d4ed8;
        }
        @media (max-width: 900px) {
            .login-shell { grid-template-columns: 1fr; }
            .login-aside { display: none; }
            .login-form-panel { padding: 28px 22px; }
        }
    </style>
</head>
<body>
<div class="login-shell">
    <div class="login-aside">
        <div class="login-badge"><i class="bi bi-camera-reels-fill"></i> FPL Cinemas Admin</div>
        <div class="login-title">Quản trị hệ thống rạp phim gọn gàng, đẹp mắt và có logic hơn.</div>
        <p class="login-copy">
            Khu vực này dùng để quản lý phim, phiên bản phim, thể loại, chuỗi rạp, rạp, phòng chiếu và suất chiếu.
        </p>

        <div class="feature-list">
            <div class="feature-item">
                <strong class="d-block mb-1">Quản lý dữ liệu liên kết</strong>
                <span>Phim có thể gắn thể loại, đạo diễn, biên kịch, diễn viên và nhiều phiên bản chiếu.</span>
            </div>
            <div class="feature-item">
                <strong class="d-block mb-1">Kiểm soát nhập liệu</strong>
                <span>Giới hạn link trailer theo nền tảng hỗ trợ và kiểm tra poster đúng định dạng ảnh.</span>
            </div>
            <div class="feature-item">
                <strong class="d-block mb-1">Giao diện rõ ràng</strong>
                <span>Tối ưu cho thao tác CRUD, xem nhanh, lọc và chỉnh sửa dữ liệu trên Laragon / MySQL.</span>
            </div>
        </div>
    </div>

    <div class="login-form-panel">
        <div class="mb-4">
            <div class="text-uppercase text-primary fw-bold small mb-2">Chào mừng quay lại</div>
            <h1 class="h3 fw-bold mb-2">Đăng nhập quản trị</h1>
            <p class="text-secondary mb-0">Sử dụng tài khoản admin để truy cập bảng điều khiển.</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-warning border-0 rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-warning border-0 rounded-4 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@cinema.local" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="admin@cinema.local" required autofocus>
            </div>
            <div class="mb-4">
                <label class="form-label fw-semibold">Mật khẩu</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập vào Admin
            </button>
        </form>

        <div class="demo-credentials mt-4">
            <div class="fw-semibold mb-1">Tài khoản demo hiện có</div>
            <div><span class="font-monospace">admin@cinema.local</span> / <span class="font-monospace">admin123</span></div>
            <button class="btn btn-primary w-100" type="submit">
                <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập vào Admin
            </button>
        </form>

        <div class="demo-credentials mt-4">
            <div class="fw-semibold mb-1">Tài khoản demo hiện có</div>
            <div><span class="font-monospace">admin@cinema.local</span> / <span class="font-monospace">admin123</span></div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
