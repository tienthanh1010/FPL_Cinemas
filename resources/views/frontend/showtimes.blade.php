@extends('frontend.layout')

@section('title', 'Suất chiếu: ' . $movie->title)

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-1">{{ $movie->title }}</h1>
      <div class="text-muted small">{{ $movie->duration_minutes }} phút · {{ optional($movie->release_date)->format('Y-m-d') }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('home') }}">← Trang chủ</a>
  </div>

  <div class="row g-3">
    <div class="col-lg-7">
      <div class="card">
        <div class="card-header fw-semibold">Danh sách suất chiếu</div>
        <div class="list-group list-group-flush">
          @forelse($shows as $s)
            <div class="list-group-item">
              <div class="d-flex justify-content-between">
                <div>
                  <div class="fw-semibold">
                    {{ $s->start_time->format('Y-m-d H:i') }} → {{ $s->end_time->format('H:i') }}
                    <span class="badge text-bg-success ms-2">{{ $s->status }}</span>
                  </div>
                  <div class="text-muted small">
                    {{ $s->auditorium->cinema->name }} · {{ $s->auditorium->name }} · {{ $s->movieVersion->format }}
                  </div>
                </div>
                <div class="text-end">
                  <div class="small text-muted">Show ID: {{ $s->id }}</div>
                </div>
              </div>
            </div>
          @empty
            <div class="list-group-item text-muted">Chưa có suất chiếu (seed demo có 4 suất trong SQL).</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-5">
      <div class="card">
        <div class="card-header fw-semibold">Tạo booking cơ bản</div>
        <div class="card-body">
          <form method="POST" action="{{ route('booking.store') }}">
            @csrf

            <div class="mb-3">
              <label class="form-label">Chọn suất chiếu</label>
              <select class="form-select" name="show_id" required>
                @foreach($shows as $s)
                  <option value="{{ $s->id }}">#{{ $s->id }} · {{ $s->start_time->format('Y-m-d H:i') }} · {{ $s->auditorium->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="row g-2">
              <div class="col-6 mb-3">
                <label class="form-label">Số vé</label>
                <input class="form-control" type="number" min="1" max="10" name="qty" value="{{ old('qty', 2) }}" required>
              </div>
              <div class="col-6 mb-3">
                <label class="form-label">Điện thoại</label>
                <input class="form-control" name="contact_phone" value="{{ old('contact_phone', '0900000000') }}" required>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Họ tên</label>
              <input class="form-control" name="contact_name" value="{{ old('contact_name', 'Guest') }}" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email (optional)</label>
              <input class="form-control" type="email" name="contact_email" value="{{ old('contact_email') }}">
            </div>

            <button class="btn btn-dark w-100" type="submit">Đặt vé (demo)</button>
            <div class="form-text mt-2">Demo sẽ tự chọn ghế trống đầu tiên và tạo booking trạng thái PENDING.</div>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
