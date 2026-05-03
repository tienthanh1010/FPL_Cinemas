@extends('frontend.layout')

@section('title', 'Tài khoản của tôi | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  @php
    $loyaltyAccount = $customer->loyaltyAccount;
    $tierName = $loyaltyAccount?->tier?->name ?: 'Member';
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel account-hero mb-4">
        <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
          <div>
            <span class="section-eyebrow">Tài khoản thành viên</span>
            <h1 class="mb-2">{{ $customer->full_name ?: auth()->user()->name }}</h1>
            <p class="mb-0">Quản lý thông tin cá nhân, lịch sử booking và điểm tích luỹ trong cùng một màn hình.</p>
          </div>
          <div class="account-tier-chip">
            <i class="bi bi-stars"></i>
            <span>{{ $tierName }}</span>
          </div>
        </div>
      </div>

      <div class="account-kpi-grid mb-4">
        <div class="success-card">
          <span>Điểm hiện có</span>
          <strong>{{ number_format((int) ($loyaltyAccount?->points_balance ?? 0)) }}</strong>
        </div>
        <div class="success-card">
          <span>Tổng điểm tích luỹ</span>
          <strong>{{ number_format((int) ($loyaltyAccount?->lifetime_points ?? 0)) }}</strong>
        </div>
        <div class="success-card">
          <span>Tổng booking</span>
          <strong>{{ number_format((int) $summary['total_bookings']) }}</strong>
        </div>
        <div class="success-card">
          <span>Tổng chi tiêu</span>
          <strong>{{ number_format((int) $summary['total_spent']) }}đ</strong>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-4">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Thông tin thành viên</h2>
            <div class="account-info-list">
              <div><span>Họ tên</span><strong>{{ $customer->full_name ?: auth()->user()->name }}</strong></div>
              <div><span>Email</span><strong>{{ $customer->email ?: auth()->user()->email }}</strong></div>
              <div><span>Điện thoại</span><strong>{{ $customer->phone ?: 'Chưa cập nhật' }}</strong></div>
              <div><span>Hạng thành viên</span><strong>{{ $tierName }}</strong></div>
              <div><span>Trạng thái</span><strong>{{ $customer->account_status }}</strong></div>
            </div>

            <div class="loyalty-note-card mt-4">
              <div class="content-tag">Gợi ý</div>
              <h3>Theo dõi điểm thưởng dễ hơn</h3>
              <p>Mỗi booking thanh toán thành công sẽ được cộng điểm tự động. Khi booking bị hoàn tiền hoặc huỷ, điểm tương ứng cũng sẽ được đồng bộ lại để tránh sai lệch.</p>
            </div>
          </div>
        </div>

        <div class="col-lg-8">
          <div class="glass-panel h-100">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
              <div>
                <h2 class="h4 mb-1">Lịch sử booking</h2>
                <p class="mb-0 text-muted">Các đơn vé gần đây của bạn được cập nhật ở đây.</p>
              </div>
              <a href="{{ route('home') }}#movie-sections" class="btn btn-cinema-secondary">Đặt vé mới</a>
            </div>

            <div class="table-responsive">
              <table class="table app-table align-middle mb-0">
                <thead>
                  <tr>
                    <th>Mã booking</th>
                    <th>Phim / suất chiếu</th>
                    <th>Trạng thái</th>
                    <th>Tổng tiền</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($bookings as $booking)
                    <tr>
                      <td>
                        <div class="fw-semibold">{{ $booking->booking_code }}</div>
                        <div class="text-muted small">{{ optional($booking->created_at)->format('d/m/Y H:i') }}</div>
                      </td>
                      <td>
                        <div class="fw-semibold">{{ $booking->show?->movieVersion?->movie?->title ?: 'Đang cập nhật' }}</div>
                        <div class="text-muted small">{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</div>
                      </td>
                      <td><span class="status-badge">{{ $booking->status }}</span></td>
                      <td>
                        <div class="fw-semibold">{{ number_format((int) $booking->total_amount) }}đ</div>
                        <div class="text-muted small">Đã thanh toán: {{ number_format((int) $booking->paid_amount) }}đ</div>
                      </td>
                      <td class="text-end">
                        <a href="{{ route('booking.success', $booking->booking_code) }}" class="btn btn-sm btn-cinema-secondary">Xem chi tiết</a>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="5" class="text-center text-muted">Bạn chưa có booking nào. Hãy bắt đầu từ lịch chiếu ngoài trang chủ.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="mt-3">{{ $bookings->links() }}</div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
