<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập Admin | {{ $appBrand ?? config('app.name', 'FPL Cinema') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        :root {
            color-scheme: dark;
            --bg-1: #020817;
            --bg-2: #0f2a44;
            --card: rgba(15, 23, 42, .92);
            --line: rgba(148, 163, 184, .22);
            --text: #e5eefb;
            --muted: #9fb0c7;
            --accent: #fb923c;
            --accent-2: #38bdf8;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 18% 18%, rgba(56, 189, 248, .18), transparent 30%),
                radial-gradient(circle at 80% 82%, rgba(251, 146, 60, .16), transparent 26%),
                linear-gradient(135deg, var(--bg-1), var(--bg-2));
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .admin-login-card {
            width: min(100%, 460px);
            border: 1px solid var(--line);
            border-radius: 28px;
            background: var(--card);
            box-shadow: 0 26px 80px rgba(0, 0, 0, .42);
            padding: 34px;
        }

        .brand-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(56, 189, 248, .28);
            border-radius: 999px;
            padding: 8px 14px;
            background: rgba(56, 189, 248, .1);
            color: #bae6fd;
            font-weight: 800;
            margin-bottom: 22px;
        }

        .eyebrow {
            color: var(--accent-2);
            text-transform: uppercase;
            letter-spacing: .16em;
            font-weight: 800;
            font-size: .78rem;
            margin: 0 0 10px;
        }

        h1 {
            font-size: clamp(1.75rem, 4vw, 2.35rem);
            line-height: 1.1;
            margin: 0 0 10px;
        }

        .subtitle {
            color: var(--muted);
            margin: 0 0 28px;
        }

        .alert {
            border-radius: 18px;
            border: 1px solid rgba(251, 191, 36, .32);
            background: rgba(251, 191, 36, .12);
            color: #fde68a;
            padding: 14px 16px;
            margin-bottom: 18px;
            font-weight: 700;
        }

        label {
            display: block;
            font-weight: 800;
            margin: 0 0 8px;
        }

        .field { margin-bottom: 18px; }

        .control {
            width: 100%;
            min-height: 54px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(15, 23, 42, .75);
            color: var(--text);
            padding: 0 16px;
            font: inherit;
            outline: none;
            transition: border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        .control::placeholder { color: #64748b; }

        .control:focus {
            border-color: rgba(56, 189, 248, .82);
            box-shadow: 0 0 0 4px rgba(56, 189, 248, .16);
            background: rgba(15, 23, 42, .95);
        }

        .btn-login {
            width: 100%;
            min-height: 56px;
            border: 0;
            border-radius: 18px;
            background: linear-gradient(135deg, #fb923c, #f97316);
            color: #111827;
            font-weight: 900;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 18px 42px rgba(249, 115, 22, .28);
        }

        .btn-login:hover { filter: brightness(1.04); }
    </style>
</head>
<body>
    <main class="admin-login-card" aria-label="Đăng nhập quản trị">
        <div class="brand-pill"><i class="bi bi-camera-reels-fill"></i> FPL Cinemas Admin</div>
        <p class="eyebrow">Chào mừng quay lại</p>
        <h1>Đăng nhập quản trị</h1>
        <p class="subtitle">Dùng tài khoản admin để truy cập khu vực quản trị.</p>

        @if ($errors->any())
            <div class="alert" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" class="control" placeholder="admin@cinema.local" required autofocus>
            </div>
            <div class="field">
                <label for="password">Mật khẩu</label>
                <input id="password" type="password" name="password" class="control" placeholder="••••••••" required>
            </div>
            <button class="btn-login" type="submit">
                <i class="bi bi-box-arrow-in-right"></i> Đăng nhập vào Admin
            </button>
        </form>
    </main>
</body>
</html>
