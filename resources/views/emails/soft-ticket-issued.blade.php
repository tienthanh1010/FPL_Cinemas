<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Vé điện tử {{ $booking->booking_code }}</title>
</head>
<body style="margin:0;padding:24px;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
  <div style="max-width:760px;margin:0 auto;background:#ffffff;border:1px solid #dbe4f0;border-radius:22px;padding:28px;overflow:hidden;">
    <div style="font-size:12px;letter-spacing:.12em;text-transform:uppercase;color:#64748b;margin-bottom:10px;">Vé điện tử FPL Cinema</div>
    <h1 style="margin:0 0 8px;font-size:28px;">{{ $booking->booking_code }}</h1>
    <p style="margin:0 0 22px;color:#475569;line-height:1.7;">Thanh toán của bạn đã hoàn tất. Vé bản mềm đã được phát hành và gửi đến email <strong>{{ $recipientEmail ?: 'người đặt' }}</strong>. Khi đến rạp, bạn chỉ cần đưa mã vé hoặc mã vạch dưới đây cho quầy / cổng soát vé.</p>

    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:16px;padding:18px 20px;margin-bottom:22px;">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
        <div><strong>Phim:</strong> {{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</div>
        <div><strong>Suất chiếu:</strong> {{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</div>
        <div><strong>Phòng / rạp:</strong> {{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: '—' }}</div>
        <div><strong>Khách hàng:</strong> {{ $booking->contact_name ?: ($booking->customer?->full_name ?: 'Khách hàng') }}</div>
      </div>
    </div>

    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;margin-bottom:22px;">
      <thead>
        <tr>
          <th align="left" style="padding:10px;border-bottom:1px solid #e2e8f0;color:#475569;">Ghế</th>
          <th align="left" style="padding:10px;border-bottom:1px solid #e2e8f0;color:#475569;">Loại vé</th>
          <th align="left" style="padding:10px;border-bottom:1px solid #e2e8f0;color:#475569;">Mã vé</th>
          <th align="right" style="padding:10px;border-bottom:1px solid #e2e8f0;color:#475569;">Giá</th>
        </tr>
      </thead>
      <tbody>
      @foreach($booking->tickets as $ticket)
        <tr>
          <td style="padding:10px;border-bottom:1px solid #eef2f7;">{{ $ticket->seat?->seat_code ?: '—' }}</td>
          <td style="padding:10px;border-bottom:1px solid #eef2f7;">{{ $ticket->ticketType?->name ?: 'Vé xem phim' }}</td>
          <td style="padding:10px;border-bottom:1px solid #eef2f7;font-family:monospace;">{{ $ticket->ticket?->ticket_code ?: 'Chưa phát hành' }}</td>
          <td align="right" style="padding:10px;border-bottom:1px solid #eef2f7;">{{ number_format($ticket->final_price_amount) }}đ</td>
        </tr>
      @endforeach
      </tbody>
    </table>

    @if($booking->tickets->isNotEmpty())
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
        @foreach($booking->tickets->take(2) as $ticket)
          @php($scanPayload = ticket_scan_payload($ticket->ticket))
          @if($scanPayload)
            <div style="border:1px solid #e2e8f0;border-radius:16px;padding:16px;text-align:center;background:#fff;">
              <div style="font-weight:700;margin-bottom:8px;">{{ $ticket->seat?->seat_code ?: 'Ghế' }} · {{ $ticket->ticket?->ticket_code ?: '—' }}</div>
              <img src="{{ ticket_barcode_image_url($scanPayload, 70) }}" alt="Barcode {{ $ticket->ticket?->ticket_code }}" style="max-width:100%;height:auto;background:#fff;padding:8px;border-radius:12px;border:1px solid #e2e8f0;">
              <div style="margin-top:8px;font-family:monospace;font-size:12px;color:#475569;">{{ $scanPayload }}</div>
            </div>
          @endif
        @endforeach
      </div>
    @endif

    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:14px;padding:16px 18px;margin-bottom:18px;color:#1d4ed8;">
      Tệp vé bản mềm dạng HTML đã được đính kèm trong email này. Bạn cũng có thể xem lại booking trên website bằng mã <strong>{{ $booking->booking_code }}</strong> và gửi lại vé qua email nếu cần.
    </div>

    <p style="margin:0;color:#64748b;font-size:13px;line-height:1.7;">Lưu ý: để email được gửi thật tới Gmail, bạn cần cấu hình SMTP trong file <code>.env</code> của dự án.</p>
  </div>
</body>
</html>
