@extends('frontend.layout')

@section('title', 'Đăng nhập | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="auth-shell">
        <div class="auth-side glass-panel">
          <span class="section-eyebrow">Đăng nhập</span>
          <h1>Vào tài khoản để đặt vé nhanh hơn</h1>
          <p>Khách hàng thành viên có thể theo dõi lịch sử booking, điểm tích luỹ và nhận ưu đãi riêng. Nếu dùng đúng tài khoản admin, hệ thống sẽ chuyển bạn thẳng sang trang quản trị.</p>

          <div class="auth-feature-list mt-4">
            <div>
              <i class="bi bi-shield-check"></i>
              <div>
                <strong>Phân quyền rõ ràng</strong>
                <p>Tài khoản admin và tài khoản người dùng được điều hướng đúng khu vực sau khi đăng nhập.</p>
              </div>
            </div>
            <div>
              <i class="bi bi-stars"></i>
              <div>
                <strong>Tích điểm tự động</strong>
                <p>Mỗi booking thanh toán thành công sẽ tự cộng điểm cho tài khoản thành viên.</p>
              </div>
            </div>
            <div>
              <i class="bi bi-ticket-detailed"></i>
              <div>
                <strong>Giữ booking gọn hơn</strong>
                <p>Thông tin khách hàng sẽ được điền sẵn ở bước đặt vé khi bạn đã đăng nhập.</p>
              </div>
            </div>
          </div>
        </div>

        <div class="auth-form-panel glass-panel">
          <div class="mb-4">
            <span class="section-eyebrow">Chào mừng quay lại</span>
            <h2 class="mb-2">Đăng nhập tài khoản</h2>
            <p class="text-muted mb-0">Dùng email và mật khẩu của bạn để tiếp tục.</p>
          </div>

          <form method="POST" action="{{ route('member.login.submit') }}">
            @csrf
            <div class="form-field mb-3">
              <label>Email</label>
              <input type="email" name="email" value="{{ old('email') }}" class="form-control cinema-input" placeholder="name@example.com" required autofocus>
            </div>
            <div class="form-field mb-3">
              <label>Mật khẩu</label>
              <input type="password" name="password" class="form-control cinema-input" placeholder="••••••••" required>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
              <div class="form-check m-0">
                <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberLogin" @checked(old('remember'))>
                <label class="form-check-label" for="rememberLogin">Ghi nhớ đăng nhập</label>
              </div>
              <a href="{{ route('member.register') }}" class="content-card__link">Chưa có tài khoản? Đăng ký</a>
            </div>
            <button class="btn btn-cinema-primary w-100" type="submit">
              <i class="bi bi-box-arrow-in-right me-2"></i>Đăng nhập
            </button>
          </form>
        </div>
      </div>
    </div>
  </section>
@endsection
