@extends('frontend.layout')

@section('title', 'Lịch chiếu ' . $movie->title)

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="showtime-hero glass-panel mb-4">
        <div class="showtime-hero__poster">
          @if($movie->poster_url)
            <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
          @else
            <div class="poster-fallback poster-fallback--showtime"><span>{{ $movie->title }}</span></div>
          @endif
        </div>
        <div class="showtime-hero__copy">
          <span class="section-eyebrow">Lịch chiếu chi tiết</span>
          <h1>{{ $movie->title }}</h1>
          <p>{{ $movie->synopsis ? \Illuminate\Support\Str::limit($movie->synopsis, 260) : 'Giao diện lịch chiếu được thiết kế lại theo dạng thẻ ngày và thẻ suất, trực quan hơn và tách biệt rõ với website tham chiếu.' }}</p>
          <div class="hero-meta hero-meta--compact">
            <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
            <span><i class="bi bi-calendar-event me-2"></i>{{ optional($movie->release_date)->format('d.m.Y') ?: 'Đang cập nhật' }}</span>
            <span><i class="bi bi-tags me-2"></i>{{ $movie->genres->pluck('name')->implode(' · ') ?: 'Chưa gán thể loại' }}</span>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-4">
            <a class="btn btn-cinema-primary" href="#booking-form"><i class="bi bi-ticket-detailed me-2"></i>Đặt vé ngay</a>
            <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-arrow-left me-2"></i>Trở lại trang chủ</a>
          </div>
        </div>
      </div>

      <div class="row g-4 align-items-start">
        <div class="col-xl-7">
          <div class="glass-panel h-100">
            <div class="panel-heading">
              <div>
                <span class="section-eyebrow">Các ngày đang mở bán</span>
                <h2>Suất chiếu khả dụng</h2>
              </div>
            </div>

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
                      <div class="showtime-card {{ $show->status === 'ON_SALE' ? 'is-on-sale' : '' }}">
                        <div>
                          <div class="showtime-card__time">{{ $show->start_time->format('H:i') }} <small>→ {{ $show->end_time->format('H:i') }}</small></div>
                          <div class="showtime-card__meta">{{ $show->auditorium->name }} · {{ $show->movieVersion->format }} · {{ $show->auditorium->cinema->name }}</div>
                        </div>
                        <span class="status-badge">{{ $show->status === 'ON_SALE' ? 'Mở bán' : 'Sắp chiếu' }}</span>
                      </div>
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
                    <option value="">Chưa có suất đang mở bán</option>
                  @endforelse
                </select>
              </div>

              <div class="form-field">
                <label>Số vé</label>
                <input class="form-control cinema-input" type="number" min="1" max="10" name="qty" value="{{ old('qty', 2) }}" required>
              </div>
              <div class="form-field">
                <label>Điện thoại</label>
                <input class="form-control cinema-input" name="contact_phone" value="{{ old('contact_phone') }}" placeholder="0900 000 000" required>
              </div>
              <div class="form-field full-width">
                <label>Họ và tên</label>
                <input class="form-control cinema-input" name="contact_name" value="{{ old('contact_name') }}" placeholder="Nguyễn Văn A" required>
              </div>
              <div class="form-field full-width">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="contact_email" value="{{ old('contact_email') }}" placeholder="name@example.com">
              </div>

              <button class="btn btn-cinema-primary w-100 full-width" type="submit" {{ (($bookableShows ?? collect())->isEmpty()) ? "disabled" : "" }}>
                <i class="bi bi-ticket-detailed me-2"></i>Tạo booking
              </button>
              <p class="booking-note full-width mb-0">Hệ thống sẽ tự chọn các ghế trống đầu tiên và tạo booking trạng thái <strong>PENDING</strong>. Chỉ các suất đang mở bán mới có thể đặt.</p>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
