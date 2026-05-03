<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>Vé điện tử {{ $booking->booking_code }}</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f6f7fb;margin:0;padding:24px;color:#111827;">
  <div style="max-width:720px;margin:0 auto;background:#ffffff;border-radius:18px;padding:24px;border:1px solid #e5e7eb;">
    <h1 style="margin:0 0 8px;font-size:24px;color:#111827;">Vé điện tử của bạn</h1>
    <p style="margin:0 0 18px;color:#6b7280;">Booking <strong>{{ $booking->booking_code }}</strong> đã thanh toán thành công.</p>

    <div style="background:#f9fafb;border-radius:14px;padding:16px;margin-bottom:18px;">
      <p><strong>Phim:</strong> {{ $booking->show?->movieVersion?->movie?->title ?: '—' }}</p>
      <p><strong>Suất chiếu:</strong> {{ optional($booking->show?->start_time)->format('d/m/Y H:i') ?: '—' }}</p>
      <p><strong>Phòng:</strong> {{ $booking->show?->auditorium?->name ?: '—' }} · {{ $booking->show?->auditorium?->cinema?->name ?: config('app.name') }}</p>
      <p><strong>Tổng tiền:</strong> {{ number_format((int) $booking->total_amount) }}đ</p>
    </div>

    <h2 style="font-size:18px;margin:0 0 12px;">Danh sách vé</h2>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr>
          <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Ghế</th>
          <th style="text-align:left;border-bottom:1px solid #e5e7eb;padding:8px;">Mã vé</th>
          <th style="text-align:right;border-bottom:1px solid #e5e7eb;padding:8px;">Giá</th>
        </tr>
      </thead>
      <tbody>
        @foreach($booking->tickets as $bookingTicket)
          <tr>
            <td style="border-bottom:1px solid #f3f4f6;padding:8px;">{{ $bookingTicket->seat?->seat_code ?: '—' }}</td>
            <td style="border-bottom:1px solid #f3f4f6;padding:8px;font-weight:bold;">{{ $bookingTicket->ticket?->ticket_code ?: 'Đang cập nhật' }}</td>
            <td style="border-bottom:1px solid #f3f4f6;padding:8px;text-align:right;">{{ number_format((int) $bookingTicket->final_price_amount) }}đ</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    <p style="margin-top:18px;color:#6b7280;">Vui lòng đưa mã vé hoặc mở trang chi tiết booking khi đến rạp để soát vé.</p>
  </div>
</body>
</html>
