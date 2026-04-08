@extends('frontend.layout')

@section('title', 'Chi tiết booking ' . $booking->booking_code)

@section('content')
<<<<<<< HEAD
  @php
    $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
    $isTerminal = in_array((string) $booking->status, ['CANCELLED', 'EXPIRED'], true);
    $isPaid = $amountDue <= 0 && in_array((string) $booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true);
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="success-shell glass-panel">
        <div class="success-icon"><i class="bi {{ $isPaid ? 'bi-check2-circle' : 'bi-receipt' }}"></i></div>
        <span class="section-eyebrow">{{ $isPaid ? 'Thanh toán hoàn tất' : 'Booking đã được tạo' }}</span>
        <h1>{{ $booking->booking_code }}</h1>
        <p>
          @if($isPaid)
            Booking của bạn đã được thanh toán. Vé điện tử đã sẵn sàng để soát vé / check-in.
          @elseif($isTerminal)
            Booking hiện ở trạng thái {{ $booking->status }} và không thể tiếp tục thanh toán.
          @else
            Booking đã được ghi nhận. Vui lòng hoàn tất thanh toán trước thời gian hết hạn để giữ chỗ và phát hành vé điện tử.
          @endif
        </p>

        <div class="success-grid">
          <div class="success-card">
            <span>Trạng thái</span>
=======
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="success-shell glass-panel">
        <div class="success-icon"><i class="bi bi-check2-circle"></i></div>
<<<<<<< HEAD
        <span class="section-eyebrow">Booking đã được tạo</span>
        <h1>{{ $booking->booking_code }}</h1>
        <p>Thiết kế trang kết quả cũng được làm đồng bộ với giao diện mới: card nổi, nền tối và cách trình bày rõ ràng, gọn mắt.</p>

        <div class="success-grid">
          <div class="success-card">
            <span>Trạng thái</span>
=======
        <span class="section-eyebrow">Booking / Payment đã ghi xuống database</span>
        <h1>{{ $booking->booking_code }}</h1>
        <p>Trang kết quả hiện đã hiển thị cả ghế, sản phẩm, khuyến mãi và trạng thái thanh toán để khớp đầy đủ luồng frontend → backend → database.</p>

        <div class="success-grid">
          <div class="success-card">
            <span>Trạng thái booking</span>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            <strong>{{ $booking->status }}</strong>
          </div>
          <div class="success-card">
            <span>Tổng tiền</span>
            <strong>{{ number_format($booking->total_amount) }}đ</strong>
          </div>
          <div class="success-card">
<<<<<<< HEAD
            <span>Đã thanh toán</span>
            <strong>{{ number_format($booking->paid_amount) }}đ</strong>
=======
<<<<<<< HEAD
            <span>Khách hàng</span>
            <strong>{{ $booking->contact_name }}</strong>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
          </div>
          <div class="success-card">
            <span>Hết hạn</span>
            <strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') }}</strong>
          </div>
        </div>

<<<<<<< HEAD
        <div class="success-grid mt-3">
          <div class="success-card">
            <span>Phim</span>
            <strong>{{ $booking->show?->movieVersion?->movie?->title ?: 'Đang cập nhật' }}</strong>
          </div>
          <div class="success-card">
            <span>Suất chiếu</span>
            <strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong>
          </div>
          <div class="success-card">
            <span>Phòng / rạp</span>
            <strong>{{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</strong>
          </div>
          <div class="success-card">
            <span>Còn phải thanh toán</span>
            <strong>{{ number_format($amountDue) }}đ</strong>
          </div>
        </div>

        <div class="loyalty-note-card mt-4">
          @if($isPaid && $booking->customer?->user_id)
            <div class="content-tag mb-2">Điểm thành viên</div>
            <h3>+{{ number_format((int) ($earnedPoints ?? 0)) }} điểm cho booking này</h3>
            <p class="mb-0">Tổng điểm hiện tại: {{ number_format((int) ($booking->customer?->loyaltyAccount?->points_balance ?? 0)) }} điểm · Hạng {{ $booking->customer?->loyaltyAccount?->tier?->name ?: 'Member' }}.</p>
          @elseif($isPaid)
            <div class="content-tag mb-2">Khách vãng lai</div>
            <h3>Tạo tài khoản để tích điểm cho các lần đặt vé tiếp theo</h3>
            <p class="mb-3">Booking này đã thanh toán nhưng chưa gắn với tài khoản thành viên. Ở các đơn sau, chỉ cần đăng nhập trước khi đặt vé là điểm sẽ được cộng tự động.</p>
            <a href="{{ route('member.register') }}" class="btn btn-cinema-secondary">Tạo tài khoản thành viên</a>
          @else
            <div class="content-tag mb-2">Điểm thưởng</div>
            <h3>Điểm sẽ được cộng sau khi thanh toán thành công</h3>
            <p class="mb-0">Hoàn tất thanh toán để hệ thống phát hành vé và cộng điểm cho tài khoản thành viên của bạn.</p>
          @endif
        </div>

        <div class="tickets-panel mt-4">
=======
        <div class="tickets-panel">
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
          <h2>Danh sách vé</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Ghế</th>
<<<<<<< HEAD
                  <th>Loại vé</th>
                  <th>Mã vé điện tử</th>
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                  <th>Giá</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
<<<<<<< HEAD
                @forelse($booking->tickets as $ticket)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ticket->seat?->seat_code ?: ('#'.$ticket->seat_id) }}</td>
                    <td>{{ $ticket->ticketType?->name ?: ('#'.$ticket->ticket_type_id) }}</td>
                    <td>{{ $ticket->ticket?->ticket_code ?: 'Chưa phát hành' }}</td>
                    <td>{{ number_format($ticket->final_price_amount) }}đ</td>
                    <td><span class="status-badge">{{ $ticket->ticket?->status ?: $ticket->status }}</span></td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted">Chưa có vé nào trong booking này.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tickets-panel mt-4">
          <h2>Lịch sử thanh toán</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Provider</th>
                  <th>Method</th>
                  <th>Mã giao dịch</th>
                  <th>Số tiền</th>
                  <th>Trạng thái</th>
                  <th>Thời gian</th>
                </tr>
              </thead>
              <tbody>
                @forelse($booking->payments->sortByDesc('created_at') as $payment)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $payment->provider }}</td>
                    <td>{{ $payment->method }}</td>
                    <td>{{ $payment->external_txn_ref ?: 'Mô phỏng' }}</td>
                    <td>{{ number_format($payment->amount) }}đ</td>
                    <td><span class="status-badge">{{ $payment->status }}</span></td>
                    <td>{{ optional($payment->paid_at ?: $payment->created_at)->format('d/m/Y H:i') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="7" class="text-center text-muted">Chưa phát sinh giao dịch thanh toán nào.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tickets-panel mt-4">
          <h2>Combo &amp; đồ ăn kèm</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Sản phẩm</th>
                  <th>Phân loại</th>
                  <th>SL</th>
                  <th>Đơn giá</th>
                  <th>Thành tiền</th>
                </tr>
              </thead>
              <tbody>
                @forelse($booking->bookingProducts as $item)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->product?->name ?: ('#'.$item->product_id) }}</td>
                    <td>{{ $item->product?->category?->name ?: ($item->product?->is_combo ? 'Combo' : 'F&B') }}</td>
                    <td>{{ $item->qty }}</td>
                    <td>{{ number_format($item->unit_price_amount) }}đ</td>
                    <td>{{ number_format($item->final_amount) }}đ</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="6" class="text-center text-muted">Bạn chưa chọn combo hoặc đồ ăn kèm.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="tickets-panel mt-4">
          <h2>Khuyến mãi / voucher đã áp dụng</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Chương trình</th>
                  <th>Loại áp dụng</th>
                  <th>Mã voucher</th>
                  <th>Giảm</th>
                </tr>
              </thead>
              <tbody>
                @forelse($booking->discounts as $discount)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $discount->promotion?->name ?: 'Khuyến mãi' }}</td>
                    <td>{{ $discount->applied_to }}</td>
                    <td>{{ $discount->coupon?->code ?: ($discount->metadata['code'] ?? 'Tự động áp dụng') }}</td>
                    <td>{{ number_format($discount->discount_amount) }}đ</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-muted">Không có ưu đãi nào được áp dụng cho booking này.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>

        <div class="success-grid mt-4">
          <div class="success-card">
            <span>Tạm tính ban đầu</span>
            <strong>{{ number_format($booking->subtotal_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Giảm giá</span>
            <strong>{{ number_format($booking->discount_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Cần thanh toán</span>
            <strong>{{ number_format($booking->total_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Đã thanh toán</span>
            <strong>{{ number_format($booking->paid_amount) }}đ</strong>
          </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
          @if(! $isTerminal && ! $isPaid)
            <a class="btn btn-cinema-primary" href="{{ route('booking.payment', $booking->booking_code) }}"><i class="bi bi-credit-card me-2"></i>Thanh toán ngay {{ number_format($amountDue) }}đ</a>
          @endif
          <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-house-door me-2"></i>Về trang chủ</a>
          <a class="btn btn-cinema-secondary" href="javascript:history.back()"><i class="bi bi-arrow-counterclockwise me-2"></i>Quay lại</a>
=======
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
=======
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
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
          </div>
        </div>

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
          <a class="btn btn-cinema-primary" href="{{ route('home') }}"><i class="bi bi-house-door me-2"></i>Về trang chủ</a>
<<<<<<< HEAD
          <a class="btn btn-cinema-secondary" href="javascript:history.back()"><i class="bi bi-arrow-counterclockwise me-2"></i>Quay lại</a>
=======
          <a class="btn btn-cinema-secondary" href="{{ route('movies.showtimes', $booking->show->movieVersion->movie) }}"><i class="bi bi-film me-2"></i>Đặt thêm vé</a>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        </div>
      </div>
    </div>
  </section>
@endsection
