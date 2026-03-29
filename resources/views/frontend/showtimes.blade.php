@extends('frontend.layout')

@section('title', 'Lịch chiếu ' . $movie->title)

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <!-- Hero Section -->
      <div class="showtime-hero glass-panel mb-5">
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
        </div>
      </div>

      <!-- Booking Panel -->
      <div class="booking-header glass-panel" id="booking-form">
        <div class="panel-heading">
          <span class="section-eyebrow">Chọn suất chiếu</span>
          <h2>Đặt vé {{ $movie->title }}</h2>
        </div>

        <!-- Date Calendar -->
        <div class="booking-section booking-dates">
          <div class="section-label">Chọn ngày</div>
          <div class="date-calendar">
            @php
              $startDate = now();
              $dates = collect(range(0, 13))->map(fn ($i) => $startDate->clone()->addDays($i));
            @endphp
            @foreach($dates as $date)
              <label class="date-item">
                <input type="radio" name="show_date" value="{{ $date->format('Y-m-d') }}" {{ $loop->first ? 'checked' : '' }}>
                <div class="date-box">
                  <span class="date-month">{{ $date->format('m') }}</span>
                  <span class="date-day">{{ $date->format('d') }}</span>
                  <span class="date-weekday">{{ $date->translatedFormat('D') }}</span>
                </div>
              </label>
            @endforeach
          </div>
        </div>

        <!-- Cinema & Location Filter -->
        <div class="booking-section booking-filters">
          <div class="filter-trio">
            <div class="filter-box">
              <div class="section-label">Chọn rạp</div>
              @php
                $cinemas = $shows->pluck('auditorium.cinema')->unique('id')->values();
              @endphp
              <select id="cinema-select" class="form-select">
                <option value="">Tất cả rạp</option>
                @foreach($cinemas as $cinema)
                  <option value="{{ $cinema->id }}">{{ $cinema->name }}</option>
                @endforeach
              </select>
            </div>

            <div class="filter-box">
              <div class="section-label">Định dạng</div>
              @php
                $formats = $shows->flatMap(fn ($s) => collect([$s->movieVersion->format]))->unique()->values();
              @endphp
              <select id="format-select" class="form-select">
                <option value="">Tất cả định dạng</option>
                @foreach($formats as $format)
                  <option value="{{ $format }}">{{ $format }}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Showtimes Grid -->
      <div class="showtimes-container mt-5">
        <div class="panel-heading mb-4">
          <span class="section-eyebrow">Lịch chiếu</span>
          <h2>Chọn suất phim bạn muốn</h2>
        </div>

        <div class="showtimes-grid">
          @php
            $showsBycinema = $shows->groupBy(fn ($s) => $s->auditorium->cinema->name);
          @endphp
          
          @forelse($showsBycinema as $cinema => $cinemaShows)
            @php $cinemaId = $cinemaShows->first()->auditorium->cinema->id; @endphp
            <div class="cinema-block" data-cinema-id="{{ $cinemaId }}">
              <div class="cinema-header">
                <h3>{{ $cinema }}</h3>
              </div>
              
              @php
                $showsByAuditorium = $cinemaShows->groupBy(fn ($s) => $s->auditorium->name);
              @endphp
              
              @foreach($showsByAuditorium as $auditorium => $auditoriumShows)
                <div class="auditorium-block">
                  <div class="auditorium-header">
                    <span class="auditorium-name">{{ $auditorium }}</span>
                  </div>
                  <div class="showtime-list">
                    @foreach($auditoriumShows->sortBy('start_time') as $show)
                      <a href="{{ route('booking.store') }}" class="showtime-item {{ $show->status === 'ON_SALE' ? 'is-bookable' : '' }}" data-show="{{ $show->id }}">
                        <span class="showtime-time">{{ $show->start_time->format('H:i') }}</span>
                        <span class="showtime-format">{{ $show->movieVersion->format }}</span>
                      </a>
                    @endforeach
                  </div>
                </div>
              @endforeach
            </div>
          @empty
            <div class="empty-panel">Chưa có suất chiếu nào cho phim này.</div>
          @endforelse
        </div>
      </div>

      <!-- Booking Form (Hidden Modal) -->
      <form method="POST" action="{{ route('booking.store') }}" id="booking-form-modal" class="d-none">
        @csrf
        <input type="hidden" name="show_id" id="selected_show_id">
        <input type="hidden" name="qty" value="1">
      </form>

      <div class="mt-5">
        <a href="{{ route('home') }}" class="btn btn-cinema-secondary">
          <i class="bi bi-arrow-left me-2"></i>Trở lại trang chủ
        </a>
      </div>
    </div>
  </section>

  @push('scripts')
    <script>
      // Handle showtime item clicks
      document.querySelectorAll('.showtime-item').forEach(item => {
        item.addEventListener('click', function(e) {
          e.preventDefault();
          const showId = this.dataset.show;
          document.getElementById('selected_show_id').value = showId;
          document.getElementById('booking-form-modal').submit();
        });
      });

      // Handle format and cinema select filters
      document.getElementById('format-select')?.addEventListener('change', filterShowtimes);
      document.getElementById('cinema-select')?.addEventListener('change', filterShowtimes);

      function filterShowtimes() {
        const selectedCinema = document.getElementById('cinema-select')?.value;
        const selectedFormat = document.getElementById('format-select')?.value;

        document.querySelectorAll('.cinema-block').forEach(block => {
          let hasVisibleShowtimes = false;
          const cinemaId = block.dataset.cinemaId;

          if (!selectedCinema || selectedCinema === cinemaId) {
            block.querySelectorAll('.showtime-item').forEach(item => {
              const format = item.querySelector('.showtime-format')?.textContent.trim();
              if (!selectedFormat || format === selectedFormat) {
                item.style.display = '';
                hasVisibleShowtimes = true;
              } else {
                item.style.display = 'none';
              }
            });
            block.style.display = hasVisibleShowtimes ? '' : 'none';
          } else {
            block.style.display = 'none';
          }
        });
      }
    </script>
  @endpush
@endsection
