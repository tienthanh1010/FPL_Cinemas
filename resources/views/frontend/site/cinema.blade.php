@extends('frontend.layout')

@section('title', ($cinema->name ?? 'FPL Cinema') . ' | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  @php
    $address = collect([$cinema->address_line, $cinema->ward, $cinema->district, $cinema->province])->filter()->implode(', ');
    $openingHours = $cinema->opening_hours ?? [];
    $dayLabels = admin_opening_hour_day_labels();
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4">
        <span class="section-eyebrow">Một rạp duy nhất</span>
        <h1>{{ $cinema->name }}</h1>
<<<<<<< HEAD
=======
        <p class="mb-0">Toàn bộ website hiện đã được chuẩn hóa theo mô hình một rạp tuyệt đối, tập trung mọi lịch chiếu, booking, thanh toán, nội dung và vận hành về {{ $cinema->name }}.</p>
>>>>>>> origin/main
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-7">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Thông tin rạp</h2>
            <div class="account-info-list site-info-list">
              <div><span>Tên rạp</span><strong>{{ $cinema->name }}</strong></div>
              <div><span>Mã rạp</span><strong>{{ $cinema->cinema_code }}</strong></div>
              <div><span>Địa chỉ</span><strong>{{ $address ?: 'Đang cập nhật' }}</strong></div>
              <div><span>Hotline</span><strong>{{ $cinema->phone ?: 'Đang cập nhật' }}</strong></div>
              <div><span>Email</span><strong>{{ $cinema->email ?: 'Đang cập nhật' }}</strong></div>
              <div><span>Timezone</span><strong>{{ $cinema->timezone }}</strong></div>
            </div>
          </div>
        </div>
        <div class="col-lg-5">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Tổng quan vận hành</h2>
            <div class="account-kpi-grid">
              <article class="account-kpi-card">
                <small>Phòng chiếu</small>
                <strong>{{ $cinema->auditoriums->count() }}</strong>
                <span>Đang hoạt động tại một địa điểm duy nhất</span>
              </article>
              <article class="account-kpi-card">
                <small>Loại màn hình</small>
                <strong>{{ count($screenTypeSummary) }}</strong>
                <span>{{ collect($screenTypeSummary)->keys()->implode(' · ') ?: 'STANDARD' }}</span>
              </article>
              <article class="account-kpi-card">
                <small>Trạng thái</small>
                <strong>{{ $cinema->status }}</strong>
                <span>Rạp chính thức của hệ thống</span>
              </article>
              <article class="account-kpi-card">
                <small>Quốc gia</small>
                <strong>{{ $cinema->country_code }}</strong>
                <span>Thiết lập chuẩn cho dữ liệu một rạp</span>
              </article>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Giờ mở cửa</h2>
            <div class="related-post-list">
              @foreach($dayLabels as $dayKey => $dayLabel)
                <div class="related-post-item d-flex justify-content-between align-items-center gap-3">
                  <strong>{{ $dayLabel }}</strong>
                  <span class="mt-0">{{ $openingHours[$dayKey] ?? 'Chưa thiết lập' }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Hệ thống phòng chiếu</h2>
            <div class="related-post-list">
              @forelse($cinema->auditoriums as $auditorium)
                <div class="related-post-item d-flex justify-content-between align-items-center gap-3">
                  <div>
                    <strong>{{ $auditorium->name }}</strong>
                    <span>{{ $auditorium->auditorium_code }} · Seat map v{{ $auditorium->seat_map_version }}</span>
                  </div>
                  <span class="mt-0 content-tag">{{ strtoupper($auditorium->screen_type ?: 'STANDARD') }}</span>
                </div>
              @empty
                <div class="text-muted">Chưa có dữ liệu phòng chiếu.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
