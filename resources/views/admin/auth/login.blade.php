@extends('frontend.layout')

@section('title', 'Đăng nhập Admin | ' . ($appBrand ?? config('app.name', 'FPL Cinema')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="auth-form-panel glass-panel">
        <div class="mb-4">
          <span class="section-eyebrow">Chào mừng quay lại</span>
          <h2 class="mb-2">Đăng nhập quản trị</h2>
          <p class="text-muted mb-0">Dùng tài khoản admin để truy cập khu vực quản trị FPL Cinema.</p>
        </div>

        @if ($errors->any())
          <div class="alert alert-warning border-0 rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}">
          @csrf
          <div class="form-field mb-3">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="form-control cinema-input" placeholder="admin@cinema.local" required autofocus>
          </div>
          <div class="form-field mb-4">
            <label>Mật khẩu</label>
            <input type="password" name="password" class="form-control cinema-input" placeholder="••••••••" required>
          </div>
          <button class="btn btn-cinema-primary w-100" type="submit">
            <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập vào Admin
          </button>
        </form>

        <div class="mt-4 p-3 rounded-4" style="background: rgba(56,189,248,.12); border: 1px solid rgba(56,189,248,.25);">
          <div class="fw-semibold mb-1">Tài khoản demo hiện có</div>
          <div><span class="font-monospace">admin@cinema.local</span> / <span class="font-monospace">admin123</span></div>
        </div>
      </div>
    </div>
  </section>
@endsection
