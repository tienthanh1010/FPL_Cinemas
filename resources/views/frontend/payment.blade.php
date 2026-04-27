@extends('frontend.layout')

@section('title', 'Thanh toán booking ' . $booking->booking_code)

@push('styles')
<style>
  .checkout-shell {
    display: grid;
    grid-template-columns: minmax(0, 1.08fr) minmax(320px, .92fr);
    gap: 1.5rem;
  }
  .checkout-card,
  .payment-summary-card,
  .payment-method-card,
  .transfer-card,
  .transfer-detail-card,
  .mini-order-item {
    border-radius: 28px;
    border: 1px solid var(--line);
    background: var(--panel-light);
    padding: 1.25rem;
  }
  .payment-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .payment-method-card {
    position: relative;
    min-height: 150px;
    border-color: color-mix(in srgb, var(--primary) 34%, var(--line));
    background: color-mix(in srgb, var(--primary) 7%, var(--panel-light));
  }
  .payment-method-card__icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    font-size: 1.4rem;
    background: var(--surface-2);
    color: var(--text);
    margin-bottom: .9rem;
  }
  .payment-method-card__title,
  .payment-summary-row strong,
  .mini-order-item strong,
  .checkout-countdown strong,
  .checkout-badge,
  .transfer-hero__title,
  .transfer-detail__value {
    color: var(--text);
  }
  .payment-method-card__meta,
  .payment-summary-row,
  .checkout-countdown,
  .transfer-hero__copy,
  .transfer-detail__label,
  .transfer-note,
  .mini-order-item .text-white-50,
  .mini-order-item .small {
    color: var(--muted) !important;
  }
  .payment-summary-list,
  .mini-order-list,
  .transfer-detail-list {
    display: grid;
    gap: .85rem;
  }
  .payment-summary-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
  }
  .payment-summary-row--total,
  .checkout-action-bar {
    padding-top: 1rem;
    margin-top: .15rem;
    border-top: 1px solid var(--line);
  }
  .payment-summary-row--total strong {
    font-size: 1.35rem;
  }
  .checkout-badge {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem .85rem;
    border-radius: 999px;
    background: color-mix(in srgb, var(--primary) 14%, var(--panel-light));
    border: 1px solid color-mix(in srgb, var(--primary) 30%, var(--line));
    font-weight: 700;
  }
  .mini-order-item {
    padding: .85rem .95rem;
  }
  .checkout-action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: .85rem;
    align-items: center;
    justify-content: space-between;
  }
  .checkout-countdown {
    font-size: .92rem;
  }
  .transfer-shell {
    display: grid;
    grid-template-columns: minmax(280px, .9fr) minmax(0, 1.1fr);
    gap: 1rem;
    margin-top: 1rem;
  }
  .transfer-card {
    text-align: center;
  }
  .transfer-qr {
    width: 100%;
    max-width: 290px;
    margin: 0 auto 1rem;
    padding: .85rem;
    border-radius: 24px;
    background: #fff;
    box-shadow: inset 0 0 0 1px rgba(15, 23, 42, .06);
  }
  .transfer-qr img {
    width: 100%;
    display: block;
  }
  .transfer-detail-card {
    display: flex;
    flex-direction: column;
    gap: 1rem;
  }
  .transfer-detail-item {
    border-radius: 18px;
    background: var(--surface-2);
    border: 1px solid var(--line);
    padding: .9rem 1rem;
  }
  .transfer-detail__label {
    display: block;
    margin-bottom: .35rem;
    font-size: .82rem;
    text-transform: uppercase;
    letter-spacing: .04em;
  }
  .transfer-detail__value {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    font-weight: 700;
    word-break: break-word;
  }
  .transfer-copy-btn {
    border: 0;
    border-radius: 999px;
    padding: .4rem .75rem;
    background: color-mix(in srgb, var(--primary) 14%, var(--surface-2));
    color: var(--text);
    font-size: .82rem;
    white-space: nowrap;
  }
  .transfer-note {
    border-radius: 18px;
    background: color-mix(in srgb, var(--primary) 8%, var(--panel-light));
    border: 1px dashed color-mix(in srgb, var(--primary) 28%, var(--line));
    padding: .95rem 1rem;
    line-height: 1.7;
  }
  .payment-success-hint {
    margin-top: .8rem;
    color: var(--text);
    font-weight: 600;
  }
  @media (max-width: 991.98px) {
    .checkout-shell,
    .transfer-shell {
      grid-template-columns: 1fr;
    }
  }
</style>
@endpush

@section('content')
  @php
    $amountDue = $amountDue ?? max(0, $booking->total_amount - $booking->paid_amount);
    $isTerminal = in_array($booking->status, ['CANCELLED', 'EXPIRED'], true);
    $isPaid = $amountDue <= 0 && in_array($booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true);
    $transferData = $transferData ?? [];
    $providerLabel = $bankProviderLabel ?? ($transferData['provider_label'] ?? 'MB Bank');
    $transferQrUrl = $transferData['qr_image_url'] ?? null;
    $transferAccountNo = $transferData['account_no'] ?? null;
    $transferAccountName = $transferData['account_name'] ?? null;
    $transferContent = $transferData['transfer_content'] ?? null;
    $transferAmount = $transferData['amount'] ?? $amountDue;
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
          <div>
            <span class="section-eyebrow">Thanh toán booking</span>
            <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
            <p class="section-copy mb-0">Quét QR bằng app ngân hàng, chuyển đúng số tiền và đúng nội dung để hệ thống đối soát booking.</p>
          </div>
          <div class="checkout-badge"><i class="bi bi-ticket-detailed"></i>{{ $booking->status }}</div>
        </div>

        @if($isTerminal)
          <div class="booking-alert mb-4">
            Booking này đang ở trạng thái <strong>{{ $booking->status }}</strong>. Bạn không thể tiếp tục thanh toán.
          </div>
        @elseif($isPaid)
          <div class="booking-alert mb-4">
            Booking này đã được thanh toán đầy đủ. Bạn có thể xem chi tiết vé đã phát hành ngay bây giờ.
          </div>
        @endif

        <div class="checkout-shell">
          <div class="checkout-card">
            <div class="show-selection-shell mb-4">
              <div class="show-selection-shell__header">
                <div>
                  <h3 class="text-white mb-1">Thông tin suất chiếu</h3>
                  <p>{{ $booking->show?->movieVersion?->movie?->title ?: 'Phim đang cập nhật' }}</p>
                </div>
              </div>
              <div class="show-selection-shell__meta">
                <div class="mini-metric">
                  <span>Suất chiếu</span>
                  <strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong>
                </div>
                <div class="mini-metric">
                  <span>Phòng / rạp</span>
                  <strong>{{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</strong>
                </div>
                <div class="mini-metric">
                  <span>Khách hàng</span>
                  <strong>{{ $booking->contact_name }} · {{ $booking->contact_phone }}</strong>
                </div>
                <div class="mini-metric">
                  <span>Giữ chỗ đến</span>
                  <strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') ?: '—' }}</strong>
                </div>
              </div>
            </div>

            <div class="tickets-panel mb-4">
              <h2>Ghế đã chọn</h2>
              <div class="mini-order-list">
                @foreach($booking->tickets as $ticket)
                  <div class="mini-order-item d-flex justify-content-between gap-3 flex-wrap align-items-start">
                    <div>
                      <strong>{{ $ticket->seat?->seat_code ?: ('#'.$ticket->seat_id) }}</strong>
                      <div class="text-white-50 small mt-1">{{ $ticket->ticketType?->name ?: 'Loại vé' }} · {{ $ticket->seatType?->name ?: 'Ghế' }}</div>
                    </div>
                    <div class="text-white fw-semibold">{{ number_format($ticket->final_price_amount) }}đ</div>
                  </div>
                @endforeach
              </div>
            </div>

            @if($booking->bookingProducts->isNotEmpty())
              <div class="tickets-panel mb-4">
                <h2>Combo &amp; đồ ăn kèm</h2>
                <div class="mini-order-list">
                  @foreach($booking->bookingProducts as $item)
                    <div class="mini-order-item d-flex justify-content-between gap-3 flex-wrap align-items-start">
                      <div>
                        <strong>{{ $item->product?->name ?: ('#'.$item->product_id) }}</strong>
                        <div class="text-white-50 small mt-1">SL: {{ $item->qty }} · {{ $item->product?->category?->name ?: 'F&B' }}</div>
                      </div>
                      <div class="text-white fw-semibold">{{ number_format($item->final_amount) }}đ</div>
                    </div>
                  @endforeach
                </div>
              </div>
            @endif

            @if(! $isTerminal && ! $isPaid)
              <div class="product-selection-shell">
                <div class="product-selection-shell__header">
                  <div>
                    <h3 class="mb-1">Phương thức thanh toán đang áp dụng</h3>
                    <p>Luồng thanh toán hiện tại dùng chuyển khoản {{ $providerLabel }} qua QR. Sau khi chuyển khoản xong, khách bấm xác nhận để admin đối soát và phát hành vé.</p>
                  </div>
                </div>

                <div class="payment-method-grid">
                  <div class="payment-method-card is-active">
                    <div class="payment-method-card__icon"><i class="bi bi-qr-code-scan"></i></div>
                    <div class="payment-method-card__title">{{ $providerLabel }} QR</div>
                    <div class="text-white-50 small mb-2">MBBANK · BANK_TRANSFER</div>
                    <div class="payment-method-card__meta">Quét mã bằng app ngân hàng, chuyển đúng số tiền, đúng nội dung, sau đó bấm “Tôi đã chuyển khoản”.</div>
                  </div>
                </div>

                <div class="transfer-shell">
                  <div class="transfer-card">
                    <div class="transfer-hero__title mb-2">Quét mã QR để thanh toán</div>
                    <div class="transfer-hero__copy mb-3">App ngân hàng sẽ tự điền sẵn số tài khoản, số tiền và nội dung chuyển khoản.</div>
                    <div class="transfer-qr">
                      @if($transferQrUrl)
                        <img src="{{ $transferQrUrl }}" alt="QR thanh toán {{ $booking->booking_code }}">
                      @else
                        <div class="py-5 text-center text-muted">QR thanh toán đang được cập nhật.</div>
                      @endif
                    </div>
                    <div class="payment-success-hint">Số tiền cần chuyển: {{ number_format($transferAmount) }}đ</div>
                  </div>

                  <div class="transfer-detail-card">
                    <div class="transfer-detail-list">
                      <div class="transfer-detail-item">
                        <span class="transfer-detail__label">Ngân hàng</span>
                        <div class="transfer-detail__value">
                          <span>{{ $providerLabel }}</span>
                        </div>
                      </div>
                      <div class="transfer-detail-item">
                        <span class="transfer-detail__label">Số tài khoản</span>
                        <div class="transfer-detail__value">
                          <span>{{ $transferAccountNo ?: 'Đang cập nhật' }}</span>
                          @if($transferAccountNo)
                            <button type="button" class="transfer-copy-btn" data-copy-value="{{ $transferAccountNo }}">Sao chép</button>
                          @endif
                        </div>
                      </div>
                      <div class="transfer-detail-item">
                        <span class="transfer-detail__label">Tên tài khoản</span>
                        <div class="transfer-detail__value">
                          <span>{{ $transferAccountName ?: 'Đang cập nhật' }}</span>
                          @if($transferAccountName)
                            <button type="button" class="transfer-copy-btn" data-copy-value="{{ $transferAccountName }}">Sao chép</button>
                          @endif
                        </div>
                      </div>
                      <div class="transfer-detail-item">
                        <span class="transfer-detail__label">Nội dung chuyển khoản</span>
                        <div class="transfer-detail__value">
                          <span>{{ $transferContent ?: $booking->booking_code }}</span>
                          <button type="button" class="transfer-copy-btn" data-copy-value="{{ $transferContent ?: $booking->booking_code }}">Sao chép</button>
                        </div>
                      </div>
                      <div class="transfer-detail-item">
                        <span class="transfer-detail__label">Số tiền</span>
                        <div class="transfer-detail__value">
                          <span>{{ number_format($transferAmount) }}đ</span>
                          <button type="button" class="transfer-copy-btn" data-copy-value="{{ $transferAmount }}">Sao chép</button>
                        </div>
                      </div>
                    </div>

                    <div class="transfer-note">
                      <strong class="d-block mb-2" style="color:var(--text)">Lưu ý đối soát</strong>
                      1. Chỉ chuyển đúng số tiền booking này.<br>
                      2. Không sửa nội dung chuyển khoản.<br>
                      3. Sau khi chuyển khoản, bấm xác nhận bên dưới để hệ thống ghi nhận chờ đối soát.<br>
                      4. Nếu quá thời gian giữ chỗ mà chưa được thanh toán, booking sẽ tự hết hạn.
                    </div>
                  </div>
                </div>

                <form method="post" action="{{ route('booking.payment.pay', $booking->booking_code) }}" id="payment-form">
                  @csrf
                  <input type="hidden" name="action" value="mark_transferred">

                  <div class="checkout-action-bar">
                    <div class="checkout-countdown">
                      @if($booking->expires_at)
                        Booking giữ chỗ đến <strong>{{ $booking->expires_at->format('H:i \n\g\à\y d/m/Y') }}</strong>
                      @else
                        Booking chưa có thời điểm hết hạn.
                      @endif
                    </div>
                    <div class="d-flex flex-column flex-sm-row gap-2 align-items-sm-center justify-content-end">
                      <a class="section-link section-link--compact" href="{{ route('booking.success', $booking->booking_code) }}">Xem chi tiết booking <i class="bi bi-arrow-right"></i></a>
                      <button type="submit" class="btn btn-cinema-primary"><i class="bi bi-check2-circle me-2"></i>Tôi đã chuyển khoản</button>
                    </div>
                  </div>
                </form>
              </div>
            @endif
          </div>

          <div class="payment-summary-card">
            <h3 class="text-white mb-3">Tóm tắt thanh toán</h3>
            <div class="payment-summary-list">
              <div class="payment-summary-row"><span>Tạm tính</span><strong>{{ number_format($booking->subtotal_amount) }}đ</strong></div>
              <div class="payment-summary-row"><span>Giảm giá</span><strong>-{{ number_format($booking->discount_amount) }}đ</strong></div>
              <div class="payment-summary-row"><span>Tổng booking</span><strong>{{ number_format($booking->total_amount) }}đ</strong></div>
              <div class="payment-summary-row"><span>Đã thanh toán</span><strong>{{ number_format($booking->paid_amount) }}đ</strong></div>
              <div class="payment-summary-row payment-summary-row--total"><span>Cần thanh toán</span><strong>{{ number_format($amountDue) }}đ</strong></div>
            </div>

            <div class="loyalty-note-card mt-4">
              @auth
                <div class="content-tag mb-2">Điểm thành viên</div>
                <h3>{{ number_format((int) ($authCustomer?->loyaltyAccount?->points_balance ?? 0)) }} điểm hiện có</h3>
                <p class="mb-0">{{ $estimatedPoints > 0 ? 'Nếu booking được xác nhận thanh toán thành công, hệ thống sẽ cộng thêm khoảng ' . number_format($estimatedPoints) . ' điểm.' : 'Booking hiện không phát sinh thêm điểm thưởng.' }}</p>
              @else
                <div class="content-tag mb-2">Tích điểm cùng tài khoản</div>
                <h3>{{ $estimatedPoints > 0 ? 'Dự kiến +' . number_format($estimatedPoints) . ' điểm' : 'Đăng nhập để lưu booking' }}</h3>
                <p class="mb-3">Đăng nhập thành viên để đồng bộ lịch sử booking và nhận điểm ngay sau khi thanh toán thành công.</p>
                <a href="{{ route('login') }}" class="btn btn-cinema-secondary w-100">Đăng nhập thành viên</a>
              @endauth
            </div>

            <div class="tickets-panel mt-4">
              <h2 class="mb-3">Lịch sử giao dịch</h2>
              <div class="mini-order-list">
                @forelse($booking->payments->sortByDesc('created_at') as $payment)
                  <div class="mini-order-item">
                    <div class="d-flex justify-content-between gap-3 flex-wrap align-items-start">
                      <div>
                        <strong>{{ $payment->provider }}</strong>
                        <div class="text-white-50 small mt-1">{{ $payment->method }} · {{ $payment->status }}</div>
                        <div class="text-white-50 small">{{ optional($payment->paid_at ?: $payment->created_at)->format('d/m/Y H:i') }}</div>
                      </div>
                      <div class="text-end">
                        <div class="text-white fw-semibold">{{ number_format($payment->amount) }}đ</div>
                        <div class="text-white-50 small">{{ $payment->external_txn_ref ?: 'Mô phỏng' }}</div>
                      </div>
                    </div>
                  </div>
                @empty
                  <div class="mini-order-item text-white-50">Chưa phát sinh giao dịch thanh toán nào.</div>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('[data-copy-value]').forEach((button) => {
    button.addEventListener('click', async () => {
      const value = button.getAttribute('data-copy-value') || '';
      if (!value) return;
      try {
        await navigator.clipboard.writeText(value);
        const original = button.textContent;
        button.textContent = 'Đã chép';
        setTimeout(() => button.textContent = original, 1400);
      } catch (error) {
        console.error(error);
      }
    });
  });
</script>
@endpush
