@extends('frontend.layout')

@section('title', ($cinema->name ?? 'FPL Cinema') . ' | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@push('styles')
  <style>
    .cinema-profile-hero {
      position: relative;
      overflow: hidden;
      border: 1px solid var(--line);
      background:
        radial-gradient(circle at 16% 20%, rgba(255, 135, 89, .22), transparent 28%),
        radial-gradient(circle at 86% 10%, rgba(87, 209, 255, .16), transparent 26%),
        linear-gradient(135deg, rgba(255,255,255,.08), rgba(255,255,255,.03));
    }
    html[data-theme='light'] .cinema-profile-hero {
      background:
        radial-gradient(circle at 16% 20%, rgba(249, 115, 22, .12), transparent 28%),
        radial-gradient(circle at 86% 10%, rgba(37, 99, 235, .12), transparent 26%),
        linear-gradient(135deg, rgba(255,255,255,.96), rgba(247,249,253,.9));
    }
    .cinema-profile-hero__icon {
      width: 76px;
      height: 76px;
      border-radius: 26px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      color: #120d09;
      background: linear-gradient(135deg, var(--primary), #ffb46d);
      box-shadow: 0 18px 48px rgba(255, 135, 89, .28);
      font-size: 2.3rem;
      flex: 0 0 auto;
    }
    .cinema-contact-grid {
      display: grid;
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 14px;
    }
    .cinema-contact-card,
    .cinema-info-card {
      border: 1px solid var(--line);
      background: var(--panel-light);
      border-radius: 22px;
      padding: 18px;
    }
    .cinema-contact-card i,
    .cinema-info-card__icon {
      width: 42px;
      height: 42px;
      border-radius: 15px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background: rgba(87,209,255,.14);
      color: var(--secondary);
      margin-bottom: 12px;
      font-size: 1.2rem;
    }
    .cinema-contact-card span,
    .cinema-info-card small,
    .cinema-auditorium-card span {
      color: var(--muted);
    }
    .cinema-contact-card strong {
      display: block;
      color: var(--text);
      word-break: break-word;
    }
    .cinema-info-grid {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 16px;
    }
    .cinema-info-card strong {
      display: block;
      font-size: 1.7rem;
      line-height: 1;
      color: var(--text);
      margin-bottom: 6px;
    }
    .cinema-hours-list,
    .cinema-auditorium-list {
      display: grid;
      gap: 12px;
    }
    .cinema-hours-row,
    .cinema-auditorium-card {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding: 15px 16px;
      border: 1px solid var(--line);
      border-radius: 18px;
      background: var(--panel-light);
    }
    .cinema-auditorium-card {
      align-items: flex-start;
    }
    .cinema-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: .42rem .72rem;
      border-radius: 999px;
      background: rgba(255, 135, 89, .14);
      color: var(--primary);
      font-weight: 800;
      font-size: .78rem;
      white-space: nowrap;
    }
    @media (max-width: 991.98px) {
      .cinema-contact-grid,
      .cinema-info-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 575.98px) {
      .cinema-contact-grid,
      .cinema-info-grid { grid-template-columns: 1fr; }
      .cinema-hours-row,
      .cinema-auditorium-card { align-items: flex-start; flex-direction: column; }
    }
  </style>
@endpush

@section('content')
  @php
    $address = collect([$cinema->address_line, $cinema->ward, $cinema->district, $cinema->province])->filter()->implode(', ');
    $openingHours = $cinema->opening_hours ?? [];
    $dayLabels = admin_opening_hour_day_labels();
    $cinemaPhone = trim((string) ($cinema->phone ?: ''));
    $cinemaEmail = trim((string) ($cinema->email ?: ''));
    $cinemaPhone = $cinemaPhone === '' || in_array($cinemaPhone, ['1900 6868', '1900 1234', '+84-60-315-1643'], true) ? '0393312307' : $cinemaPhone;
    $cinemaEmail = $cinemaEmail === '' || in_array(mb_strtolower($cinemaEmail), ['support@fplcinema.local', 'support@cinevn.test', 'nhan99@example.org'], true) ? 'kientr2307@gmail.com' : $cinemaEmail;
    $activeAuditoriums = $cinema->auditoriums->count();
    $screenLabels = collect($screenTypeSummary)->keys()->implode(' · ') ?: 'STANDARD';
  @endphp

  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel cinema-profile-hero mb-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-4">
          <div class="d-flex align-items-start gap-3 gap-lg-4">
            <span class="cinema-profile-hero__icon"><i class="bi bi-building"></i></span>
            <div>
              <span class="section-eyebrow">Một rạp duy nhất</span>
              <h1 class="mb-3">{{ $cinema->name }}</h1>
              <p class="mb-0 text-secondary-emphasis" style="color: var(--muted) !important; line-height: 1.7; max-width: 780px;">
                Quản lý lịch chiếu, phòng chiếu và bán vé tập trung tại một rạp. Thông tin liên hệ, giờ mở cửa và hệ thống phòng được cập nhật tại đây.
              </p>
            </div>
          </div>
          <span class="cinema-badge"><i class="bi bi-check-circle me-2"></i>{{ $cinema->status }}</span>
        </div>

        <div class="cinema-contact-grid mt-4">
          <article class="cinema-contact-card">
            <i class="bi bi-geo-alt"></i>
            <span>Địa chỉ</span>
            <strong>{{ $address ?: 'Đang cập nhật' }}</strong>
          </article>
          <article class="cinema-contact-card">
            <i class="bi bi-telephone-outbound"></i>
            <span>Hotline</span>
            <strong>{{ $cinemaPhone }}</strong>
          </article>
          <article class="cinema-contact-card">
            <i class="bi bi-envelope"></i>
            <span>Email</span>
            <strong>{{ $cinemaEmail }}</strong>
          </article>
        </div>
      </div>

      <div class="cinema-info-grid mb-4">
        <article class="cinema-info-card">
          <span class="cinema-info-card__icon"><i class="bi bi-door-open"></i></span>
          <strong>{{ $activeAuditoriums }}</strong>
          <small>Phòng chiếu đang hoạt động</small>
        </article>
        <article class="cinema-info-card">
          <span class="cinema-info-card__icon"><i class="bi bi-badge-3d"></i></span>
          <strong>{{ count($screenTypeSummary) }}</strong>
          <small>{{ $screenLabels }}</small>
        </article>
        <article class="cinema-info-card">
          <span class="cinema-info-card__icon"><i class="bi bi-upc-scan"></i></span>
          <strong>{{ $cinema->cinema_code }}</strong>
          <small>Mã rạp</small>
        </article>
        <article class="cinema-info-card">
          <span class="cinema-info-card__icon"><i class="bi bi-clock-history"></i></span>
          <strong>{{ $cinema->timezone }}</strong>
          <small>Múi giờ vận hành</small>
        </article>
      </div>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="glass-panel h-100">
            <div class="section-heading mb-3">
              <div>
                <span class="section-eyebrow">Lịch vận hành</span>
                <h2 class="h4 mb-0">Giờ mở cửa</h2>
              </div>
            </div>
            <div class="cinema-hours-list">
              @foreach($dayLabels as $dayKey => $dayLabel)
                <div class="cinema-hours-row">
                  <strong>{{ $dayLabel }}</strong>
                  <span class="cinema-badge">{{ $openingHours[$dayKey] ?? 'Chưa thiết lập' }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-7">
          <div class="glass-panel h-100">
            <div class="section-heading mb-3">
              <div>
                <span class="section-eyebrow">Phòng chiếu</span>
                <h2 class="h4 mb-0">Hệ thống phòng chiếu</h2>
              </div>
            </div>
            <div class="cinema-auditorium-list">
              @forelse($cinema->auditoriums as $auditorium)
                <article class="cinema-auditorium-card">
                  <div>
                    <strong>{{ $auditorium->name }}</strong>
                    <span class="d-block mt-1">{{ $auditorium->auditorium_code }} · Seat map v{{ $auditorium->seat_map_version }}</span>
                  </div>
                  <span class="cinema-badge">{{ strtoupper($auditorium->screen_type ?: 'STANDARD') }}</span>
                </article>
              @empty
                <div class="empty-panel">Chưa có dữ liệu phòng chiếu.</div>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
