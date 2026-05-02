<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Ve {{ $booking->booking_code }}</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#0f172a;padding:24px}
    .shell{max-width:920px;margin:0 auto;background:#fff;border:1px solid #dbe4f0;border-radius:20px;padding:28px}
    .code{font-size:28px;font-weight:700;margin:0 0 16px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px}
    .card{padding:14px 16px;border:1px solid #e2e8f0;border-radius:14px;background:#f8fafc}
    table{width:100%;border-collapse:collapse;margin-bottom:18px}
    th,td{padding:10px;border-bottom:1px solid #e2e8f0;text-align:left}
    .mono{font-family:monospace}
    .barcode-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .barcode-card{border:1px solid #e2e8f0;border-radius:14px;padding:14px;text-align:center;background:#fff}
    img{max-width:100%;height:auto}
  </style>
</head>
<body>
  <div class="shell">
    <div class="code">{{ $booking->booking_code }}</div>
    <div class="grid">
      <div class="card"><strong>Phim:</strong> {{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</div>
      <div class="card"><strong>Suất chiếu:</strong> {{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</div>
      <div class="card"><strong>Phòng / rạp:</strong> {{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</div>
      <div class="card"><strong>Khách:</strong> {{ $booking->contact_name ?: ($booking->customer?->full_name ?: 'Khách hàng') }}</div>
    </div>
    <table>
      <thead>
        <tr><th>Ghế</th><th>Loại vé</th><th>Mã vé</th><th>Trạng thái</th><th>Giá</th></tr>
      </thead>
      <tbody>
        @foreach($booking->tickets as $ticket)
          <tr>
            <td>{{ $ticket->seat?->seat_code ?: '—' }}</td>
            <td>{{ $ticket->ticketType?->name ?: 'Vé xem phim' }}</td>
            <td class="mono">{{ $ticket->ticket?->ticket_code ?: 'Chưa phát hành' }}</td>
            <td>{{ $ticket->ticket?->status ?: $ticket->status }}</td>
            <td>{{ number_format($ticket->final_price_amount) }}đ</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <div class="barcode-grid">
      @foreach($booking->tickets as $ticket)
        @php($scanPayload = ticket_scan_payload($ticket->ticket))
        @if($scanPayload)
          <div class="barcode-card">
            <div><strong>{{ $ticket->seat?->seat_code ?: 'Ghế' }}</strong> · {{ $ticket->ticket?->ticket_code ?: '—' }}</div>
            <img src="{{ ticket_barcode_image_url($scanPayload, 70) }}" alt="Barcode {{ $ticket->ticket?->ticket_code }}">
            <div class="mono">{{ $scanPayload }}</div>
          </div>
        @endif
      @endforeach
    </div>
  </div>
</body>
</html>
