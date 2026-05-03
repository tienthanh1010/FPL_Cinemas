@extends('frontend.layout')

@section('title', 'Booking thành công')

@section('content')
  <div class="card">
    <div class="card-body">
      <h1 class="h4">Booking đã tạo</h1>

      <div class="row g-3 mt-2">
        <div class="col-md-6">
          <div><span class="text-muted">Mã booking:</span> <span class="fw-semibold">{{ $booking->booking_code }}</span></div>
          <div><span class="text-muted">Trạng thái:</span> <span class="badge text-bg-warning">{{ $booking->status }}</span></div>
          <div><span class="text-muted">Tổng tiền:</span> <span class="fw-semibold">{{ number_format($booking->total_amount) }} VND</span></div>
          <div><span class="text-muted">Hết hạn:</span> {{ optional($booking->expires_at)->format('Y-m-d H:i') }}</div>
        </div>
        <div class="col-md-6">
          <div><span class="text-muted">Khách:</span> {{ $booking->contact_name }}</div>
          <div><span class="text-muted">Phone:</span> {{ $booking->contact_phone }}</div>
          <div><span class="text-muted">Email:</span> {{ $booking->contact_email ?: '-' }}</div>
        </div>
      </div>

      <hr>

      <h2 class="h6">Vé (booking_tickets)</h2>
      <div class="table-responsive">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>#</th>
              <th>Seat ID</th>
              <th>Price</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            @foreach($booking->tickets as $t)
              <tr>
                <td>{{ $t->id }}</td>
                <td>{{ $t->seat_id }}</td>
                <td>{{ number_format($t->final_price_amount) }} VND</td>
                <td>{{ $t->status }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>

      <a class="btn btn-outline-secondary" href="{{ route('home') }}">← Về trang chủ</a>
    </div>
  </div>
@endsection
