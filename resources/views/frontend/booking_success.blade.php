@extends('frontend.layout')

@section('title', 'Booking thành công')

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="success-shell glass-panel">
        <div class="success-icon"><i class="bi bi-check2-circle"></i></div>
        <span class="section-eyebrow">Booking đã được tạo</span>
        <h1>{{ $booking->booking_code }}</h1>
        <p>Thiết kế trang kết quả cũng được làm đồng bộ với giao diện mới: card nổi, nền tối và cách trình bày rõ ràng, gọn mắt.</p>

        <div class="success-grid">
          <div class="success-card">
            <span>Trạng thái</span>
            <strong>{{ $booking->status }}</strong>
          </div>
          <div class="success-card">
            <span>Tổng tiền</span>
            <strong>{{ number_format($booking->total_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Khách hàng</span>
            <strong>{{ $booking->contact_name }}</strong>
          </div>
          <div class="success-card">
            <span>Hết hạn</span>
            <strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') }}</strong>
          </div>
        </div>

        <div class="tickets-panel">
          <h2>Danh sách vé</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Ghế</th>
                  <th>Giá</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                @foreach($booking->tickets as $ticket)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>#{{ $ticket->seat_id }}</td>
                    <td>{{ number_format($ticket->final_price_amount) }}đ</td>
                    <td><span class="status-badge">{{ $ticket->status }}</span></td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
          <a class="btn btn-cinema-primary" href="{{ route('home') }}"><i class="bi bi-house-door me-2"></i>Về trang chủ</a>
          <a class="btn btn-cinema-secondary" href="javascript:history.back()"><i class="bi bi-arrow-counterclockwise me-2"></i>Quay lại</a>
        </div>
      </div>
    </div>
  </section>
@endsection
