@extends('frontend.layout')

@section('title', 'Đăng ký tài khoản | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="auth-shell auth-shell--register">
        <div class="auth-side glass-panel">
          <span class="section-eyebrow">Đăng ký thành viên</span>
          <h1>Tạo tài khoản để lưu booking và tích điểm</h1>
          <p>Sau khi đăng ký, bạn có thể đặt vé bằng tài khoản cá nhân, xem lịch sử giao dịch và nhận điểm tích luỹ tự động sau mỗi đơn thành công.</p>

          <div class="auth-stat-grid mt-4">
            <div class="success-card">
              <span>Tích điểm</span>
              <strong>Cứ {{ number_format((int) config('loyalty.amount_per_point', 10000)) }}đ = 1 điểm</strong>
            </div>
            <div class="success-card">
              <span>Lịch sử booking</span>
              <strong>Theo dõi ngay trong tài khoản</strong>
            </div>
            <div class="success-card">
              <span>Ưu đãi riêng</span>
              <strong>Nhận tin tức &amp; khuyến mãi sớm</strong>
            </div>
          </div>
        </div>

        <div class="auth-form-panel glass-panel">
          <div class="mb-4">
            <span class="section-eyebrow">Tạo tài khoản mới</span>
            <h2 class="mb-2">Đăng ký thành viên</h2>
            <p class="text-muted mb-0">Chỉ mất một phút để sẵn sàng cho các lần đặt vé tiếp theo.</p>
          </div>

          <form method="POST" action="{{ route('member.register.submit') }}">
            @csrf
            <div class="row g-3">
              <div class="col-12">
                <div class="form-field">
                  <label>Họ và tên</label>
                  <input type="text" name="name" value="{{ old('name') }}" class="form-control cinema-input" placeholder="Nguyễn Văn A" required autofocus>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-field">
                  <label>Email</label>
                  <input type="email" name="email" value="{{ old('email') }}" class="form-control cinema-input" placeholder="name@example.com" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-field">
                  <label>Số điện thoại</label>
                  <input type="text" name="phone" value="{{ old('phone') }}" class="form-control cinema-input" placeholder="0900 000 000">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-field">
                  <label>Mật khẩu</label>
                  <input type="password" name="password" class="form-control cinema-input" placeholder="Tối thiểu 6 ký tự" required>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-field">
                  <label>Xác nhận mật khẩu</label>
                  <input type="password" name="password_confirmation" class="form-control cinema-input" placeholder="Nhập lại mật khẩu" required>
                </div>
              </div>
            </div>
            <div class="d-flex flex-wrap justify-content-between gap-3 mt-4 align-items-center">
              <a href="{{ route('login') }}" class="content-card__link">Đã có tài khoản? Đăng nhập</a>
              <button class="btn btn-cinema-primary" type="submit"><i class="bi bi-person-plus me-2"></i>Tạo tài khoản</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
@endsection
