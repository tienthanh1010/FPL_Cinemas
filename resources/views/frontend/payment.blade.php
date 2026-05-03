@extends('frontend.layout')

@section('title', 'Thanh toán booking ' . $booking->booking_code)

@push('styles')
<style>
  .checkout-shell {
    display: grid;
    grid-template-columns: minmax(0, 1.15fr) minmax(320px, .85fr);
    gap: 1.5rem;
  }
  .checkout-card,
  .payment-summary-card,
  .payment-method-card {
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.08);
    background: rgba(255,255,255,.04);
    padding: 1.25rem;
  }
  .payment-method-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
  }
  .payment-method-card {
    position: relative;
    cursor: pointer;
    transition: transform .16s ease, border-color .16s ease, background .16s ease;
    min-height: 170px;
  }
  .payment-method-card:hover {
    transform: translateY(-2px);
    border-color: rgba(255, 178, 71, .38);
  }
  .payment-method-card.is-active {
    border-color: rgba(255, 122, 24, .58);
    background: rgba(255, 122, 24, .08);
    box-shadow: 0 16px 34px rgba(255, 122, 24, .12);
  }
  .payment-method-card input[type="radio"] {
    position: absolute;
    inset: 0;
    opacity: 0;
    cursor: pointer;
  }
  .payment-method-card__icon {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    font-size: 1.4rem;
    background: rgba(255,255,255,.08);
    color: #fff;
    margin-bottom: .9rem;
  }
  .payment-method-card__title {
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: .35rem;
  }
  .payment-method-card__meta {
    color: rgba(255,255,255,.58);
    font-size: .9rem;
    line-height: 1.6;
  }
  .payment-summary-list {
    display: flex;
    flex-direction: column;
    gap: .85rem;
  }
  .payment-summary-row {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    color: rgba(255,255,255,.72);
  }
  .payment-summary-row strong {
    color: #fff;
  }
  .payment-summary-row--total {
    padding-top: 1rem;
    margin-top: .15rem;
    border-top: 1px solid rgba(255,255,255,.08);
    font-size: 1.02rem;
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
    background: rgba(255, 122, 24, .14);
    border: 1px solid rgba(255, 122, 24, .3);
    color: #ffd1b0;
    font-weight: 700;
  }
  .mini-order-list {
    display: grid;
    gap: .75rem;
  }
  .mini-order-item {
    border-radius: 18px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.06);
    padding: .85rem .95rem;
  }
  .mini-order-item strong {
    color: #fff;
  }
  .checkout-action-bar {
    display: flex;
    flex-wrap: wrap;
    gap: .85rem;
    align-items: center;
    justify-content: space-between;
    margin-top: 1.25rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255,255,255,.08);
  }
  .checkout-countdown {
    color: rgba(255,255,255,.62);
    font-size: .92rem;
  }
  .checkout-countdown strong {
    color: #fff;
  }
  @media (max-width: 991.98px) {
    .checkout-shell {
      grid-template-columns: 1fr;
    }
  }


  .checkout-card,
  .payment-summary-card,
  .payment-method-card,
  .mini-order-item {
    border-color: var(--line) !important;
    background: var(--panel-light) !important;
  }
  .payment-method-card__icon {
    background: var(--surface-2) !important;
    color: var(--text) !important;
  }
  .payment-method-card__title,
  .payment-summary-row strong,
  .mini-order-item strong,
  .checkout-countdown strong,
  .checkout-badge {
    color: var(--text) !important;
  }
  .payment-method-card__meta,
  .payment-summary-row,
  .checkout-countdown {
    color: var(--muted) !important;
  }
  .payment-method-card.is-active {
    border-color: color-mix(in srgb, var(--primary) 40%, var(--line)) !important;
    background: color-mix(in srgb, var(--primary) 12%, var(--panel-light)) !important;
    box-shadow: 0 16px 34px color-mix(in srgb, var(--primary) 14%, transparent) !important;
  }
  .payment-summary-row--total,
  .checkout-action-bar {
    border-top-color: var(--line) !important;
  }
  .checkout-badge {
    background: color-mix(in srgb, var(--primary) 14%, var(--panel-light)) !important;
    border-color: color-mix(in srgb, var(--primary) 30%, var(--line)) !important;
  }

</style>
@endpush

@section('content')
  @php
    $amountDue = $amountDue ?? max(0, $booking->total_amount - $booking->paid_amount);
    $isTerminal = in_array($booking->status, ['CANCELLED', 'EXPIRED'], true);
    $isPaid = $amountDue <= 0 && in_array($booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true);
    $paymentIcons = [
      'MOMO' => 'bi-wallet2',
      'ZALOPAY' => 'bi-phone',
      'VNPAY' => 'bi-bank',
      'CARD' => 'bi-credit-card-2-front',
    ];
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel p-4 p-lg-5">
        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-4">
          <div>
            <span class="section-eyebrow">Thanh toán booking</span>
            <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
            <p class="section-copy mb-0">Hoàn tất thanh toán để phát hành vé điện tử và giữ chỗ của bạn.</p>
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
                    <h3 class="mb-1">Chọn phương thức thanh toán</h3>
                    <p>Luồng thanh toán đã được tối ưu để người dùng chỉ cần chọn phương thức, xác nhận và hệ thống sẽ đồng bộ trạng thái booking, vé và điểm thành viên.</p>
                  </div>
                </div>

                <form method="post" action="{{ route('booking.payment.pay', $booking->booking_code) }}" id="payment-form">
                  @csrf
                  <div class="payment-method-grid">
                    @foreach($providerOptions as $providerCode => $provider)
                      <label class="payment-method-card {{ old('provider', 'MOMO') === $providerCode ? 'is-active' : '' }}" data-payment-option>
                        <input type="radio" name="provider" value="{{ $providerCode }}" {{ old('provider', 'MOMO') === $providerCode ? 'checked' : '' }}>
                        <div class="payment-method-card__icon"><i class="bi {{ $paymentIcons[$providerCode] ?? 'bi-wallet2' }}"></i></div>
                        <div class="payment-method-card__title">{{ $provider['label'] }}</div>
                        <div class="text-white-50 small mb-2">{{ $providerCode }} · {{ $provider['method'] }}</div>
                        <div class="payment-method-card__meta">{{ $provider['description'] }}</div>
                      </label>
                    @endforeach
                  </div>

                  <div class="checkout-action-bar">
                    <div class="checkout-countdown">
                      @if($booking->expires_at)
                        Booking giữ chỗ đến <strong>{{ $booking->expires_at->format('H:i \n\g\à\y d/m/Y') }}</strong>
                      @else
                        Booking chưa có thời điểm hết hạn.
                      @endif
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-end">
                      <a class="btn btn-cinema-secondary" href="{{ route('booking.success', $booking->booking_code) }}">Xem chi tiết booking</a>
                      <button type="submit" class="btn btn-cinema-primary"><i class="bi bi-credit-card me-2"></i>Thanh toán {{ number_format($amountDue) }}đ</button>
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
                <p class="mb-0">{{ $estimatedPoints > 0 ? 'Nếu thanh toán thành công, booking này sẽ cộng thêm khoảng ' . number_format($estimatedPoints) . ' điểm.' : 'Booking hiện không phát sinh thêm điểm thưởng.' }}</p>
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
  document.querySelectorAll('[data-payment-option]').forEach((card) => {
    const input = card.querySelector('input[type="radio"]');
    if (!input) return;
    card.addEventListener('click', () => {
      document.querySelectorAll('[data-payment-option]').forEach((item) => item.classList.remove('is-active'));
      input.checked = true;
      card.classList.add('is-active');
    });
  });
</script>
@endpush
