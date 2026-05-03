@extends('frontend.layout')

@section('title', 'Chi tiết booking ' . $booking->booking_code)

@section('content')
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

        @php
          $latestCapturedPayment = $booking->payments->where('status', 'CAPTURED')->sortByDesc('paid_at')->first();
          $ticketEmailRecipient = $booking->contact_email ?: ($booking->customer?->email);
          $ticketEmailSentAt = data_get($latestCapturedPayment?->response_payload, 'ticket_email_sent_at');
          $ticketEmailStatus = data_get($latestCapturedPayment?->response_payload, 'ticket_email_status');
        @endphp

        @if($isPaid)
          <div class="booking-alert mb-4">
            @if($ticketEmailRecipient)
              Vé bản mềm sẽ được gửi tới <strong>{{ $ticketEmailRecipient }}</strong>.
              @if($ticketEmailSentAt)
                Hệ thống đã gửi lúc <strong>{{ \Illuminate\Support\Carbon::parse($ticketEmailSentAt)->format('d/m/Y H:i') }}</strong>.
              @elseif($ticketEmailStatus === 'FAILED')
                Lần gửi gần nhất chưa thành công. Bạn có thể bấm gửi lại vé qua email ở phía dưới.
              @else
                Email đang được xử lý gửi đi.
              @endif
            @else
              Booking chưa có email người nhận để gửi vé bản mềm.
            @endif
          </div>
        @endif

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
            <span>Đã thanh toán</span>
            <strong>{{ number_format($booking->paid_amount) }}đ</strong>
          </div>
          <div class="success-card">
            <span>Hết hạn</span>
            <strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') }}</strong>
          </div>
        </div>

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
          <h2>Danh sách vé</h2>
          <div class="table-responsive">
            <table class="table app-table align-middle mb-0">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Ghế</th>
                  <th>Mã vé điện tử</th>
                  <th>Giá</th>
                  <th>Trạng thái</th>
                </tr>
              </thead>
              <tbody>
                @forelse($booking->tickets as $ticket)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ticket->seat?->seat_code ?: ('#'.$ticket->seat_id) }}</td>
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

        @if($booking->tickets->isNotEmpty())
          <div class="tickets-panel mt-4">
            <h2>Mã vạch check-in</h2>
            <div class="row g-3">
              @foreach($booking->tickets as $bookingTicket)
                @php
                  $electronicTicket = $bookingTicket->ticket;
                  $scanPayload = ticket_scan_payload($electronicTicket);
                @endphp
                <div class="col-lg-6">
                  <div class="glass-panel h-100">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                      <div>
                        <div class="section-eyebrow">{{ $bookingTicket->seat?->seat_code ?: ('Ghế #'.$bookingTicket->seat_id) }}</div>
                        <h3 class="h5 mb-1">{{ $electronicTicket?->ticket_code ?: 'Chưa phát hành vé điện tử' }}</h3>
                        <p class="mb-0 text-light-emphasis">{{ $bookingTicket->ticketType?->name ?: 'Vé xem phim' }} · {{ $bookingTicket->seatType?->name ?: 'Ghế tiêu chuẩn' }}</p>
                      </div>
                      <span class="status-badge">{{ $electronicTicket?->status ?: $bookingTicket->status }}</span>
                    </div>
                    @if($scanPayload)
                      <div class="row g-3 align-items-center">
                        <div class="col-12 text-center">
                          <img src="{{ ticket_barcode_image_url($scanPayload, 70) }}" alt="Barcode {{ $electronicTicket?->ticket_code }}" class="img-fluid rounded-3 bg-white p-2 mb-2" style="max-width: 100%;">
                          <div class="fw-semibold mt-2">{{ $scanPayload }}</div>
                          <div class="small text-light-emphasis mt-2">Mang mã vạch này đến quầy hoặc cổng soát vé để scan check-in.</div>
                        </div>
                      </div>
                    @else
                      <div class="text-light-emphasis">Vé điện tử sẽ hiển thị mã vạch sau khi thanh toán thành công.</div>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @endif

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

        @if($isPaid)
          <div class="tickets-panel mt-4">
            <h2>Đánh giá trải nghiệm của bạn</h2>
            <form method="POST" action="{{ route('booking.feedback.store', $booking->booking_code) }}" class="row g-3">
              @csrf
              @php($feedback = $booking->feedback)
              @foreach([
                ['movie_rating', 'movie_comment', 'Đánh giá phim'],
                                ['facility_rating', 'facility_comment', 'Cơ sở vật chất'],
                ['staff_rating', 'staff_comment', 'Nhân viên phục vụ'],
              ] as [$ratingField, $commentField, $label])
                <div class="col-lg-6">
                  <div class="glass-panel h-100">
                    <label class="form-label">{{ $label }}</label>
                    <select class="form-select mb-2" name="{{ $ratingField }}">
                      <option value="">Chọn số sao</option>
                      @for($star = 5; $star >= 1; $star--)
                        <option value="{{ $star }}" @selected(old($ratingField, $feedback?->{$ratingField}) == $star)>{{ $star }} sao</option>
                      @endfor
                    </select>
                    <textarea class="form-control" rows="3" name="{{ $commentField }}" placeholder="Nhận xét ngắn về {{ \Illuminate\Support\Str::lower($label) }}">{{ old($commentField, $feedback?->{$commentField}) }}</textarea>
                  </div>
                </div>
              @endforeach
              <div class="col-12">
                <label class="form-label">Nhận xét chung</label>
                <textarea class="form-control" rows="4" name="overall_comment" placeholder="Điều bạn hài lòng hoặc muốn rạp cải thiện thêm">{{ old('overall_comment', $feedback?->overall_comment) }}</textarea>
              </div>
              <div class="col-12 d-flex justify-content-end">
                <button class="btn btn-cinema-primary" type="submit"><i class="bi bi-send-check me-2"></i>Gửi đánh giá</button>
              </div>
            </form>
          </div>
        @endif

        <div class="d-flex flex-wrap gap-3 justify-content-center mt-4">
          @if(! $isTerminal && ! $isPaid)
            <a class="btn btn-cinema-primary" href="{{ route('booking.payment', $booking->booking_code) }}"><i class="bi bi-credit-card me-2"></i>Thanh toán ngay {{ number_format($amountDue) }}đ</a>
          @endif
          @if($isPaid && $ticketEmailRecipient)
            <form method="POST" action="{{ route('booking.payment.email.resend', $booking->booking_code) }}">
              @csrf
              <button class="btn btn-cinema-secondary" type="submit"><i class="bi bi-envelope-paper me-2"></i>Gửi lại vé qua email</button>
            </form>
          @endif
          @if($isPaid)
          @endif
          <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-house-door me-2"></i>Về trang chủ</a>
          <a class="btn btn-cinema-secondary" href="javascript:history.back()"><i class="bi bi-arrow-counterclockwise me-2"></i>Quay lại</a>
        </div>
      </div>
    </div>
  </section>
@endsection
