<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>In vé {{ $booking->booking_code }}</title>
  <style>
    @page { size: A4 portrait; margin: 10mm; }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: DejaVu Sans, Arial, sans-serif;
      background: #eef2f7;
      color: #0f172a;
      padding: 20px;
    }
    .toolbar {
      max-width: 860px;
      margin: 0 auto 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .toolbar__meta {
      color: #475569;
      font-size: 13px;
      line-height: 1.55;
    }
    .toolbar__meta strong { color: #0f172a; }
    .toolbar__actions {
      display: inline-flex;
      gap: 10px;
      flex-wrap: wrap;
    }
    .btn {
      border: 0;
      border-radius: 999px;
      padding: 11px 18px;
      font-weight: 800;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-primary { background: #0f172a; color: #fff; }
    .btn-light { background: #dbe3ee; color: #0f172a; }
    .sheet {
      max-width: 860px;
      margin: 0 auto;
      display: grid;
      gap: 14px;
    }
    .ticket {
      background: #fff;
      border: 1px solid #dbe3ee;
      border-radius: 24px;
      overflow: hidden;
      display: grid;
      grid-template-columns: minmax(0, 1.5fr) minmax(260px, .75fr);
      box-shadow: 0 12px 30px rgba(15, 23, 42, .08);
      break-inside: avoid;
      page-break-inside: avoid;
    }
    .ticket__main {
      padding: 22px 24px;
      position: relative;
    }
    .ticket__main::after {
      content: '';
      position: absolute;
      top: 18px;
      right: -10px;
      width: 20px;
      height: calc(100% - 36px);
      background:
        radial-gradient(circle at center, #eef2f7 0 7px, transparent 7.5px) center / 20px 28px repeat-y;
    }
    .ticket__side {
      padding: 22px 20px;
      border-left: 1px dashed #c8d3e0;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      text-align: center;
      background: linear-gradient(180deg, #fcfdff 0%, #f7f9fc 100%);
    }
    .brand {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      font-size: 13px;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: .14em;
      color: #64748b;
    }
    .brand__dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: #0f172a;
      box-shadow: 0 0 0 6px rgba(15, 23, 42, .08);
    }
    .movie-title {
      font-size: 29px;
      line-height: 1.18;
      font-weight: 900;
      margin: 14px 0 18px;
      color: #111827;
    }
    .ticket-head {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      align-items: flex-start;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    .ticket-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      border-radius: 999px;
      padding: 7px 12px;
      background: #eef4ff;
      color: #1d4ed8;
      font-size: 12px;
      font-weight: 800;
    }
    .ticket-code-box {
      text-align: right;
    }
    .ticket-code-box span {
      display: block;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #64748b;
      margin-bottom: 4px;
    }
    .ticket-code-box strong {
      font-size: 21px;
      letter-spacing: .04em;
      color: #0f172a;
    }
    .meta-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 12px 16px;
    }
    .meta-item {
      border-radius: 16px;
      background: #f8fafc;
      border: 1px solid #e5edf5;
      padding: 12px 13px;
    }
    .meta-item span {
      display: block;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: .08em;
      color: #64748b;
      margin-bottom: 5px;
    }
    .meta-item strong {
      display: block;
      font-size: 15px;
      line-height: 1.45;
      color: #111827;
    }
    .meta-item strong small {
      font-size: 12px;
      color: #64748b;
      font-weight: 700;
    }
    .ticket-note {
      margin-top: 15px;
      padding-top: 14px;
      border-top: 1px dashed #d3dce8;
      color: #64748b;
      font-size: 12px;
      line-height: 1.7;
    }
    .barcode-title {
      color: #334155;
      font-size: 12px;
      text-transform: uppercase;
      letter-spacing: .1em;
      font-weight: 800;
      margin-bottom: 10px;
    }
    .barcode-box {
      width: 100%;
      max-width: 285px;
      background: #fff;
      padding: 12px;
      border: 1px solid #dde5ef;
      border-radius: 16px;
      box-shadow: inset 0 0 0 1px rgba(255,255,255,.7);
    }
    .barcode-box img {
      width: 100%;
      display: block;
    }
    .scan-code {
      margin-top: 12px;
      font-size: 19px;
      font-weight: 900;
      letter-spacing: .06em;
      color: #0f172a;
      word-break: break-word;
    }
    .side-foot {
      margin-top: 12px;
      color: #64748b;
      font-size: 12px;
      line-height: 1.6;
    }
    @media (max-width: 768px) {
      body { padding: 12px; }
      .ticket { grid-template-columns: 1fr; }
      .ticket__main::after { display: none; }
      .ticket__side { border-left: 0; border-top: 1px dashed #c8d3e0; }
      .meta-grid { grid-template-columns: 1fr; }
    }
    @media print {
      body { background: #fff; padding: 0; }
      .toolbar { display: none; }
      .sheet { max-width: none; gap: 10px; }
      .ticket { box-shadow: none; }
    }
  </style>
</head>
<body>
  <div class="toolbar">
    <div class="toolbar__meta">
      <div><strong>Booking:</strong> {{ $booking->booking_code }}</div>
      <div><strong>Người đặt:</strong> {{ $booking->contact_name ?: ($booking->customer?->full_name ?: 'Khách lẻ') }}</div>
      <div><strong>In lúc:</strong> {{ optional($printedAt)->format('d/m/Y H:i') }}</div>
      @if($lastSuccessfulPayment?->paid_at)
        <div><strong>Thanh toán:</strong> {{ $lastSuccessfulPayment->paid_at->format('d/m/Y H:i') }} · {{ $lastSuccessfulPayment->provider ?: ($lastSuccessfulPayment->method ?: 'Online') }}</div>
      @endif
    </div>
    <div class="toolbar__actions">
      <button class="btn btn-light" onclick="window.close()">Đóng</button>
      <button class="btn btn-primary" onclick="window.print()">In vé</button>
    </div>
  </div>

  <div class="sheet">
    @foreach($booking->tickets as $index => $bookingTicket)
      @php
        $electronicTicket = $bookingTicket->ticket;
        $scanPayload = ticket_scan_payload($electronicTicket);
      @endphp
      <section class="ticket">
        <div class="ticket__main">
          <div class="ticket-head">
            <div>
              <div class="brand"><span class="brand__dot"></span>FPL Cinema · Vé cứng</div>
              <div class="movie-title">{{ $booking->show?->movieVersion?->movie?->title ?: 'Vé xem phim' }}</div>
              <div class="ticket-badge">Vé #{{ $index + 1 }} · {{ $booking->show?->movieVersion?->format ?: '2D' }}</div>
            </div>
            <div class="ticket-code-box">
              <span>Mã vé điện tử</span>
              <strong>{{ $electronicTicket?->ticket_code ?: 'Chưa phát hành' }}</strong>
            </div>
          </div>

          <div class="meta-grid">
            <div class="meta-item">
              <span>Ghế</span>
              <strong>{{ $bookingTicket->seat?->seat_code ?: ('#'.$bookingTicket->seat_id) }}</strong>
            </div>
            <div class="meta-item">
              <span>Loại ghế</span>
              <strong>{{ $bookingTicket->seatType?->name ?: 'Ghế tiêu chuẩn' }}</strong>
            </div>
            <div class="meta-item">
              <span>Loại vé</span>
              <strong>{{ $bookingTicket->ticketType?->name ?: 'Vé xem phim' }}</strong>
            </div>
            <div class="meta-item">
              <span>Giá đã thanh toán</span>
              <strong>{{ number_format($bookingTicket->final_price_amount) }}đ</strong>
            </div>
            <div class="meta-item">
              <span>Suất chiếu</span>
              <strong>{{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</strong>
            </div>
            <div class="meta-item">
              <span>Phòng chiếu</span>
              <strong>{{ $booking->show?->auditorium?->name ?: '—' }}<br><small>{{ $booking->show?->auditorium?->cinema?->name ?: 'FPL Cinema' }}</small></strong>
            </div>
            <div class="meta-item">
              <span>Mã booking</span>
              <strong>{{ $booking->booking_code }}</strong>
            </div>
            <div class="meta-item">
              <span>Độ tuổi</span>
              <strong>{{ $booking->show?->movieVersion?->movie?->contentRating?->code ?: 'P' }}</strong>
            </div>
          </div>

          <div class="ticket-note">
            Vui lòng mang vé này đến quầy hoặc cổng soát vé để đối chiếu mã vạch. Vé chỉ hợp lệ cho đúng suất chiếu, ghế và thời gian được in trên vé.
          </div>
        </div>

        <aside class="ticket__side">
          <div class="barcode-title">Mã vạch check-in</div>
          @if($scanPayload)
            <div class="barcode-box">
              <img src="{{ ticket_barcode_image_url($scanPayload, 92) }}" alt="Barcode {{ $electronicTicket?->ticket_code }}">
            </div>
            <div class="scan-code">{{ $scanPayload }}</div>
            <div class="side-foot">Đưa mã này cho nhân viên soát vé hoặc máy quét để check-in.</div>
          @else
            <div class="side-foot">Booking này chưa phát hành mã vạch.</div>
          @endif
        </aside>
      </section>
    @endforeach
  </div>
</body>
</html>
