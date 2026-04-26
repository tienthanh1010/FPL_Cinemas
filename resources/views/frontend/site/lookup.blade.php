@extends('frontend.layout')

@section('title', 'Tra cứu booking | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4">
        <span class="section-eyebrow">Tra cứu booking</span>
        <h1>Kiểm tra đơn vé đã đặt</h1>
        <p class="mb-0">Tính năng này đặc biệt cần thiết cho website rạp chiếu phim: khách chưa đăng nhập vẫn có thể tra cứu đơn bằng mã booking và thông tin liên hệ đã dùng khi đặt.</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Nhập thông tin tra cứu</h2>
            <form method="GET" action="{{ route('booking.lookup') }}" class="row g-3">
              <div class="col-12">
                <label class="form-label">Mã booking</label>
                <input type="text" name="booking_code" value="{{ $lookupCode }}" class="form-control" placeholder="Ví dụ: BK20260405NHN1M1" required>
              </div>
              <div class="col-12">
                <label class="form-label">Số điện thoại hoặc email</label>
                <input type="text" name="contact" value="{{ $lookupContact }}" class="form-control" placeholder="Thông tin đã dùng khi đặt vé">
                <div class="form-text text-light-emphasis">Nếu bạn là thành viên đã đăng nhập và booking thuộc tài khoản của bạn, hệ thống có thể tự xác thực.</div>
              </div>
              <div class="col-12 d-grid d-sm-flex gap-2">
                <button class="btn btn-cinema-primary" type="submit"><i class="bi bi-search me-2"></i>Tra cứu ngay</button>
                <a class="btn btn-cinema-secondary" href="{{ route('home') }}#booking-widget">Đặt vé mới</a>
              </div>
            </form>

            @if($lookupError)
              <div class="app-alert app-alert--error mt-4">{{ $lookupError }}</div>
            @endif
          </div>
        </div>

        <div class="col-lg-7">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Kết quả tra cứu</h2>
            @if($booking)
              @php
                $movie = $booking->show?->movieVersion?->movie;
                $seatLabels = $booking->tickets->pluck('seat.label')->filter()->implode(', ');
                $comboSummary = $booking->bookingProducts->map(fn($item) => ($item->product?->name ?: 'Combo') . ' x' . $item->qty)->implode(', ');
                $capturedAmount = (int) $booking->payments->sum('amount');
                $successRefundAmount = (int) $booking->payments->flatMap->refunds->where('status', 'SUCCESS')->sum('amount');
                $canPay = in_array((string) $booking->status, ['PENDING'], true) && (int) $booking->paid_amount < (int) $booking->total_amount;
              @endphp

              <div class="account-kpi-grid mb-4">
                <article class="account-kpi-card">
                  <small>Mã booking</small>
                  <strong>{{ $booking->booking_code }}</strong>
                  <span>Trạng thái {{ $booking->status }}</span>
                </article>
                <article class="account-kpi-card">
                  <small>Tổng tiền</small>
                  <strong>{{ number_format($booking->total_amount) }}đ</strong>
                  <span>Đã thanh toán {{ number_format($booking->paid_amount) }}đ</span>
                </article>
                <article class="account-kpi-card">
                  <small>Thanh toán</small>
                  <strong>{{ number_format($capturedAmount) }}đ</strong>
                  <span>Hoàn thành công {{ number_format($successRefundAmount) }}đ</span>
                </article>
                <article class="account-kpi-card">
                  <small>Điểm dự kiến</small>
                  <strong>{{ number_format(loyalty_preview_points((int) $booking->paid_amount)) }}</strong>
                  <span>Áp dụng cho booking thanh toán thành công</span>
                </article>
              </div>

              <div class="account-info-list site-info-list mb-4">
                <div><span>Phim</span><strong>{{ $movie?->title ?: 'Suất chiếu' }}</strong></div>
                <div><span>Suất chiếu</span><strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong></div>
                <div><span>Rạp / phòng</span><strong>{{ $booking->show?->auditorium?->cinema?->name ?: 'FPL Cinema' }} · {{ $booking->show?->auditorium?->name ?: 'Phòng chiếu' }}</strong></div>
                <div><span>Ghế</span><strong>{{ $seatLabels ?: 'Chưa có thông tin ghế' }}</strong></div>
                <div><span>Combo</span><strong>{{ $comboSummary ?: 'Không có combo' }}</strong></div>
                <div><span>Người đặt</span><strong>{{ $booking->contact_name ?: 'Khách hàng' }} · {{ $booking->contact_phone ?: ($booking->contact_email ?: '—') }}</strong></div>
              </div>

              <div class="related-post-list">
                @forelse($booking->payments as $payment)
                  <div class="related-post-item d-flex justify-content-between align-items-start gap-3">
                    <div>
                      <strong>{{ $payment->provider }} / {{ $payment->method }}</strong>
                      <span>{{ $payment->external_txn_ref ?: 'Chưa có mã giao dịch ngoài hệ thống' }}</span>
                    </div>
                    <span class="mt-0">{{ $payment->status }}</span>
                  </div>
                @empty
                  <div class="related-post-item">
                    <strong>Chưa có giao dịch thanh toán</strong>
                    <span>Booking này chưa ghi nhận payment trong hệ thống.</span>
                  </div>
                @endforelse
              </div>

              @if($canPay)
                <div class="mt-4 d-grid d-sm-flex gap-2">
                  <a class="btn btn-cinema-primary" href="{{ route('booking.payment', $booking->booking_code) }}"><i class="bi bi-credit-card me-2"></i>Tiếp tục thanh toán</a>
                  <a class="btn btn-cinema-secondary" href="{{ route('shows.book', $booking->show_id) }}">Xem lại suất chiếu</a>
                </div>
              @endif
            @else
              <div class="empty-panel h-100 d-flex align-items-center justify-content-center text-center">
                Nhập mã booking để hiển thị chi tiết đơn vé, ghế ngồi, combo, thanh toán và khả năng tiếp tục thanh toán.
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
