@extends('frontend.layout')

<<<<<<< HEAD
@section('title', $movie->title . ' | Lịch chiếu')

@push('styles')
<style>
  .schedule-page-hero {
    display: grid;
    grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    gap: 1.5rem;
    align-items: stretch;
  }
  .schedule-page-hero__poster {
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.08);
    min-height: 380px;
  }
  .schedule-page-hero__poster img {
    width: 100%; height: 100%; object-fit: cover; display: block;
  }
  .schedule-page-hero__copy {
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 1rem;
  }
  .schedule-page-hero__copy h1 { margin: 0; }
  .schedule-page-hero__copy p { margin: 0; color: var(--muted); }

  .schedule-board {
    --schedule-surface: linear-gradient(180deg, rgba(8, 15, 30, .96) 0%, rgba(10, 20, 38, .94) 100%);
    --schedule-text: #edf3ff;
    --schedule-muted: #9fb0ca;
    --schedule-line: rgba(255,255,255,.10);
    --schedule-card: rgba(255,255,255,.06);
    --schedule-card-strong: linear-gradient(180deg, rgba(26, 72, 137, .42) 0%, rgba(19, 46, 84, .64) 100%);
    --schedule-card-border: rgba(255,255,255,.10);
    --schedule-chip: rgba(255,255,255,.08);
    --schedule-chip-text: #dbe7ff;
    background: var(--schedule-surface);
    color: var(--schedule-text);
    border-radius: 26px;
    padding: 1.5rem;
    border: 1px solid var(--schedule-line);
    box-shadow: 0 18px 46px rgba(15,23,42,.18);
  }
  html[data-theme='light'] .schedule-board {
    --schedule-surface: #f6f6f6;
    --schedule-text: #1f2937;
    --schedule-muted: #475569;
    --schedule-line: rgba(17,24,39,.10);
    --schedule-card: #d7e3f4;
    --schedule-card-strong: linear-gradient(180deg, #e9f1ff 0%, #cfe0ff 100%);
    --schedule-card-border: rgba(15,90,166,.18);
    --schedule-chip: rgba(15,23,42,.08);
    --schedule-chip-text: #334155;
    box-shadow: 0 18px 46px rgba(15,23,42,.08);
  }
  .schedule-board__heading {
    text-align: center;
    font-size: clamp(1.8rem, 3vw, 2.7rem);
    font-weight: 800;
    margin: .4rem 0 1.5rem;
    color: var(--schedule-text);
  }
  .schedule-board .nav-tabs {
    border-bottom: 1px solid var(--schedule-line);
    gap: .8rem;
    flex-wrap: nowrap;
    overflow: auto;
    padding-bottom: .2rem;
  }
  .schedule-board .nav-tabs .nav-link {
    border: 0;
    background: transparent;
    color: var(--schedule-text);
    padding: .35rem .4rem .85rem;
    border-radius: 0;
    border-bottom: 3px solid transparent;
    display: inline-flex;
    align-items: flex-end;
    gap: .15rem;
    min-width: 138px;
  }
  .schedule-board .nav-tabs .nav-link.active {
    color: var(--secondary);
    background: transparent;
    border-color: var(--secondary);
  }
  .schedule-board .nav-tabs .nav-link:hover { border-color: color-mix(in srgb, var(--secondary) 45%, transparent); }
  .schedule-date-number { font-size: 3rem; font-weight: 900; line-height: .9; }
  .schedule-date-meta { font-size: 1.1rem; font-weight: 700; line-height: 1.1; margin-bottom: .2rem; }

  .schedule-pane { padding-top: 2rem; }
  .schedule-format-group + .schedule-format-group { margin-top: 1.75rem; }
  .schedule-format-title {
    font-size: 2rem;
    font-weight: 900;
    color: var(--schedule-text);
    margin-bottom: 1rem;
    text-transform: uppercase;
  }
  .schedule-show-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
  }
  .schedule-show-card {
    width: min(100%, 180px);
    background: var(--schedule-card);
    border-radius: 18px;
    padding: 1rem;
    text-align: center;
    box-shadow: inset 0 -6px 0 rgba(15,23,42,.10);
    border: 1px solid var(--schedule-card-border);
  }
  .schedule-show-card.is-on-sale {
    background: var(--schedule-card-strong);
    border-color: var(--schedule-card-border);
  }
  .schedule-show-card__time {
    font-size: 2rem;
    font-weight: 900;
    line-height: 1;
    color: var(--schedule-text);
  }
  .schedule-show-card__date {
    margin-top: .4rem;
    font-size: 1rem;
    font-weight: 700;
    color: var(--schedule-muted);
  }
  .schedule-show-card__meta {
    margin-top: .65rem;
    font-size: .9rem;
    color: var(--schedule-muted);
    min-height: 2.6em;
  }
  .schedule-show-card__status {
    display: inline-flex;
    margin-top: .7rem;
    padding: .35rem .7rem;
    border-radius: 999px;
    background: var(--schedule-chip);
    font-size: .78rem;
    font-weight: 800;
    color: var(--schedule-muted);
  }
  .schedule-show-card__footer { margin-top: .9rem; }
  .schedule-show-card__button {
    display: inline-flex;
    width: 100%;
    justify-content: center;
    align-items: center;
    padding: .75rem .8rem;
    border-radius: 12px;
    font-weight: 800;
    background: var(--primary);
    color: var(--on-primary, #fff);
  }
  .schedule-show-card__button:hover { color: var(--on-primary, #fff); background: var(--primary-strong); }
  .schedule-show-card__button.is-disabled {
    background: var(--schedule-chip);
    color: var(--schedule-muted);
    pointer-events: none;
  }

  @media (max-width: 991.98px) {
    .schedule-page-hero { grid-template-columns: 1fr; }
    .schedule-page-hero__poster { max-width: 280px; }
  }
</style>
@endpush
=======
<<<<<<< HEAD
@section('title', 'Lịch chiếu ' . $movie->title)
=======
@section('title', 'Đặt vé ' . $movie->title)

@php
  $defaultShowId = old('show_id', optional($bookableShows->first())->id);
@endphp
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
<<<<<<< HEAD
      <div class="glass-panel mb-4 mb-xl-5">
        <div class="schedule-page-hero">
          <div class="schedule-page-hero__poster">
            @if($movie->poster_url)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
            @else
              <div class="poster-fallback poster-fallback--showtime"><span>{{ $movie->title }}</span></div>
            @endif
          </div>
          <div class="schedule-page-hero__copy">
            <span class="section-eyebrow">Lịch chiếu chi tiết</span>
            <h1>{{ $movie->title }}</h1>
            <p>{{ $movie->synopsis ? \Illuminate\Support\Str::limit($movie->synopsis, 220) : 'Chọn ngày và suất chiếu phù hợp để chuyển sang trang đặt vé riêng, nơi bạn có thể chọn ghế trực quan trên sơ đồ lớn.' }}</p>
            <div class="hero-meta hero-meta--compact">
              <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
              <span><i class="bi bi-calendar-event me-2"></i>{{ optional($movie->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
              <span><i class="bi bi-tags me-2"></i>{{ $movie->genres->pluck('name')->implode(' · ') ?: 'Chưa gán thể loại' }}</span>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-arrow-left me-2"></i>Trở lại trang chủ</a>
            </div>
          </div>
        </div>
      </div>

      <div class="schedule-board">
        <div class="section-eyebrow text-uppercase mb-2">Lịch chiếu của phim</div>
        <div class="schedule-board__heading">{{ $movie->title }}</div>

        @if($showsByDate->isNotEmpty())
          <ul class="nav nav-tabs" id="showDateTab" role="tablist">
            @foreach($showsByDate as $date => $dateShows)
              @php($dateCarbon = \Carbon\Carbon::parse($date))
              <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="show-date-tab-{{ $loop->index }}" data-bs-toggle="tab" data-bs-target="#show-date-pane-{{ $loop->index }}" type="button" role="tab">
                  <span class="schedule-date-number">{{ $dateCarbon->format('d') }}</span>
                  <span class="schedule-date-meta">/{{ $dateCarbon->format('m') }} - {{ mb_strtoupper($dateCarbon->translatedFormat('D')) }}</span>
                </button>
              </li>
            @endforeach
          </ul>

          <div class="tab-content">
            @foreach($showsByDate as $date => $dateShows)
              @php($showsByFormat = $dateShows->groupBy(fn ($show) => $show->movieVersion?->format ?: '2D'))
              <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }} schedule-pane" id="show-date-pane-{{ $loop->index }}" role="tabpanel">
                @foreach($showsByFormat as $format => $formatShows)
                  <div class="schedule-format-group">
                    <div class="schedule-format-title">{{ $format }}</div>
                    <div class="schedule-show-grid">
                      @foreach($formatShows as $show)
                        <div class="schedule-show-card {{ $show->isOnSaleNow() ? 'is-on-sale' : '' }}">
                          <div class="schedule-show-card__time">{{ $show->start_time->format('H:i') }}</div>
                          <div class="schedule-show-card__date">{{ $show->start_time->format('d/m') }}</div>
                          <div class="schedule-show-card__meta">{{ $show->auditorium->name }} · {{ $show->auditorium->cinema->name }}</div>
                          <div class="schedule-show-card__status">{{ $show->frontendStatusLabel() }}</div>
                          <div class="schedule-show-card__footer">
                            @if($show->isOnSaleNow())
                              <a href="{{ route('shows.book', $show) }}" class="schedule-show-card__button">Đặt vé</a>
                            @else
                              <span class="schedule-show-card__button is-disabled">{{ $show->frontendStatusLabel() }}</span>
                            @endif
                          </div>
                        </div>
                      @endforeach
                    </div>
                  </div>
                @endforeach
              </div>
            @endforeach
          </div>
        @else
          <div class="empty-panel">Chưa có suất chiếu nào cho phim này.</div>
        @endif
      </div>
=======
      <div class="showtime-hero glass-panel mb-4">
        <div class="showtime-hero__poster">
          @if($movie->poster_url)
            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
          @else
            <div class="poster-fallback poster-fallback--showtime"><span>{{ $movie->title }}</span></div>
          @endif
        </div>
        <div class="showtime-hero__copy">
<<<<<<< HEAD
          <span class="section-eyebrow">Lịch chiếu chi tiết</span>
          <h1>{{ $movie->title }}</h1>
          <p>{{ $movie->synopsis ? \Illuminate\Support\Str::limit($movie->synopsis, 260) : 'Giao diện lịch chiếu được thiết kế lại theo dạng thẻ ngày và thẻ suất, trực quan hơn và tách biệt rõ với website tham chiếu.' }}</p>
          <div class="hero-meta hero-meta--compact">
            <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
            <span><i class="bi bi-calendar-event me-2"></i>{{ optional($movie->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
=======
          <span class="section-eyebrow">Trang chi tiết phim & đặt vé</span>
          <h1>{{ $movie->title }}</h1>
          <p>{{ $movie->synopsis ? \Illuminate\Support\Str::limit($movie->synopsis, 320) : 'Trang này đã được ghép đầy đủ phần trailer, lịch chiếu, sơ đồ ghế, thanh toán mô phỏng và đánh giá khách hàng.' }}</p>
          <div class="hero-meta hero-meta--compact">
            <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
            <span><i class="bi bi-calendar-event me-2"></i>{{ optional($movie->release_date)->format('d/m/Y') ?: 'Đang cập nhật' }}</span>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
            <span><i class="bi bi-tags me-2"></i>{{ $movie->genres->pluck('name')->implode(' · ') ?: 'Chưa gán thể loại' }}</span>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-cinema-primary" href="#booking-form"><i class="bi bi-ticket-detailed me-2"></i>Đặt vé ngay</a>
<<<<<<< HEAD
            <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-arrow-left me-2"></i>Trở lại trang chủ</a>
=======
            <a class="btn btn-cinema-secondary" href="#reviews"><i class="bi bi-star-half me-2"></i>Xem đánh giá</a>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
          </div>
        </div>
      </div>

<<<<<<< HEAD
      <div class="row g-4 align-items-start">
=======
      <div class="row g-4 align-items-start mb-4">
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
        <div class="col-xl-7">
          <div class="glass-panel h-100">
            <div class="panel-heading">
              <div>
<<<<<<< HEAD
                <span class="section-eyebrow">Các ngày đang mở bán</span>
                <h2>Suất chiếu khả dụng</h2>
              </div>
            </div>

=======
                <span class="section-eyebrow">Trailer & thông tin</span>
                <h2>Giới thiệu phim</h2>
              </div>
            </div>

            @if($trailerEmbedUrl)
              <div class="ratio ratio-16x9 rounded-4 overflow-hidden mb-4">
                <iframe src="{{ $trailerEmbedUrl }}" title="Trailer {{ $movie->title }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
              </div>
            @else
              <div class="empty-panel mb-4">Phim chưa có trailer. Bạn có thể cập nhật trailer URL trong trang quản trị phim.</div>
            @endif

>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
            <div class="show-date-groups">
              @forelse($showsByDate as $date => $dateShows)
                <div class="show-date-card">
                  <div class="show-date-card__header">
                    <div>
                      <strong>{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d/m/Y') }}</strong>
                      <span>{{ $dateShows->count() }} suất chiếu</span>
                    </div>
                    <span class="date-pill">{{ \Carbon\Carbon::parse($date)->format('d.m') }}</span>
                  </div>
                  <div class="showtime-grid">
                    @foreach($dateShows as $show)
<<<<<<< HEAD
                      <div class="showtime-card {{ $show->status === 'ON_SALE' ? 'is-on-sale' : '' }}">
=======
                      <button type="button"
                              class="showtime-card {{ $show->status === 'ON_SALE' ? 'is-on-sale' : '' }} js-choose-show"
                              data-show-id="{{ $show->id }}"
                              @disabled($show->status !== 'ON_SALE')>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
                        <div>
                          <div class="showtime-card__time">{{ $show->start_time->format('H:i') }} <small>→ {{ $show->end_time->format('H:i') }}</small></div>
                          <div class="showtime-card__meta">{{ $show->auditorium->name }} · {{ $show->movieVersion->format }} · {{ $show->auditorium->cinema->name }}</div>
                        </div>
                        <span class="status-badge">{{ $show->status === 'ON_SALE' ? 'Mở bán' : 'Sắp chiếu' }}</span>
<<<<<<< HEAD
                      </div>
=======
                      </button>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
                    @endforeach
                  </div>
                </div>
              @empty
                <div class="empty-panel">Chưa có suất chiếu nào cho phim này.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="col-xl-5" id="booking-form">
          <div class="glass-panel booking-panel sticky-xl-top">
            <div class="panel-heading">
              <div>
<<<<<<< HEAD
                <span class="section-eyebrow">Đặt vé demo</span>
                <h2>Thông tin booking</h2>
              </div>
            </div>

            <form method="POST" action="{{ route('booking.store') }}" class="booking-form-grid">
              @csrf
              <div class="form-field full-width">
                <label>Suất chiếu</label>
                <select class="form-select cinema-select" name="show_id" required>
                  @forelse(($bookableShows ?? $shows) as $show)
                    <option value="{{ $show->id }}">{{ $show->start_time->format('d/m H:i') }} · {{ $show->auditorium->name }} · {{ $show->movieVersion->format }}</option>
                  @endforeach
                                  @empty
=======
                <span class="section-eyebrow">Kết nối FE với BE + DB</span>
                <h2>Form đặt vé hoàn chỉnh</h2>
              </div>
            </div>

            <form method="POST" action="{{ route('booking.store') }}" class="booking-form-grid" id="bookingForm">
              @csrf
              <div class="form-field full-width">
                <label>Suất chiếu</label>
                <select class="form-select cinema-select" name="show_id" id="showSelect" required>
                  @forelse(($bookableShows ?? $shows) as $show)
                    <option value="{{ $show->id }}" @selected((string) $defaultShowId === (string) $show->id)>
                      {{ $show->start_time->format('d/m H:i') }} · {{ $show->auditorium->name }} · {{ $show->movieVersion->format }}
                    </option>
                  @empty
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
                    <option value="">Chưa có suất đang mở bán</option>
                  @endforelse
                </select>
              </div>

<<<<<<< HEAD
              <div class="form-field">
                <label>Số vé</label>
                <input class="form-control cinema-input" type="number" min="1" max="10" name="qty" value="{{ old('qty', 2) }}" required>
              </div>
              <div class="form-field">
                <label>Điện thoại</label>
                <input class="form-control cinema-input" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="0900 000 000" required>
=======
              <div class="form-field full-width">
                <label>Quản lý ghế</label>
                <div class="seatmap-panel">
                  <div class="screen-label">MÀN HÌNH</div>
                  <div id="seatMap" class="seatmap-grid"></div>
                  <div class="seat-legend mt-3">
                    <span><i class="seat-dot available"></i>Trống</span>
                    <span><i class="seat-dot selected"></i>Đang chọn</span>
                    <span><i class="seat-dot reserved"></i>Đã đặt</span>
                    <span><i class="seat-dot blocked"></i>Bảo trì / khóa</span>
                  </div>
                  <div id="selectedSeatsText" class="booking-note mt-3 mb-0">Chưa chọn ghế nào.</div>
                  <div id="seatInputs"></div>
                </div>
              </div>

              <div class="form-field">
                <label>Loại vé</label>
                <select class="form-select cinema-select" name="ticket_type_id" required>
                  <option value="1">Người lớn</option>
                </select>
              </div>
              <div class="form-field">
                <label>Số vé dự kiến</label>
                <input class="form-control cinema-input" type="number" min="1" max="10" name="qty" value="{{ old('qty', 2) }}" required>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
              </div>
              <div class="form-field full-width">
                <label>Họ và tên</label>
                <input class="form-control cinema-input" name="contact_name" value="{{ old('contact_name') }}" placeholder="Nguyễn Văn A" required>
              </div>
<<<<<<< HEAD
              <div class="form-field full-width">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="name@example.com">
              </div>

              <button class="btn btn-cinema-primary w-100 full-width" type="submit" {{ (($bookableShows ?? collect())->isEmpty()) ? "disabled" : "" }}>
                <i class="bi bi-ticket-detailed me-2"></i>Tạo booking
              </button>
              <p class="booking-note full-width mb-0">Hệ thống sẽ tự chọn các ghế trống đầu tiên và tạo booking trạng thái <strong>PENDING</strong>. Chỉ các suất đang mở bán mới có thể đặt.</p>
=======
              <div class="form-field">
                <label>Điện thoại</label>
                <input class="form-control cinema-input" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="0900 000 000" required>
              </div>
              <div class="form-field">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="name@example.com">
              </div>
              <div class="form-field">
                <label>Mã giảm giá</label>
                <input class="form-control cinema-input" name="coupon_code" value="{{ old('coupon_code') }}" placeholder="AURORA10">
              </div>
              <div class="form-field">
                <label>Thanh toán</label>
                <select class="form-select cinema-select" name="payment_method" required>
                  @foreach($paymentMethods as $method => $label)
                    <option value="{{ $method }}" @selected(old('payment_method', 'BANK_TRANSFER') === $method)>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-field full-width">
                <label>Ghi chú thanh toán</label>
                <input class="form-control cinema-input" name="payment_note" value="{{ old('payment_note') }}" placeholder="VD: chuyển khoản demo, giữ chỗ 10 phút...">
              </div>

              <button class="btn btn-cinema-primary w-100 full-width" type="submit" {{ (($bookableShows ?? collect())->isEmpty()) ? 'disabled' : '' }}>
                <i class="bi bi-credit-card me-2"></i>Đặt vé & thanh toán
              </button>
              <p class="booking-note full-width mb-0">Ghế chọn trên frontend sẽ gửi thẳng xuống backend qua mảng <code>seat_ids[]</code>, backend kiểm tra ghế trống rồi tạo booking, ticket và payment trong database.</p>
            </form>
          </div>
        </div>
      </div>

      <div class="row g-4" id="reviews">
        <div class="col-xl-7">
          <div class="glass-panel h-100">
            <div class="panel-heading">
              <div>
                <span class="section-eyebrow">Đánh giá phim</span>
                <h2>Phản hồi từ khán giả</h2>
              </div>
              <div class="text-end">
                <strong class="d-block fs-4">{{ $reviewStats['average'] ?: '0.0' }}/5</strong>
                <span class="text-white-50">{{ $reviewStats['count'] }} đánh giá</span>
              </div>
            </div>

            <div class="review-list">
              @forelse($reviews as $review)
                <article class="review-card">
                  <div class="review-card__head">
                    <strong>{{ $review->customer_name }}</strong>
                    <span>{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}</span>
                  </div>
                  <p>{{ $review->comment ?: 'Khách hàng chưa để lại nhận xét chi tiết.' }}</p>
                  <small>{{ $review->created_at?->format('d/m/Y H:i') }}</small>
                </article>
              @empty
                <div class="empty-panel">Chưa có đánh giá nào. Hãy là người đầu tiên nhận xét bộ phim này.</div>
              @endforelse
            </div>
          </div>
        </div>

        <div class="col-xl-5">
          <div class="glass-panel h-100">
            <div class="panel-heading">
              <div>
                <span class="section-eyebrow">Gửi đánh giá</span>
                <h2>Chấm điểm sau khi xem</h2>
              </div>
            </div>

            <form method="POST" action="{{ route('movies.reviews.store', $movie) }}" class="booking-form-grid">
              @csrf
              <div class="form-field full-width">
                <label>Họ tên</label>
                <input class="form-control cinema-input" name="customer_name" value="{{ old('customer_name') }}" required>
              </div>
              <div class="form-field full-width">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="customer_email" value="{{ old('customer_email') }}">
              </div>
              <div class="form-field full-width">
                <label>Số sao</label>
                <select class="form-select cinema-select" name="rating" required>
                  @for($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected((string) old('rating', 5) === (string) $i)>{{ $i }} sao</option>
                  @endfor
                </select>
              </div>
              <div class="form-field full-width">
                <label>Nhận xét</label>
                <textarea class="form-control cinema-input" name="comment" rows="5" placeholder="Nội dung đánh giá của bạn...">{{ old('comment') }}</textarea>
              </div>
              <button class="btn btn-cinema-secondary full-width" type="submit"><i class="bi bi-send me-2"></i>Gửi đánh giá</button>
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
            </form>
          </div>
        </div>
      </div>
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    </div>
  </section>
@endsection

@push('styles')
  <style>
    .seatmap-panel{background:rgba(8,12,30,.55);border:1px solid rgba(255,255,255,.08);border-radius:22px;padding:18px}
    .screen-label{width:min(280px,100%);margin:0 auto 18px;padding:8px 18px;border-radius:999px;background:linear-gradient(135deg, rgba(244,181,98,.25), rgba(255,255,255,.08));text-align:center;font-weight:700;letter-spacing:.18em;font-size:.8rem}
    .seatmap-grid{display:grid;gap:10px}
    .seat-row{display:grid;grid-template-columns:36px 1fr;align-items:center;gap:12px}
    .seat-row__label{font-weight:700;color:rgba(255,255,255,.72)}
    .seat-row__seats{display:flex;flex-wrap:wrap;gap:8px}
    .seat-btn{min-width:42px;height:42px;border:none;border-radius:12px;font-size:.82rem;font-weight:700;color:#fff;background:rgba(99,115,255,.22);transition:.2s transform,.2s opacity,.2s background}
    .seat-btn:hover:not(:disabled){transform:translateY(-2px)}
    .seat-btn.is-selected{background:linear-gradient(135deg,#f4b562,#ff8a4d)}
    .seat-btn.is-reserved,.seat-btn.is-blocked{background:rgba(255,255,255,.12);opacity:.45;cursor:not-allowed}
    .seat-legend{display:flex;flex-wrap:wrap;gap:14px 18px;color:rgba(255,255,255,.72);font-size:.92rem}
    .seat-dot{display:inline-flex;width:14px;height:14px;border-radius:4px;margin-right:6px;vertical-align:-2px}
    .seat-dot.available{background:rgba(99,115,255,.6)} .seat-dot.selected{background:#f4b562} .seat-dot.reserved,.seat-dot.blocked{background:rgba(255,255,255,.22)}
    .review-list{display:grid;gap:16px}
    .review-card{padding:18px;border-radius:20px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06)}
    .review-card__head{display:flex;justify-content:space-between;gap:16px;margin-bottom:10px}
    .js-choose-show{width:100%;text-align:left;border:none}
  </style>
@endpush

@push('scripts')
  <script>
    const seatMaps = @json($seatMaps);
    const showSelect = document.getElementById('showSelect');
    const seatMapEl = document.getElementById('seatMap');
    const seatInputsEl = document.getElementById('seatInputs');
    const selectedSeatsTextEl = document.getElementById('selectedSeatsText');
    const selectedSeatIdsByShow = {};

    function renderSeatMap(showId) {
      const map = seatMaps[showId];
      seatMapEl.innerHTML = '';
      seatInputsEl.innerHTML = '';
      const selected = selectedSeatIdsByShow[showId] || [];

      if (!map || !map.rows || !map.rows.length) {
        seatMapEl.innerHTML = '<div class="empty-panel">Suất này chưa có dữ liệu ghế.</div>';
        selectedSeatsTextEl.textContent = 'Chưa chọn ghế nào.';
        return;
      }

      map.rows.forEach((row) => {
        const firstSeat = row[0] || {};
        const rowWrap = document.createElement('div');
        rowWrap.className = 'seat-row';
        rowWrap.innerHTML = `<div class="seat-row__label">${firstSeat.row || ''}</div><div class="seat-row__seats"></div>`;
        const seatBox = rowWrap.querySelector('.seat-row__seats');

        row.forEach((seat) => {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.className = `seat-btn is-${seat.status}`;
          btn.textContent = seat.code;
          btn.dataset.seatId = seat.id;
          btn.dataset.status = seat.status;
          if (['reserved', 'blocked'].includes(seat.status)) {
            btn.disabled = true;
          }
          if (selected.includes(seat.id)) {
            btn.classList.add('is-selected');
          }
          btn.addEventListener('click', () => toggleSeat(showId, seat.id, seat.code));
          seatBox.appendChild(btn);
        });

        seatMapEl.appendChild(rowWrap);
      });

      selected.forEach((seatId) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'seat_ids[]';
        input.value = seatId;
        seatInputsEl.appendChild(input);
      });

      selectedSeatsTextEl.textContent = selected.length
        ? `Ghế đã chọn: ${selected.join(', ')} (${selected.length} ghế)`
        : 'Chưa chọn ghế nào. Nếu không chọn, hệ thống sẽ tự xếp ghế trống.';
    }

    function toggleSeat(showId, seatId, seatCode) {
      if (!selectedSeatIdsByShow[showId]) selectedSeatIdsByShow[showId] = [];
      const arr = selectedSeatIdsByShow[showId];
      const index = arr.indexOf(seatId);
      if (index >= 0) {
        arr.splice(index, 1);
      } else {
        arr.push(seatId);
      }
      renderSeatMap(showId);
      const selectedCodes = [];
      (seatMaps[showId]?.rows || []).flat().forEach(seat => {
        if ((selectedSeatIdsByShow[showId] || []).includes(seat.id)) selectedCodes.push(seat.code);
      });
      selectedSeatsTextEl.textContent = selectedCodes.length
        ? `Ghế đã chọn: ${selectedCodes.join(', ')} (${selectedCodes.length} ghế)`
        : 'Chưa chọn ghế nào. Nếu không chọn, hệ thống sẽ tự xếp ghế trống.';
    }

    showSelect?.addEventListener('change', (e) => renderSeatMap(e.target.value));
    document.querySelectorAll('.js-choose-show').forEach((btn) => {
      btn.addEventListener('click', () => {
        if (btn.dataset.showId) {
          showSelect.value = btn.dataset.showId;
          renderSeatMap(btn.dataset.showId);
          document.getElementById('booking-form')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    if (showSelect?.value) renderSeatMap(showSelect.value);
  </script>
@endpush
