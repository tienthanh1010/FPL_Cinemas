@extends('frontend.layout')

@section('title', 'Thanh toán booking ' . $booking->booking_code)

@push('styles')
<style>
  .payment-shell{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:1.5rem}
  .payment-card{background:var(--panel-light);border:1px solid var(--line);border-radius:24px;padding:1.2rem;box-shadow:0 16px 32px rgba(15,23,42,.08)}
  .provider-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1rem}
  .provider-option{position:relative}
  .provider-option input{position:absolute;opacity:0;pointer-events:none}
  .provider-card{display:block;padding:1rem;border:1px solid var(--line);border-radius:22px;background:var(--surface-2);cursor:pointer;height:100%;transition:.18s ease;position:relative;overflow:hidden}
  .provider-card strong{display:flex;align-items:center;gap:.6rem;color:var(--text);margin-bottom:.35rem}
  .provider-card span{display:block;color:var(--muted);font-size:.92rem}
  .provider-logo{display:inline-flex;align-items:center;justify-content:center;width:42px;height:42px;border-radius:14px;color:#fff;font-weight:900;font-size:.78rem;flex:0 0 auto}
  .provider-badge{display:inline-flex!important;width:max-content;margin-top:.75rem;border-radius:999px;padding:.35rem .65rem;background:color-mix(in srgb,var(--primary) 12%, var(--surface));border:1px solid color-mix(in srgb,var(--primary) 28%, var(--line));font-size:.8rem!important;font-weight:800;color:var(--text)!important}
  .provider-option input:checked + .provider-card{border-color:color-mix(in srgb,var(--primary) 55%, var(--line));background:color-mix(in srgb,var(--primary) 10%, var(--surface-2));box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%, transparent)}
  .provider-card.is-disabled{opacity:.58;cursor:not-allowed;filter:grayscale(.25)}
  .provider-card.is-disabled::after{content:'Tạm thời khoá';position:absolute;right:.9rem;top:.9rem;border-radius:999px;padding:.25rem .55rem;background:rgba(239,68,68,.16);border:1px solid rgba(239,68,68,.35);color:#fecaca;font-size:.78rem;font-weight:900}
  .payment-row{display:flex;justify-content:space-between;gap:1rem;padding:.55rem 0;border-bottom:1px solid var(--line)}
  .payment-row:last-child{border-bottom:0}
  .payment-row span{color:var(--muted)}
  .payment-row strong{color:var(--text);text-align:right}
  .payment-total{font-size:1.55rem;color:var(--primary)!important}
  .payment-countdown{margin:1rem 0 1.2rem;padding:.9rem 1rem;border-radius:18px;background:var(--surface-2);border:1px solid var(--line)}
  .payment-countdown small{display:block;color:var(--muted);margin-bottom:.25rem;font-weight:700}
  .payment-countdown strong{font-size:1.5rem;color:var(--text)}
  .payment-note{margin-top:1rem;padding:.9rem 1rem;border-radius:16px;background:color-mix(in srgb,var(--primary) 8%, var(--surface-2));border:1px dashed color-mix(in srgb,var(--primary) 26%, var(--line));color:var(--muted)}
  @media (max-width: 991.98px){.payment-shell{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @php
    $amountDue = $amountDue ?? max(0, $booking->total_amount - $booking->paid_amount);
    $isTerminal = in_array($booking->status, ['CANCELLED', 'EXPIRED'], true);
    $isPaid = $amountDue <= 0 && in_array($booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true);
    $latestCapturedPayment = $booking->payments->where('status', 'CAPTURED')->sortByDesc('paid_at')->first();
    $emailSentAt = data_get($latestCapturedPayment?->response_payload, 'ticket_email_sent_at');
    $emailStatus = data_get($latestCapturedPayment?->response_payload, 'ticket_email_status');
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel p-4 p-lg-5">
        <span class="section-eyebrow">Thanh toán online</span>
        <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
        <p class="section-copy mb-4">Chọn cổng thanh toán. Hiện tại MoMo đang trong quá trình xử lý, VNPay tạm thời khoá. Khi MoMo xác nhận thanh toán thành công, hệ thống mới phát hành vé và gửi vé bản mềm về Gmail/email người đặt.</p>

        @if(session('error'))
          <div class="alert alert-danger rounded-4 mb-4">{{ session('error') }}</div>
        @endif
        @if(session('success'))
          <div class="alert alert-success rounded-4 mb-4">{{ session('success') }}</div>
        @endif

        @if($isTerminal)
          <div class="booking-alert mb-4">Booking này đang ở trạng thái <strong>{{ $booking->status }}</strong> và không thể thanh toán tiếp.</div>
        @elseif($isPaid)
          <div class="booking-alert mb-4">
            Booking này đã được thanh toán đầy đủ.
            @if($emailRecipient)
              Vé điện tử sẽ được gửi về <strong>{{ $emailRecipient }}</strong>
              @if($emailSentAt)
                lúc <strong>{{ \Illuminate\Support\Carbon::parse($emailSentAt)->format('d/m/Y H:i') }}</strong>.
              @elseif($emailStatus === 'FAILED')
                nhưng lần gửi gần nhất chưa thành công.
              @else
                trong vài giây tới.
              @endif
            @else
              Booking chưa có email nhận vé, vui lòng cập nhật email để hệ thống có thể gửi vé.
            @endif
          </div>
        @endif

        <div class="payment-shell">
          <div class="payment-card">
            <h2 class="h4 mb-3">Chọn cổng thanh toán</h2>
            <p class="text-light-emphasis mb-4">Sau khi bấm tiếp tục với MoMo, trang sẽ chuyển thẳng sang cổng thanh toán MoMo. Không hiển thị trang QR hoặc trang trung gian riêng của dự án.</p>

            @if(! $isTerminal && ! $isPaid)
              <div class="payment-countdown">
                <small>Thời gian giữ booking còn lại</small>
                <strong id="paymentCountdownValue">00:00</strong>
              </div>

              <form method="POST" action="{{ route('booking.payment.pay', $booking->booking_code) }}">
                @csrf
                <div class="provider-grid mb-4">
                  @foreach($providerOptions as $providerCode => $provider)
                    @php($enabled = (bool) ($provider['enabled'] ?? true))
                    <label class="provider-option">
                      <input type="radio" name="provider" value="{{ $providerCode }}" @checked(old('provider', 'MOMO') === $providerCode) @disabled(! $enabled)>
                      <span class="provider-card {{ $enabled ? '' : 'is-disabled' }}">
                        <strong>
                          <span class="provider-logo" style="background:{{ $provider['accent'] }};">{{ $provider['short'] }}</span>
                          {{ $provider['label'] }}
                        </strong>
                        <span>{{ $provider['description'] }}</span>
                        @if(!empty($provider['badge']))
                          <span class="provider-badge">{{ $provider['badge'] }}</span>
                        @endif
                      </span>
                    </label>
                  @endforeach
                </div>

                <div class="d-flex flex-wrap gap-3">
                  <button class="btn btn-cinema-primary" type="submit">
                    <i class="bi bi-box-arrow-up-right me-2"></i>Tiếp tục thanh toán MoMo
                  </button>
                  <a class="btn btn-cinema-secondary" href="{{ route('shows.book', ['show' => $booking->show_id, 'booking_code' => $booking->booking_code]) }}">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại sửa ghế
                  </a>
                </div>
              </form>

              @if($pendingPayments->isNotEmpty())
                <div class="payment-note">
                  <strong class="d-block mb-1">Giao dịch gần đây</strong>
                  @foreach($pendingPayments->take(3) as $payment)
                    <div class="d-flex justify-content-between gap-3 py-1">
                      <span>{{ $payment->provider }} · {{ $payment->external_txn_ref ?: 'Chưa có mã' }}</span>
                      <strong>{{ match((string) $payment->status) { 'INITIATED' => 'Khởi tạo', 'AUTHORIZED' => 'Đang chờ MoMo', 'CAPTURED' => 'Đã thanh toán', 'FAILED' => 'Thất bại', 'CANCELLED' => 'Đã huỷ', default => $payment->status } }}</strong>
                    </div>
                  @endforeach
                </div>
              @endif
            @else
              <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-cinema-primary" href="{{ route('booking.success', $booking->booking_code) }}"><i class="bi bi-ticket-perforated me-2"></i>Xem vé</a>
                @if($emailRecipient)
                  <form method="POST" action="{{ route('booking.payment.email.resend', $booking->booking_code) }}">
                    @csrf
                    <button class="btn btn-cinema-secondary" type="submit"><i class="bi bi-envelope-paper me-2"></i>Gửi lại vé qua email</button>
                  </form>
                @endif
              </div>
            @endif
          </div>

          <div class="payment-card">
            <h2 class="h4 mb-3">Thông tin đơn vé</h2>
            <div class="payment-row"><span>Phim</span><strong>{{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</strong></div>
            <div class="payment-row"><span>Suất chiếu</span><strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong></div>
            <div class="payment-row"><span>Phòng / rạp</span><strong>{{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</strong></div>
            <div class="payment-row"><span>Ghế</span><strong>{{ $booking->tickets->pluck('seat.seat_code')->filter()->implode(', ') ?: '—' }}</strong></div>
            <div class="payment-row"><span>Email nhận vé</span><strong>{{ $emailRecipient ?: 'Chưa có email' }}</strong></div>
            <div class="payment-row"><span>Tạm tính</span><strong>{{ number_format($booking->subtotal_amount) }}đ</strong></div>
            <div class="payment-row"><span>Giảm giá</span><strong>{{ number_format($booking->discount_amount) }}đ</strong></div>
            <div class="payment-row"><span>Đã thanh toán</span><strong>{{ number_format($booking->paid_amount) }}đ</strong></div>
            <div class="payment-row"><span>Hết hạn booking</span><strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') ?: '—' }}</strong></div>
            <div class="payment-row"><span>Cần thanh toán</span><strong class="payment-total">{{ number_format($amountDue) }}đ</strong></div>
            @if(($estimatedPoints ?? 0) > 0)
              <div class="mt-3 text-light-emphasis">Dự kiến cộng <strong>{{ number_format($estimatedPoints) }} điểm</strong> sau khi thanh toán thành công.</div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
<script>
(() => {
  const countdownNode = document.getElementById('paymentCountdownValue');
  if (!countdownNode) return;

  const expiresAt = new Date(@json(optional($booking->expires_at)?->toIso8601String()));
  if (Number.isNaN(expiresAt.getTime())) {
    countdownNode.textContent = '00:00';
    return;
  }

  const format = (seconds) => {
    const safe = Math.max(0, Number(seconds || 0));
    const minutes = Math.floor(safe / 60).toString().padStart(2, '0');
    const secs = Math.floor(safe % 60).toString().padStart(2, '0');
    return `${minutes}:${secs}`;
  };

  const tick = () => {
    const secondsLeft = Math.max(0, Math.ceil((expiresAt.getTime() - Date.now()) / 1000));
    countdownNode.textContent = format(secondsLeft);
    if (secondsLeft <= 0) window.location.reload();
  };

  tick();
  window.setInterval(tick, 1000);
})();
</script>
@endpush
