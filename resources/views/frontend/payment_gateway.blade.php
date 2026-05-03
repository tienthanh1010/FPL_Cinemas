@extends('frontend.layout')

@section('title', 'Thanh toán ' . $booking->booking_code)

@push('styles')
<style>
  .gateway-shell{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:1.4rem;align-items:start}
  .gateway-card{background:var(--panel-light);border:1px solid var(--line);border-radius:24px;padding:1.25rem;box-shadow:0 16px 32px rgba(15,23,42,.08)}
  .qr-box{display:flex;align-items:center;justify-content:center;min-height:320px;border-radius:24px;background:#fff;border:1px solid var(--line);padding:1rem}
  .qr-box img{max-width:300px;width:100%;height:auto;border-radius:16px}
  .payment-row{display:flex;justify-content:space-between;gap:1rem;padding:.65rem 0;border-bottom:1px solid var(--line)}
  .payment-row:last-child{border-bottom:0}
  .payment-row span{color:var(--muted)}
  .payment-row strong{color:var(--text);text-align:right}
  .amount-highlight{font-size:1.8rem;color:var(--primary)!important}
  .transfer-content{font-size:1.05rem;border:1px dashed color-mix(in srgb,var(--primary) 40%, var(--line));background:color-mix(in srgb,var(--primary) 8%, var(--surface-2));border-radius:18px;padding:.9rem 1rem;color:var(--text);font-weight:800;word-break:break-word}
  .status-pill{display:inline-flex;align-items:center;gap:.45rem;border-radius:999px;padding:.55rem .85rem;background:var(--surface-2);border:1px solid var(--line);font-weight:700;color:var(--text)}
  .gateway-note{border-radius:18px;background:var(--surface-2);border:1px solid var(--line);padding:1rem;color:var(--muted)}
  .countdown{font-size:1.5rem;font-weight:900;color:var(--text)}
  @media(max-width:991.98px){.gateway-shell{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
@php
  $isMomo = $payment->provider === 'MOMO';
  $qrUrl = data_get($payment->response_payload, 'momo.qr_code_url');
  $payUrl = data_get($payment->response_payload, 'momo.pay_url');
  $deeplink = data_get($payment->response_payload, 'momo.deeplink');
  $orderInfo = data_get($payment->request_payload, 'order_info') ?: data_get($payment->request_payload, 'transfer_content') ?: ('Thanh toán vé ' . $booking->booking_code);
@endphp

<section class="section-space pt-4 pt-lg-5">
  <div class="container-fluid app-container">
    <div class="glass-panel p-4 p-lg-5">
      <span class="section-eyebrow">Thanh toán online</span>
      <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
      <p class="section-copy mb-4">Quét mã QR và thanh toán đúng số tiền. Khi MoMo xác nhận thành công, hệ thống sẽ tự xác nhận vé, chuyển sang trang thành công và gửi vé mềm về email của người dùng.</p>

      @if(!empty($gatewayError))
        <div class="alert alert-danger rounded-4 mb-4">
          <strong>Không tạo được mã QR MoMo.</strong>
          <div class="mt-1">{{ $gatewayError }}</div>
          <div class="small mt-2">Lỗi này đã được chặn để không còn vòng lặp chuyển hướng. Hãy kiểm tra lại APP_URL, MOMO_REDIRECT_URL, MOMO_IPN_URL và thông tin MoMo trong file .env, sau đó chạy <code>php artisan optimize:clear</code>.</div>
        </div>
      @endif

      <div class="gateway-shell">
        <div class="gateway-card">
          @if($isMomo)
            <div class="d-flex justify-content-between align-items-center gap-3 mb-3 flex-wrap">
              <h2 class="h4 mb-0">Quét mã QR MoMo</h2>
              <span class="status-pill"><i class="bi bi-arrow-repeat"></i> {{ empty($gatewayError) ? 'Đang chờ thanh toán' : 'Chưa tạo được QR' }}</span>
            </div>

            <div class="qr-box mb-3">
              @if($qrUrl)
                <img src="{{ $qrUrl }}" alt="QR thanh toán MoMo">
              @elseif($payUrl)
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode($payUrl) }}" alt="QR thanh toán MoMo">
              @else
                <div class="text-center text-muted">MoMo chưa trả về mã QR. Vui lòng thử tải lại trang.</div>
              @endif
            </div>

            <div class="gateway-note mb-3">
              Trang này sẽ tự kiểm tra trạng thái thanh toán mỗi 3 giây. Nếu bạn đang chạy localhost, cần dùng ngrok/Cloudflare Tunnel để MoMo gọi được IPN thì trạng thái mới tự cập nhật sau khi quét QR.
            </div>

            <div class="d-flex flex-wrap gap-2">
              @if($deeplink)
                <a class="btn btn-cinema-primary" href="{{ $deeplink }}"><i class="bi bi-phone me-2"></i>Mở app MoMo</a>
              @endif
              @if($payUrl)
                <a class="btn btn-cinema-secondary" href="{{ $payUrl }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-2"></i>Mở trang thanh toán</a>
              @endif
              <a class="btn btn-cinema-secondary" href="{{ route('shows.book', ['show' => $booking->show_id, 'booking_code' => $booking->booking_code]) }}"><i class="bi bi-arrow-left me-2"></i>Quay lại</a>
            </div>
          @else
            <h2 class="h4 mb-3">VNPay</h2>
            <p class="text-light-emphasis">VNPay đang được giữ nguyên theo luồng cũ. Khi bạn muốn sửa VNPay, có thể cập nhật riêng sau.</p>
            <form method="POST" action="{{ route('booking.payment.callback', ['booking_code' => $booking->booking_code, 'payment' => $payment]) }}" class="d-flex flex-wrap gap-2">
              @csrf
              <button class="btn btn-cinema-primary" type="submit" name="result" value="success">Giả lập thanh toán thành công</button>
              <button class="btn btn-cinema-secondary" type="submit" name="result" value="failed">Giả lập thất bại</button>
              <button class="btn btn-cinema-secondary" type="submit" name="result" value="cancel">Huỷ</button>
            </form>
          @endif
        </div>

        <div class="gateway-card">
          <h2 class="h4 mb-3">Nội dung chuyển khoản</h2>
          <div class="payment-row"><span>Số tiền cần chuyển</span><strong class="amount-highlight">{{ number_format($payment->amount) }}đ</strong></div>
          <div class="payment-row"><span>Nội dung</span><strong>{{ $orderInfo }}</strong></div>
          <div class="transfer-content mt-3 mb-3">{{ $orderInfo }}</div>
          <div class="payment-row"><span>Mã booking</span><strong>{{ $booking->booking_code }}</strong></div>
          <div class="payment-row"><span>Mã giao dịch</span><strong>{{ $payment->external_txn_ref }}</strong></div>
          <div class="payment-row"><span>Phim</span><strong>{{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</strong></div>
          <div class="payment-row"><span>Suất chiếu</span><strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong></div>
          <div class="payment-row"><span>Phòng</span><strong>{{ $booking->show?->auditorium?->name ?: '—' }}</strong></div>
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
      if (data.paid && data.success_url) {
        window.location.href = data.success_url;
      } else if (data.failed && data.lookup_url) {
        window.location.href = data.lookup_url;
      }
    } catch (error) {}
  };

  tickCountdown();
  window.setInterval(tickCountdown, 1000);
  window.setInterval(pollPayment, 3000);
  window.setTimeout(pollPayment, 1000);
})();
</script>
@endif
@endpush
