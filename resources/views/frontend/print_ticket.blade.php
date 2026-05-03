<!doctype html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <title>In vé {{ $booking->booking_code }}</title>

  <style>
    @page { size: A4; margin: 10mm; }

    body {
      font-family: Arial, sans-serif;
      font-size: 13px;
      margin: 0;
      background: #fff;
      color: #000;
    }

    .toolbar {
      margin-bottom: 15px;
    }

    .btn {
      padding: 8px 14px;
      border: none;
      cursor: pointer;
      font-weight: bold;
    }

    .btn-print { background: black; color: white; }
    .btn-close { background: #ccc; }

    .ticket {
      width: 100%;
      border: 1px solid #ccc;
      margin-bottom: 12px;
      page-break-inside: avoid;
    }

    .ticket-table {
      width: 100%;
      border-collapse: collapse;
    }

    .ticket-main {
      width: 70%;
      padding: 12px;
      vertical-align: top;
    }

    .ticket-side {
      width: 30%;
      padding: 12px;
      border-left: 1px dashed #999;
      text-align: center;
      vertical-align: middle;
    }

    .title {
      font-size: 20px;
      font-weight: bold;
      margin-bottom: 8px;
    }

    .meta {
      margin-bottom: 6px;
    }

    .meta strong {
      display: inline-block;
      width: 120px;
    }

    .barcode img {
      width: 100%;
      max-width: 200px;
    }

    @media print {
      .toolbar { display: none; }
    }
  </style>
</head>

<body>

<div class="toolbar">
  <button class="btn btn-print" onclick="window.print()">In vé</button>
  <button class="btn btn-close" onclick="window.history.back()">Quay lại</button>

  <div>
    <strong>Booking:</strong> {{ $booking->booking_code }} <br>
    <strong>Người đặt:</strong> {{ $booking->contact_name ?? $booking->customer->full_name ?? 'Khách lẻ' }} <br>
    <strong>In lúc:</strong> {{ optional($printedAt)->format('d/m/Y H:i') }}
  </div>
</div>

@foreach($booking->tickets as $index => $bookingTicket)

@php
  $ticket = $bookingTicket->ticket;
  $scan = $ticket ? ticket_scan_payload($ticket) : null;
@endphp

<div class="ticket">
  <table class="ticket-table">
    <tr>

      <!-- LEFT -->
      <td class="ticket-main">

        <div class="title">
          {{ $booking->show->movieVersion->movie->title ?? 'Vé xem phim' }}
        </div>

        <div class="meta"><strong>Mã vé:</strong>
          {{ $ticket->ticket_code ?? 'Chưa có' }}
        </div>

        <div class="meta"><strong>Ghế:</strong>
          {{ $bookingTicket->seat->seat_code ?? ('#'.($bookingTicket->seat_id ?? '—')) }}
        </div>

        <div class="meta"><strong>Loại ghế:</strong>
          {{ $bookingTicket->seatType->name ?? 'Thường' }}
        </div>

        <div class="meta"><strong>Loại vé:</strong>
          {{ $bookingTicket->ticketType->name ?? 'Thường' }}
        </div>

        <div class="meta"><strong>Giá:</strong>
          {{ number_format($bookingTicket->final_price_amount ?? 0) }}đ
        </div>

        <div class="meta"><strong>Suất chiếu:</strong>
          {{ optional($booking->show->start_time)->format('d/m/Y H:i') ?? '—' }}
        </div>

        <div class="meta"><strong>Phòng:</strong>
          {{ $booking->show->auditorium->name ?? '—' }}
        </div>

        <div class="meta"><strong>Rạp:</strong>
          {{ $booking->show->auditorium->cinema->name ?? 'FPL Cinema' }}
        </div>

      </td>

      <!-- RIGHT -->
      <td class="ticket-side">

        @if($scan)
          <div class="barcode">
            <img src="data:image/png;base64,{{ ticket_barcode_base64($scan) }}">
          </div>

          <div style="margin-top:8px; font-weight:bold;">
            {{ $scan }}
          </div>
        @else
          <div>Chưa có mã</div>
        @endif

      </td>

    </tr>
  </table>
</div>

@endforeach

</body>
</html>