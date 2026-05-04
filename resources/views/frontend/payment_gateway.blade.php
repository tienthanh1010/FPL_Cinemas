@extends('frontend.layout')

@section('title', 'Đang chuyển sang cổng thanh toán')

@push('styles')
<style>
  .gateway-shell{display:grid;grid-template-columns:minmax(0,1fr) minmax(320px,.75fr);gap:1.5rem}
  .gateway-card{background:var(--panel-light);border:1px solid var(--line);border-radius:24px;padding:1.25rem;box-shadow:0 16px 32px rgba(15,23,42,.08)}
  .payment-row{display:flex;justify-content:space-between;gap:1rem;padding:.65rem 0;border-bottom:1px solid var(--line)}
  .payment-row:last-child{border-bottom:0}
  .payment-row span{color:var(--muted)}
  .payment-row strong{color:var(--text);text-align:right}
  .amount-highlight{font-size:1.8rem;color:var(--primary)!important}
  .gateway-note{border-radius:18px;background:var(--surface-2);border:1px solid var(--line);padding:1rem;color:var(--muted)}
  .countdown{font-size:1.5rem;font-weight:900;color:var(--text)}
  @media(max-width:991.98px){.gateway-shell{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
  $isMomo = $payment->provider === 'MOMO';
  $payUrl = data_get($payment->response_payload, 'momo.pay_url');
  $orderInfo = data_get($payment->request_payload, 'order_info') ?: data_get($payment->request_payload, 'transfer_content') ?: ('Thanh toán vé ' . $booking->booking_code);
@endphp

<section class="section-space pt-4 pt-lg-5">
  <div class="container-fluid app-container">
    <div class="glass-panel p-4 p-lg-5">
      <span class="section-eyebrow">Thanh toán online</span>
      <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
      <p class="section-copy mb-4">Trang này chỉ dùng khi cổng thanh toán chưa tự chuyển được. Với MoMo, hệ thống sẽ ưu tiên chuyển thẳng sang trang MoMo.</p>

      @if(!empty($gatewayError))
        <div class="alert alert-danger rounded-4 mb-4">
          <strong>Không tạo được trang thanh toán.</strong>
          <div class="mt-1">{{ $gatewayError }}</div>
        </div>
      @endif

      <div class="gateway-shell">
        <div class="gateway-card">
          @if($isMomo)
            <h2 class="h4 mb-3">MoMo</h2>
            <div class="gateway-note mb-3">MoMo đang trong quá trình xử lý. Hãy bấm nút dưới để mở trang thanh toán MoMo nếu trình duyệt chưa tự chuyển.</div>
            @if($payUrl)
              <a class="btn btn-cinema-primary w-100 mb-3" href="{{ $payUrl }}"><i class="bi bi-box-arrow-up-right me-2"></i>Mở trang thanh toán MoMo</a>
            @else
              <div class="alert alert-warning rounded-4 mb-3">MoMo chưa trả về link thanh toán. Vui lòng quay lại và thử lại.</div>
            @endif
            <a class="btn btn-cinema-secondary" href="{{ route('booking.payment', ['booking_code' => $booking->booking_code]) }}"><i class="bi bi-arrow-left me-2"></i>Quay lại chọn cổng</a>
          @else
            <h2 class="h4 mb-3">VNPay tạm thời khoá</h2>
            <p class="text-light-emphasis">VNPay hiện chưa thể thanh toán. Vui lòng quay lại chọn MoMo.</p>
            <a class="btn btn-cinema-primary" href="{{ route('booking.payment', ['booking_code' => $booking->booking_code]) }}">Quay lại chọn MoMo</a>
          @endif
        </div>

        <div class="gateway-card">
          <h2 class="h4 mb-3">Thông tin thanh toán</h2>
          <div class="payment-row"><span>Số tiền cần thanh toán</span><strong class="amount-highlight">{{ number_format($payment->amount) }}đ</strong></div>
          <div class="payment-row"><span>Nội dung</span><strong>{{ $orderInfo }}</strong></div>
          <div class="payment-row"><span>Mã booking</span><strong>{{ $booking->booking_code }}</strong></div>
          <div class="payment-row"><span>Mã giao dịch</span><strong>{{ $payment->external_txn_ref }}</strong></div>
          <div class="payment-row"><span>Phim</span><strong>{{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</strong></div>
          <div class="payment-row"><span>Suất chiếu</span><strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong></div>
          <div class="payment-row"><span>Ghế</span><strong>{{ $booking->tickets->pluck('seat.seat_code')->filter()->implode(', ') ?: '—' }}</strong></div>
          <div class="payment-row"><span>Email nhận vé</span><strong>{{ $recipientEmail ?: 'Chưa có email' }}</strong></div>
          <div class="payment-row"><span>Hết hạn booking</span><strong>{{ optional($booking->expires_at)->format('d/m/Y H:i') ?: '—' }}</strong></div>
          <div class="mt-3">
            <small class="text-light-emphasis d-block mb-1">Thời gian giữ booking còn lại</small>
            <div class="countdown" id="paymentCountdownValue">00:00</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
@endsection

@push('scripts')
@if($isMomo)
<script>
(() => {
  const statusUrl = @json(route('booking.payment.status', ['booking_code' => $booking->booking_code, 'payment' => $payment]));
  const countdownNode = document.getElementById('paymentCountdownValue');
  const expiresAt = new Date(@json(optional($booking->expires_at)?->toIso8601String()));

  const format = (seconds) => {
    const safe = Math.max(0, Number(seconds || 0));
    return `${Math.floor(safe / 60).toString().padStart(2, '0')}:${Math.floor(safe % 60).toString().padStart(2, '0')}`;
  };

  const tickCountdown = () => {
    if (!countdownNode || Number.isNaN(expiresAt.getTime())) return;
    const secondsLeft = Math.max(0, Math.ceil((expiresAt.getTime() - Date.now()) / 1000));
    countdownNode.textContent = format(secondsLeft);
    if (secondsLeft <= 0) window.location.reload();
  };

  const pollPayment = async () => {
    try {
      const response = await fetch(statusUrl, {headers: {'Accept': 'application/json'}});
      if (!response.ok) return;
      const data = await response.json();
      if (data.paid && data.success_url) window.location.href = data.success_url;
      else if (data.failed && data.lookup_url) window.location.href = data.lookup_url;
    } catch (error) {}
  };

  tickCountdown();
  window.setInterval(tickCountdown, 1000);
  window.setInterval(pollPayment, 3000);
})();
</script>
@endif
@endpush
