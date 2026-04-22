@extends('frontend.layout')

@section('title', 'Thanh toán booking ' . $booking->booking_code)

@push('styles')
<style>
  .payment-shell{display:grid;grid-template-columns:minmax(0,1.15fr) minmax(320px,.85fr);gap:1.5rem}
  .payment-card{background:var(--panel-light);border:1px solid var(--line);border-radius:24px;padding:1.2rem;box-shadow:0 16px 32px rgba(15,23,42,.08)}
  .provider-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1rem}
  .provider-option{position:relative}
  .provider-option input{position:absolute;opacity:0;pointer-events:none}
  .provider-card{display:block;padding:1rem;border:1px solid var(--line);border-radius:22px;background:var(--surface-2);cursor:pointer;height:100%;transition:.18s ease}
  .provider-card strong{display:block;color:var(--text);margin-bottom:.35rem}
  .provider-card span{display:block;color:var(--muted);font-size:.92rem}
  .provider-option input:checked + .provider-card{border-color:color-mix(in srgb,var(--primary) 55%, var(--line));background:color-mix(in srgb,var(--primary) 10%, var(--surface-2));box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 18%, transparent)}
  .payment-row{display:flex;justify-content:space-between;gap:1rem;padding:.55rem 0;border-bottom:1px solid var(--line)}
  .payment-row:last-child{border-bottom:0}
  .payment-row span{color:var(--muted)}
  .payment-row strong{color:var(--text)}
  .payment-total{font-size:1.55rem}
  .payment-countdown{margin:1rem 0 1.2rem;padding:.9rem 1rem;border-radius:18px;background:var(--surface-2);border:1px solid var(--line)}
  .payment-countdown small{display:block;color:var(--muted);margin-bottom:.25rem;font-weight:700}
  .payment-countdown strong{font-size:1.5rem;color:var(--text)}
  @media (max-width: 991.98px){.payment-shell{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
  @php
    $amountDue = $amountDue ?? max(0, $booking->total_amount - $booking->paid_amount);
    $isTerminal = in_array($booking->status, ['CANCELLED', 'EXPIRED'], true);
    $isPaid = $amountDue <= 0 && in_array($booking->status, ['PAID', 'CONFIRMED', 'COMPLETED'], true);
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel p-4 p-lg-5">
        <span class="section-eyebrow">Thanh toán online</span>
        <h1 class="section-title mb-2">{{ $booking->booking_code }}</h1>
        <p class="section-copy mb-4">Chọn phương thức thanh toán để hoàn tất đơn vé. Sau khi thanh toán thành công, vé điện tử sẽ tự phát hành và sẵn sàng để in vé cứng hoặc check-in.</p>

        @if($isTerminal)
          <div class="booking-alert mb-4">Booking này đang ở trạng thái <strong>{{ $booking->status }}</strong> và không thể thanh toán tiếp.</div>
        @elseif($isPaid)
          <div class="booking-alert mb-4">Booking này đã được thanh toán đầy đủ. Bạn có thể xem vé đã phát hành ngay bây giờ.</div>
        @endif

        <div class="payment-shell">
          <div class="payment-card">
            <h2 class="h4 mb-3">Chọn phương thức thanh toán</h2>
            <p class="text-light-emphasis mb-4">Mô phỏng thanh toán online để hoàn thiện luồng đặt vé trong đồ án.</p>

            @if(! $isTerminal && ! $isPaid)
              <div class="payment-countdown">
                <small>Thời gian giữ booking còn lại</small>
                <strong id="paymentCountdownValue">00:00</strong>
              </div>

              <form method="POST" action="{{ route('booking.payment.pay', $booking->booking_code) }}">
                @csrf
                <div class="provider-grid mb-4">
                  @foreach($providerOptions as $providerCode => $provider)
                    <label class="provider-option">
                      <input type="radio" name="provider" value="{{ $providerCode }}" @checked(old('provider', array_key_first($providerOptions)) === $providerCode)>
                      <span class="provider-card">
                        <strong>{{ $provider['label'] }}</strong>
                        <span>{{ $provider['description'] }}</span>
                      </span>
                    </label>
                  @endforeach
                </div>

                <div class="d-flex flex-wrap gap-3">
                  <button class="btn btn-cinema-primary" type="submit">
                    <i class="bi bi-credit-card me-2"></i>Thanh toán ngay {{ number_format($amountDue) }}đ
                  </button>
                  <a class="btn btn-cinema-secondary" href="{{ route('shows.book', $booking->show_id) }}">
                    <i class="bi bi-arrow-left me-2"></i>Quay lại sửa ghế
                  </a>
                </div>
              </form>
            @else
              <div class="d-flex flex-wrap gap-3">
                <a class="btn btn-cinema-primary" href="{{ route('booking.success', $booking->booking_code) }}">Xem vé</a>
              </div>
            @endif
          </div>

          <div class="payment-card">
            <h2 class="h4 mb-3">Thông tin đơn vé</h2>
            <div class="payment-row"><span>Phim</span><strong>{{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</strong></div>
            <div class="payment-row"><span>Suất chiếu</span><strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong></div>
            <div class="payment-row"><span>Phòng / rạp</span><strong>{{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</strong></div>
            <div class="payment-row"><span>Ghế</span><strong>{{ $booking->tickets->pluck('seat.seat_code')->filter()->implode(', ') ?: '—' }}</strong></div>
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
    const remainSeconds = Math.floor(safe % 60).toString().padStart(2, '0');
    return `${minutes}:${remainSeconds}`;
  };

  const tick = () => {
    const secondsLeft = Math.max(0, Math.ceil((expiresAt.getTime() - Date.now()) / 1000));
    countdownNode.textContent = format(secondsLeft);
    if (secondsLeft <= 0) {
      window.clearInterval(timer);
      countdownNode.textContent = '00:00';
      window.location.reload();
    }
  };

  tick();
  const timer = window.setInterval(tick, 1000);
})();
</script>
@endpush
