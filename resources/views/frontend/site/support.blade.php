@extends('frontend.layout')

@section('title', 'Hỗ trợ khách hàng | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4">
        <span class="section-eyebrow">Hỗ trợ khách hàng</span>
        <h1>FAQ, chính sách và hướng dẫn sử dụng</h1>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-lg-6">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Những thành phần bắt buộc</h2>
            <div class="related-post-list">
              @foreach($mustHaveItems as $item)
                <div class="related-post-item d-flex gap-3 align-items-start">
                  <i class="bi bi-check2-circle fs-5 text-success"></i>
                  <div><strong>{{ $item }}</strong></div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Những thành phần nên có</h2>
            <div class="related-post-list">
              @foreach($shouldHaveItems as $item)
                <div class="related-post-item d-flex gap-3 align-items-start">
                  <i class="bi bi-stars fs-5 text-warning"></i>
                  <div><strong>{{ $item }}</strong></div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      <div class="glass-panel mb-4">
        <h2 class="h4 mb-3">Câu hỏi thường gặp</h2>
        <div class="accordion accordion-flush" id="supportFaqAccordion">
          @foreach($faqs as $index => $faq)
            <div class="accordion-item bg-transparent border border-light-subtle rounded-4 mb-3 overflow-hidden">
              <h3 class="accordion-header">
                <button class="accordion-button {{ $index === 0 ? '' : 'collapsed' }} bg-transparent shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $index }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}" aria-controls="faq{{ $index }}">
                  {{ $faq['question'] }}
                </button>
              </h3>
              <div id="faq{{ $index }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#supportFaqAccordion">
                <div class="accordion-body text-muted">{{ $faq['answer'] }}</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-4">
          <a class="quick-action-card h-100" href="{{ route('booking.lookup') }}">
            <div class="quick-action-card__icon"><i class="bi bi-search"></i></div>
            <div>
              <strong>Tra cứu booking</strong>
              <span>Xem lại đơn vé, ghế ngồi, thanh toán và trạng thái booking.</span>
            </div>
          </a>
        </div>
        <div class="col-md-4">
          <a class="quick-action-card h-100" href="{{ route('cinema.info') }}">
            <div class="quick-action-card__icon"><i class="bi bi-buildings"></i></div>
            <div>
              <strong>Thông tin rạp</strong>
              <span>Xem địa chỉ, hotline, giờ mở cửa và phòng chiếu của FPL Cinema.</span>
            </div>
          </a>
        </div>
        <div class="col-md-4">
          <a class="quick-action-card h-100" href="{{ route('member.account') }}">
            <div class="quick-action-card__icon"><i class="bi bi-person-circle"></i></div>
            <div>
              <strong>Tài khoản thành viên</strong>
              <span>Theo dõi lịch sử đặt vé, chi tiêu và điểm thưởng tích lũy.</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>
@endsection
