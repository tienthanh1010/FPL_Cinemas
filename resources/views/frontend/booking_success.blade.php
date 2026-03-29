@extends('frontend.layout')

@section('title', 'Booking thành công')

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="success-shell glass-panel">
        <div class="success-icon"><i class="bi bi-check2-circle"></i></div>
        <span class="section-eyebrow">Booking / Payment đã ghi xuống database</span>
        <h1>{{ $booking->booking_code }}</h1>
        <p>Trang kết quả hiện đã hiển thị cả ghế, sản phẩm, khuyến mãi và trạng thái thanh toán để khớp đầy đủ luồng frontend → backend → database.</p>

        <div class="success-grid">
          <div class="success-card">
            <span>Trạng thái booking</span>
            <strong>{{ $booking->status }}</strong>
          </div>
          <div class="success-card">
            <span>Tổng tiền</span>
            <strong>{{ number_format($booking->total_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Đã thanh toán</span>
            <strong>{{ number_format($booking->paid_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Khách hàng</span>
            <strong>{{ $booking->contact_name }}</strong>
          </div>
        </div>

        <div class="row g-4 mt-1">
          <div class="col-lg-7">
            <div class="tickets-panel h-100">
              <h2>Danh sách vé / ghế</h2>
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
                        <td>{{ $ticket->seat?->seat_code ?: ('#' . $ticket->seat_id) }}</td>
                        <td>{{ number_format($ticket->final_price_amount) }}đ</td>
                        <td><span class="status-badge">{{ $ticket->status }}</span></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <div class="col-lg-5">
            <div class="tickets-panel h-100">
              <h2>Thanh toán</h2>
              @forelse($booking->payments as $payment)
                <div class="success-card mb-3 text-start">
                  <span>{{ $payment->provider }} · {{ $payment->method }}</span>
                  <strong>{{ number_format($payment->amount) }}đ</strong>
                  <small class="d-block text-white-50 mt-2">Trạng thái: {{ $payment->status }} · Mã GD: {{ $payment->external_txn_ref }}</small>
                </div>
              @empty
                <div class="empty-panel">Chưa có bản ghi thanh toán.</div>
              @endforelse

              @if($booking->bookingProducts->isNotEmpty())
                <h2 class="mt-4">Combo / sản phẩm</h2>
                @foreach($booking->bookingProducts as $product)
                  <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                    <span>{{ $product->product?->name ?? 'Sản phẩm' }} x {{ $product->qty }}</span>
                    <strong>{{ number_format($product->final_amount) }}đ</strong>
                  </div>
                @endforeach
              @endif

              @if($booking->discounts->isNotEmpty())
                <h2 class="mt-4">Ưu đãi áp dụng</h2>
                @foreach($booking->discounts as $discount)
                  <div class="d-flex justify-content-between py-2 border-bottom border-light-subtle">
                    <span>{{ $discount->promotion?->name ?? 'Khuyến mãi' }}</span>
                    <strong>-{{ number_format($discount->discount_amount ?? 0) }}đ</strong>
                  </div>
                @endforeach
              @endif
            </div>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
          <a class="btn btn-cinema-primary" href="{{ route('home') }}"><i class="bi bi-house-door me-2"></i>Về trang chủ</a>
          <a class="btn btn-cinema-secondary" href="{{ route('movies.showtimes', $booking->show->movieVersion->movie) }}"><i class="bi bi-film me-2"></i>Đặt thêm vé</a>
        </div>
      </div>
    </div>
  </section>
@endsection
