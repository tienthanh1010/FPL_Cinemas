@extends('frontend.layout')

@section('title', 'Đặt vé | ' . $movie->title)

@push('styles')
<style>
  .booking-page-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.5fr) minmax(320px, .78fr);
    gap: 1.5rem;
    align-items: start;
  }
  .booking-page-grid .sticky-summary {
    position: sticky;
    top: 100px;
  }
  .booking-intro {
    display: grid;
    grid-template-columns: minmax(220px, 280px) minmax(0, 1fr);
    gap: 1.4rem;
    align-items: stretch;
  }
  .booking-intro__poster {
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,255,255,.08);
    min-height: 380px;
  }
  .booking-intro__poster img { width: 100%; height: 100%; object-fit: cover; display: block; }
  .booking-intro__copy { display: flex; flex-direction: column; justify-content: center; gap: 1rem; }
  .booking-intro__copy h1 { margin: 0; }
  .booking-intro__copy p { margin: 0; color: var(--muted); }

  .booking-metric-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: .9rem;
    margin-top: 1rem;
  }
  .booking-metric {
    padding: 1rem;
    border-radius: 20px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
  }
  .booking-metric span { display: block; font-size: .76rem; color: rgba(255,255,255,.58); margin-bottom: .35rem; }
  .booking-metric strong { display: block; color: #fff; font-size: 1rem; }

  .seatmap-shell {
    background:
      radial-gradient(circle at top, rgba(59, 130, 246, .18), transparent 42%),
      linear-gradient(180deg, #0f172a 0%, #111827 55%, #0b1220 100%);
    border-radius: 28px;
    border: 1px solid rgba(148, 163, 184, .18);
    padding: 28px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .25);
    color: #e5eefc;
  }
  .screen-arc { max-width: 840px; margin: 0 auto 28px; text-align: center; }
  .screen-arc::before {
    content: '';
    display: block;
    height: 32px;
    border-radius: 999px 999px 24px 24px;
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(191,219,254,.82));
    box-shadow: 0 10px 35px rgba(147, 197, 253, .35);
  }
  .screen-arc span {
    display: inline-block;
    margin-top: 10px;
    padding: 4px 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, .06);
    color: #cbd5e1;
    font-size: .74rem;
    letter-spacing: .28em;
    text-transform: uppercase;
  }
  .legend-wrap { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px; }
  .legend-chip {
    display: inline-flex; align-items: center; gap: 8px; padding: 9px 14px; border-radius: 999px;
    font-size: .84rem; font-weight: 700; color: #0f172a; background: #fff;
  }
  .legend-chip::before {
    content: ''; width: 10px; height: 10px; border-radius: 999px; background: currentColor;
    box-shadow: 0 0 0 4px rgba(255,255,255,.12);
  }
  .chip-empty { background: #dcfce7; color: #15803d; }
  .chip-selected { background: #ffedd5; color: #c2410c; }
  .chip-booked { background: #fee2e2; color: #b91c1c; }
  .chip-vip { background: #fef3c7; color: #b45309; }
  .chip-couple { background: #f5d0fe; color: #a21caf; }

  .seat-section {
    margin-top: 18px; padding: 18px; border-radius: 22px; background: rgba(255,255,255,.05);
    border: 1px solid rgba(148,163,184,.14); backdrop-filter: blur(6px);
  }
  .seat-section + .seat-section { margin-top: 16px; }
  .seat-section-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
  .seat-section-title { font-size: 1.02rem; font-weight: 800; margin: 0; color: #f8fafc; }
  .seat-section-subtitle { color: #93c5fd; font-size: .84rem; margin: 4px 0 0; text-transform: uppercase; letter-spacing: .08em; }
  .seat-row { display: grid; grid-template-columns: 44px 1fr; align-items: center; gap: 14px; }
  .seat-row + .seat-row { margin-top: 12px; }
  .seat-row-label {
    width: 44px; height: 44px; display: grid; place-items: center; border-radius: 14px;
    background: rgba(255,255,255,.09); border: 1px solid rgba(148,163,184,.18); color: #f8fafc; font-weight: 800;
  }
  .seat-row-banks { display: flex; align-items: center; justify-content: center; gap: 28px; flex-wrap: wrap; }
  .seat-bank { display: flex; gap: 10px; flex-wrap: wrap; justify-content: center; }
  .seat-tile {
    min-width: 82px; min-height: 72px; border-radius: 18px 18px 14px 14px; border: 1px solid rgba(255,255,255,.12);
    padding: 8px 10px; text-align: center; display: flex; align-items: center; justify-content: center; background: #fff;
    box-shadow: inset 0 -8px 0 rgba(15, 23, 42, .08), 0 8px 18px rgba(15, 23, 42, .16);
    transition: transform .18s ease, box-shadow .18s ease, filter .18s ease; overflow: hidden;
  }
  .seat-tile:hover:not(.seat-disabled) { transform: translateY(-2px); box-shadow: inset 0 -8px 0 rgba(15, 23, 42, .08), 0 14px 22px rgba(15, 23, 42, .24); }
  .seat-tile button { border: 0; background: transparent; width: 100%; padding: 0; color: inherit; }
  .seat-tile.couple { min-width: 124px; }
  .seat-tile.vip { box-shadow: inset 0 -8px 0 rgba(120, 53, 15, .10), 0 10px 20px rgba(245, 158, 11, .18); }
  .seat-tile .seat-code { display: block; font-weight: 800; font-size: .98rem; color: #0f172a; line-height: 1.1; }
  .seat-tile .seat-meta { display: block; margin-top: 4px; font-size: .72rem; color: #475569; line-height: 1.2; }
  .seat-empty { background: linear-gradient(180deg, #f0fdf4 0%, #dcfce7 100%); }
  .seat-vip { background: linear-gradient(180deg, #fffbeb 0%, #fde68a 100%); }
  .seat-couple { background: linear-gradient(180deg, #fdf4ff 0%, #f5d0fe 100%); }
  .seat-selected { background: linear-gradient(180deg, #ffedd5 0%, #fdba74 100%); }
  .seat-disabled { background: linear-gradient(180deg, #fef2f2 0%, #fee2e2 100%); opacity: .65; filter: grayscale(.1); cursor: not-allowed; }

  .booking-side-card,
  .booking-extra-card {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.08);
    border-radius: 24px;
    padding: 1.15rem;
  }
  .booking-side-card + .booking-side-card,
  .booking-extra-card + .booking-extra-card { margin-top: 1rem; }
  .selected-seat-list { display: flex; flex-wrap: wrap; gap: .55rem; margin-top: .8rem; }
  .selected-seat-pill { padding: .55rem .85rem; border-radius: 999px; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1); color: #fff; font-size: .86rem; font-weight: 600; }
  .product-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; }
  .product-card { position: relative; border-radius: 22px; border: 1px solid rgba(255,255,255,.08); background: rgba(255,255,255,.04); padding: 1rem; display: flex; flex-direction: column; gap: .85rem; min-height: 200px; }
  .product-card.is-disabled { opacity: .45; }
  .product-card__badges { display: flex; flex-wrap: wrap; gap: .45rem; }
  .product-badge { display: inline-flex; align-items: center; gap: .35rem; padding: .34rem .68rem; border-radius: 999px; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; background: rgba(255,255,255,.08); color: rgba(255,255,255,.78); }
  .product-badge--combo { background: rgba(255,122,24,.14); color: #ffcfab; }
  .product-card__title { color: #fff; font-weight: 700; font-size: 1rem; line-height: 1.35; }
  .product-card__description { color: rgba(255,255,255,.6); font-size: .9rem; line-height: 1.55; min-height: 44px; }
  .product-card__footer { display: flex; justify-content: space-between; align-items: flex-end; gap: .75rem; margin-top: auto; }
  .product-price { color: #fff; font-size: 1.05rem; font-weight: 800; }
  .product-stock { color: rgba(255,255,255,.52); font-size: .82rem; margin-top: .2rem; }
  .product-qty-control { display: inline-flex; align-items: center; gap: .45rem; border-radius: 999px; padding: .35rem; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); }
  .product-qty-button { width: 34px; height: 34px; border-radius: 50%; border: 0; background: rgba(255,255,255,.1); color: #fff; font-weight: 700; display: inline-flex; align-items: center; justify-content: center; }
  .product-qty-button:disabled { opacity: .4; cursor: not-allowed; }
  .product-qty-input { width: 52px; border: 0; background: transparent; color: #fff; text-align: center; font-weight: 700; outline: none; }
  .summary-breakdown { display: grid; gap: .5rem; margin-top: .85rem; }
  .summary-breakdown__row { display: flex; justify-content: space-between; gap: 1rem; color: rgba(255,255,255,.72); font-size: .92rem; }
  .summary-breakdown__row strong { color: #fff; }
  .booking-total-value { color: #fff; font-size: 1.8rem; font-weight: 800; line-height: 1.1; }
  .booking-note { color: rgba(255,255,255,.56); font-size: .86rem; }
  .form-field label { display: block; margin-bottom: .45rem; font-weight: 600; color: rgba(255,255,255,.82); }

  @media (max-width: 1199.98px) {
    .booking-page-grid { grid-template-columns: 1fr; }
    .booking-page-grid .sticky-summary { position: static; }
  }
  @media (max-width: 991.98px) {
    .booking-intro { grid-template-columns: 1fr; }
    .booking-intro__poster { max-width: 280px; }
    .booking-metric-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 768px) {
    .seatmap-shell { padding: 18px; }
    .seat-row { grid-template-columns: 1fr; }
    .seat-row-label { margin: 0 auto; }
    .seat-row-banks { gap: 12px; }
    .seat-bank { gap: 8px; }
    .seat-tile, .seat-tile.couple { min-width: 72px; }
  }


  .booking-intro__poster,
  .booking-metric,
  .booking-side-card,
  .booking-extra-card,
  .product-card,
  .product-qty-control,
  .selected-seat-pill,
  .booking-alert {
    border-color: var(--line) !important;
  }
  .booking-metric,
  .booking-side-card,
  .booking-extra-card,
  .product-card,
  .product-qty-control,
  .selected-seat-pill,
  .booking-alert {
    background: var(--panel-light) !important;
  }
  .booking-metric span,
  .product-card__description,
  .product-stock,
  .booking-note,
  .summary-breakdown__row,
  .form-field label,
  .booking-intro__copy p {
    color: var(--muted) !important;
  }
  .booking-metric strong,
  .product-card__title,
  .product-price,
  .product-qty-input,
  .summary-breakdown__row strong,
  .booking-total-value,
  .selected-seat-pill,
  .product-badge,
  .product-qty-button,
  .seat-section-title {
    color: var(--text) !important;
  }
  .product-qty-button {
    background: var(--surface-2) !important;
  }
  .product-badge { background: var(--surface-2) !important; }
  .product-badge--combo {
    background: color-mix(in srgb, var(--primary) 14%, var(--surface-2)) !important;
    color: var(--text) !important;
  }
  .seat-section,
  .seat-row-label,
  .screen-arc span {
    background: var(--surface-2) !important;
    border-color: var(--line) !important;
    color: var(--text) !important;
  }
  .seat-section {
    background: color-mix(in srgb, var(--panel-light) 88%, transparent) !important;
  }
  .seat-section-subtitle { color: var(--secondary) !important; }
  html[data-theme='dark'] .seatmap-shell {
    background:
      radial-gradient(circle at top, rgba(59, 130, 246, .18), transparent 42%),
      linear-gradient(180deg, #0f172a 0%, #111827 55%, #0b1220 100%) !important;
    border-color: rgba(148, 163, 184, .18) !important;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .25) !important;
    color: #e5eefc !important;
  }
  html[data-theme='light'] .seatmap-shell {
    background:
      radial-gradient(circle at top, rgba(37, 99, 235, .10), transparent 42%),
      linear-gradient(180deg, #ffffff 0%, #f8fbff 55%, #eef4fc 100%) !important;
    border-color: rgba(15,23,42,.1) !important;
    box-shadow: 0 20px 46px rgba(15, 23, 42, .10) !important;
    color: #1e293b !important;
  }
  html[data-theme='light'] .screen-arc::before {
    background: linear-gradient(180deg, rgba(255,255,255,.98), rgba(191,219,254,.95)) !important;
    box-shadow: 0 10px 35px rgba(59, 130, 246, .18) !important;
  }
  html[data-theme='light'] .legend-chip { box-shadow: none; }
  html[data-theme='light'] .seat-row-label,
  html[data-theme='light'] .screen-arc span { color: #334155 !important; }

</style>
@endpush

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel mb-4">
        <div class="booking-intro">
          <div class="booking-intro__poster">
            @if($movie->poster_url)
              <img src="{{ $movie->poster_url }}" alt="{{ $movie->title }}">
            @else
              <div class="poster-fallback poster-fallback--showtime"><span>{{ $movie->title }}</span></div>
            @endif
          </div>
          <div class="booking-intro__copy">
            <span class="section-eyebrow">Trang đặt vé riêng</span>
            <h1>{{ $movie->title }}</h1>
            <p>Suất chiếu bạn chọn: <strong class="text-white">{{ $show->start_time->translatedFormat('l, d/m/Y H:i') }}</strong>. Hãy chọn ghế trực tiếp trên sơ đồ lớn, thêm combo nếu cần và tạo booking để chuyển sang bước thanh toán.</p>
            <div class="hero-meta hero-meta--compact">
              <span><i class="bi bi-clock me-2"></i>{{ $movie->duration_minutes }} phút</span>
              <span><i class="bi bi-camera-reels me-2"></i>{{ $show->movieVersion?->format ?: '2D' }}</span>
              <span><i class="bi bi-door-open me-2"></i>{{ $show->auditorium?->name ?: 'Phòng chiếu' }}</span>
            </div>
            <div class="booking-metric-grid">
              <div class="booking-metric"><span>Rạp chiếu</span><strong>{{ $show->auditorium?->cinema?->name ?: 'FPL Cinema' }}</strong></div>
              <div class="booking-metric"><span>Khung giờ</span><strong>{{ $show->start_time->format('H:i') }} → {{ optional($show->end_time)->format('H:i') }}</strong></div>
              <div class="booking-metric"><span>Trạng thái</span><strong>{{ $show->frontendStatusLabel() }}</strong></div>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <a href="{{ route('movies.showtimes', $movie) }}" class="btn btn-cinema-secondary"><i class="bi bi-arrow-left me-2"></i>Quay lại lịch chiếu</a>
              @if($relatedShows->isNotEmpty())
                <div class="booking-note align-self-center">Có {{ $relatedShows->count() }} suất khác của phim này đang hiển thị trong trang lịch chiếu.</div>
              @endif
            </div>
          </div>
        </div>
      </div>

      <form method="POST" action="{{ route('booking.store') }}" id="bookingForm">
        @csrf
        <input type="hidden" name="show_id" value="{{ $show->id }}">
        <input type="hidden" name="qty" id="qtyInput" value="{{ old('qty', 0) }}">
        <div id="seatInputs"></div>

        <div class="booking-page-grid">
          <div>
            @if($errors->any())
              <div class="app-alert app-alert--error mb-4">
                <div class="fw-semibold mb-2">Không thể tạo booking, vui lòng kiểm tra lại:</div>
                <ul class="mb-0 ps-3">
                  @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="seatmap-shell mb-4">
              <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                  <h2 class="h4 mb-2 text-white">Sơ đồ ghế trực quan</h2>
                  <p class="mb-0 text-light-emphasis">Bố cục ghế được mở rộng giống phần quản lý suất chiếu để dễ nhìn, dễ chọn và thao tác chính xác hơn.</p>
                </div>
                <div class="selected-seat-pill" id="selectedSeatCount">0 ghế</div>
              </div>

              <div class="legend-wrap">
                <span class="legend-chip chip-empty">Ghế thường</span>
                <span class="legend-chip chip-vip">Ghế VIP</span>
                <span class="legend-chip chip-couple">Ghế đôi</span>
                <span class="legend-chip chip-selected">Đang chọn</span>
                <span class="legend-chip chip-booked">Đã bán / đang giữ</span>
              </div>

              <div class="screen-arc"><span>Màn hình</span></div>
              <div id="seatMap"></div>
              <div id="selectedSeatList" class="selected-seat-list"></div>
            </div>

            <div class="booking-extra-card mb-4">
              <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div>
                  <h3 class="h5 mb-1 text-white">Combo &amp; đồ ăn kèm</h3>
                  <p class="booking-note mb-0">Giá và tồn kho được tính theo rạp của suất chiếu đang chọn.</p>
                </div>
                <div class="selected-seat-pill" id="selectedProductCount">0 món</div>
              </div>
              <div id="productCatalog" class="product-grid"></div>
            </div>
          </div>

          <div class="sticky-summary">
            <div class="booking-side-card">
              <h3 class="h5 text-white mb-3">Tóm tắt đơn hàng</h3>
              <div class="summary-breakdown" id="summaryBreakdown"></div>
              <div class="border-top border-secondary-subtle mt-3 pt-3">
                <div class="booking-note">Tạm tính toàn bộ đơn hàng</div>
                <div class="booking-total-value" id="bookingTotalValue">0đ</div>
                <div class="booking-note mt-2" id="loyaltyPreview"></div>
              </div>
            </div>

            <div class="booking-side-card member-points-card">
              @auth
                <div class="content-tag mb-2">Thành viên</div>
                <h3 class="h5 text-white mb-2">{{ auth()->user()->name }}</h3>
                <p class="booking-note mb-2">Điểm hiện có: <strong class="text-white">{{ number_format((int) ($authCustomer?->loyaltyAccount?->points_balance ?? 0)) }} điểm</strong></p>
                <p class="booking-note mb-0">Hoàn tất thanh toán để hệ thống cộng điểm tự động cho booking này.</p>
              @else
                <div class="content-tag mb-2">Khách vãng lai</div>
                <h3 class="h5 text-white mb-2">Đăng nhập để tích điểm</h3>
                <p class="booking-note mb-3">Khi đăng nhập, thông tin đặt vé sẽ được lưu vào tài khoản và bạn sẽ nhận điểm sau mỗi đơn thành công.</p>
                <a href="{{ route('login') }}" class="btn btn-cinema-secondary w-100">Đăng nhập / tạo tài khoản</a>
              @endauth
            </div>

            <div class="booking-side-card">
              <div class="form-field mb-3">
                <label>Loại vé</label>
                <select class="form-select cinema-select" name="ticket_type_id" id="ticketTypeSelect" required>
                  @foreach($ticketTypes as $ticketType)
                    <option value="{{ $ticketType->id }}" @selected((string) old('ticket_type_id', $ticketTypes->first()?->id) === (string) $ticketType->id)>
                      {{ $ticketType->name }}
                    </option>
                  @endforeach
                </select>
              </div>
              <div class="form-field mb-3">
                <label>Mã giảm giá / voucher</label>
                <input class="form-control cinema-input text-uppercase" name="coupon_code" id="couponInput" value="{{ old('coupon_code') }}" placeholder="Ví dụ: CINEMA20">
              </div>
              <div class="form-field mb-3">
                <label>Điện thoại</label>
                <input class="form-control cinema-input" name="contact_phone" value="{{ old('contact_phone', $authCustomer?->phone) }}" placeholder="0900 000 000" required>
              </div>
              <div class="form-field mb-3">
                <label>Email</label>
                <input class="form-control cinema-input" type="email" name="contact_email" value="{{ old('contact_email', $authCustomer?->email ?: auth()->user()?->email) }}" placeholder="name@example.com">
              </div>
              <div class="form-field mb-0">
                <label>Họ và tên</label>
                <input class="form-control cinema-input" name="contact_name" value="{{ old('contact_name', $authCustomer?->full_name ?: auth()->user()?->name) }}" placeholder="Nguyễn Văn A" required>
              </div>
            </div>

            <button class="btn btn-cinema-primary w-100 mt-3" type="submit" id="bookingSubmitButton">
              <i class="bi bi-ticket-detailed me-2"></i>Tạo booking và sang bước thanh toán
            </button>
            <p class="booking-note mt-3 mb-0">Nếu ghế vừa bị người khác giữ trong lúc bạn thao tác, hệ thống sẽ báo lỗi và yêu cầu chọn lại để đảm bảo dữ liệu chính xác. Điểm thưởng chỉ được cộng sau khi booking thanh toán thành công.</p>
          </div>
        </div>
      </form>
    </div>
  </section>
@endsection

@push('scripts')
<script>
(() => {
  const bookingConfig = @json($bookingConfig);
  const oldState = {
    ticketTypeId: @json((string) old('ticket_type_id', $ticketTypes->first()?->id)),
    seatIds: @json(collect(old('seat_ids', []))->map(fn ($value) => (int) $value)->values()),
    productQty: @json(collect(old('product_qty', []))->mapWithKeys(fn ($value, $key) => [(string) $key => (int) $value])),
  };

  const ticketTypeSelect = document.getElementById('ticketTypeSelect');
  const seatMap = document.getElementById('seatMap');
  const seatInputs = document.getElementById('seatInputs');
  const qtyInput = document.getElementById('qtyInput');
  const selectedSeatList = document.getElementById('selectedSeatList');
  const selectedSeatCount = document.getElementById('selectedSeatCount');
  const productCatalog = document.getElementById('productCatalog');
  const selectedProductCount = document.getElementById('selectedProductCount');
  const summaryBreakdown = document.getElementById('summaryBreakdown');
  const bookingTotalValue = document.getElementById('bookingTotalValue');
  const loyaltyPreview = document.getElementById('loyaltyPreview');
  const bookingSubmitButton = document.getElementById('bookingSubmitButton');
  const amountPerPoint = Number(@json((int) config('loyalty.amount_per_point', 10000)));
  const isMember = @json(auth()->check());

  if (!ticketTypeSelect || !seatMap) return;

  let selectedSeatIds = Array.isArray(oldState.seatIds) ? oldState.seatIds.map(Number) : [];
  let selectedProductQty = Object.fromEntries(Object.entries(oldState.productQty || {}).map(([productId, qty]) => [String(productId), Number(qty || 0)]));
  const formatCurrency = (value) => `${Number(value || 0).toLocaleString('vi-VN')}đ`;

  const sectionMeta = {
    REGULAR: { key: 'regular', title: 'Khu ghế thường', subtitle: 'Vị trí phổ thông dễ chọn' },
    VIP: { key: 'vip', title: 'Khu ghế VIP', subtitle: 'Trải nghiệm trung tâm màn hình' },
    COUPLE: { key: 'couple', title: 'Khu ghế đôi', subtitle: 'Không gian dành cho 2 người' },
  };

  const sanitizeSelectedProducts = () => {
    const allowedProducts = Object.fromEntries((bookingConfig.products || []).map((product) => [String(product.id), product]));
    selectedProductQty = Object.fromEntries(
      Object.entries(selectedProductQty)
        .map(([productId, qty]) => {
          const product = allowedProducts[String(productId)];
          if (!product || !product.available) return null;
          const normalizedQty = Math.max(0, Math.min(Number(qty || 0), Math.min(20, Number(product.qty_on_hand || 0))));
          return [String(productId), normalizedQty];
        })
        .filter(Boolean)
    );
  };

  const getSeatPrice = (seat) => Number((bookingConfig.prices?.[seat.seat_type_id]?.[String(ticketTypeSelect.value || '')]) ?? 120000);

  const buildSeatTile = (seat, selected) => {
    const seatClass = !seat.available
      ? 'seat-disabled'
      : selected
        ? 'seat-selected'
        : seat.seat_type_code === 'VIP'
          ? 'seat-vip'
          : seat.seat_type_code === 'COUPLE'
            ? 'seat-couple'
            : 'seat-empty';

    const extraClass = [
      'seat-tile',
      seatClass,
      seat.seat_type_code === 'VIP' ? 'vip' : '',
      seat.seat_type_code === 'COUPLE' ? 'couple' : '',
    ].filter(Boolean).join(' ');

    return `
      <div class="${extraClass}" title="${seat.seat_code} · ${seat.seat_type_name}">
        <button type="button" ${seat.available ? '' : 'disabled'} data-seat-id="${seat.id}" data-seat-code="${seat.seat_code}">
          <span class="seat-code">${seat.seat_code}</span>
          <span class="seat-meta">${seat.seat_type_name}</span>
        </button>
      </div>
    `;
  };

  const renderSeatMap = () => {
    const seats = Array.isArray(bookingConfig.seats) ? bookingConfig.seats : [];
    if (!seats.length) {
      seatMap.innerHTML = '<div class="empty-panel">Chưa có dữ liệu ghế cho suất chiếu này.</div>';
      return;
    }

    const sections = { regular: [], vip: [], couple: [] };
    const rowBuckets = {};
    seats.forEach((seat) => {
      const section = sectionMeta[seat.seat_type_code]?.key || 'regular';
      rowBuckets[section] = rowBuckets[section] || {};
      rowBuckets[section][seat.row_label] = rowBuckets[section][seat.row_label] || [];
      rowBuckets[section][seat.row_label].push(seat);
    });

    Object.entries(rowBuckets).forEach(([sectionKey, rows]) => {
      const mapped = Object.entries(rows)
        .sort(([a], [b]) => a.localeCompare(b, undefined, { numeric: true }))
        .map(([rowLabel, rowSeats]) => ({ rowLabel, seats: rowSeats.sort((a, b) => a.col_number - b.col_number) }));
      sections[sectionKey] = mapped;
    });

    seatMap.innerHTML = Object.entries(sections)
      .filter(([, rows]) => rows.length)
      .map(([sectionKey, rows]) => {
        const meta = Object.values(sectionMeta).find((item) => item.key === sectionKey) || sectionMeta.REGULAR;
        const rowHtml = rows.map(({ rowLabel, seats }) => {
          const half = Math.ceil(seats.length / 2);
          const leftBank = seats.slice(0, half);
          const rightBank = seats.slice(half);
          const renderBank = (bank) => `<div class="seat-bank">${bank.map((seat) => buildSeatTile(seat, selectedSeatIds.includes(Number(seat.id)))).join('')}</div>`;
          return `
            <div class="seat-row">
              <div class="seat-row-label">${rowLabel}</div>
              <div class="seat-row-banks">
                ${renderBank(leftBank)}
                ${rightBank.length ? renderBank(rightBank) : ''}
              </div>
            </div>
          `;
        }).join('');

        return `
          <div class="seat-section section-${sectionKey}">
            <div class="seat-section-head">
              <div>
                <h3 class="seat-section-title">${meta.title}</h3>
                <p class="seat-section-subtitle">${meta.subtitle}</p>
              </div>
              <span class="selected-seat-pill">${rows.length} dãy</span>
            </div>
            ${rowHtml}
          </div>
        `;
      }).join('');
    attachSeatEvents();
  };

  const buildProductCard = (product, qty) => {
    const safeQty = Math.max(0, Number(qty || 0));
    const maxQty = Math.min(20, Number(product.qty_on_hand || 0));

    return `
      <div class="product-card ${product.available ? '' : 'is-disabled'}">
        <div class="product-card__badges">
          <span class="product-badge ${product.is_combo ? 'product-badge--combo' : ''}">${product.is_combo ? 'Combo' : product.category}</span>
          <span class="product-badge">${product.unit || 'ITEM'}</span>
        </div>
        <div>
          <div class="product-card__title">${product.name}</div>
          <div class="product-card__description">${product.description || 'Món ăn kèm được phục vụ tại quầy F&B của rạp.'}</div>
        </div>
        <div class="product-card__footer">
          <div>
            <div class="product-price">${formatCurrency(product.price_amount)}</div>
            <div class="product-stock">${product.available ? `Còn ${product.qty_on_hand} ${product.unit || 'món'}` : 'Tạm hết hàng'}</div>
          </div>
          <div class="product-qty-control">
            <button type="button" class="product-qty-button" data-product-action="decrease" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>−</button>
            <input class="product-qty-input" type="number" min="0" max="${maxQty}" step="1" name="product_qty[${product.id}]" value="${safeQty}" data-product-input data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>
            <button type="button" class="product-qty-button" data-product-action="increase" data-product-id="${product.id}" ${product.available ? '' : 'disabled'}>+</button>
          </div>
        </div>
      </div>
    `;
  };

  const renderProducts = () => {
    sanitizeSelectedProducts();
    const products = Array.isArray(bookingConfig.products) ? bookingConfig.products : [];
    if (!products.length) {
      productCatalog.innerHTML = '<div class="empty-panel">Hiện chưa có sản phẩm F&B hoạt động cho rạp này.</div>';
      selectedProductCount.textContent = '0 món';
      return;
    }

    productCatalog.innerHTML = products.map((product) => buildProductCard(product, selectedProductQty[String(product.id)] || 0)).join('');
    const totalProducts = Object.values(selectedProductQty).reduce((sum, qty) => sum + Number(qty || 0), 0);
    selectedProductCount.textContent = `${totalProducts} món`;
    attachProductEvents();
  };

  const renderSummary = () => {
    const seats = (bookingConfig.seats || []).filter((seat) => selectedSeatIds.includes(Number(seat.id)));
    const ticketSubtotal = seats.reduce((sum, seat) => sum + getSeatPrice(seat), 0);
    const products = bookingConfig.products || [];
    const productSubtotal = products.reduce((sum, product) => sum + (Number(selectedProductQty[String(product.id)] || 0) * Number(product.price_amount || 0)), 0);
    const total = ticketSubtotal + productSubtotal;

    const seatLabels = seats.map((seat) => `${seat.seat_code} · ${formatCurrency(getSeatPrice(seat))}`);
    selectedSeatList.innerHTML = seatLabels.length
      ? seatLabels.map((label) => `<span class="selected-seat-pill">${label}</span>`).join('')
      : '<span class="booking-note">Chưa chọn ghế nào.</span>';

    selectedSeatCount.textContent = `${selectedSeatIds.length} ghế`;
    summaryBreakdown.innerHTML = `
      <div class="summary-breakdown__row"><span>Suất chiếu</span><strong>${bookingConfig.show_date}</strong></div>
      <div class="summary-breakdown__row"><span>Khung giờ</span><strong>${bookingConfig.start_time} → ${bookingConfig.end_time}</strong></div>
      <div class="summary-breakdown__row"><span>Ghế đã chọn</span><strong>${selectedSeatIds.length ? selectedSeatIds.length + ' ghế' : 'Chưa chọn'}</strong></div>
      <div class="summary-breakdown__row"><span>Tiền vé</span><strong>${formatCurrency(ticketSubtotal)}</strong></div>
      <div class="summary-breakdown__row"><span>Combo / F&B</span><strong>${formatCurrency(productSubtotal)}</strong></div>
    `;
    bookingTotalValue.textContent = formatCurrency(total);

    if (loyaltyPreview) {
      const estimatedPoints = amountPerPoint > 0 ? Math.floor(total / amountPerPoint) : 0;
      if (isMember) {
        loyaltyPreview.innerHTML = estimatedPoints > 0
          ? `Dự kiến cộng <strong>${estimatedPoints} điểm</strong> sau khi thanh toán thành công.`
          : 'Đơn hàng hiện chưa đủ điều kiện cộng điểm.';
      } else {
        loyaltyPreview.innerHTML = estimatedPoints > 0
          ? `Đăng nhập thành viên để lưu đơn và tích khoảng <strong>${estimatedPoints} điểm</strong> cho booking này.`
          : 'Đăng nhập thành viên để lưu lịch sử booking và tích điểm ở các đơn tiếp theo.';
      }
    }

    qtyInput.value = String(selectedSeatIds.length);
    seatInputs.innerHTML = selectedSeatIds.map((seatId) => `<input type="hidden" name="seat_ids[]" value="${seatId}">`).join('');
    bookingSubmitButton.disabled = selectedSeatIds.length === 0;
  };

  const attachSeatEvents = () => {
    seatMap.querySelectorAll('[data-seat-id]').forEach((button) => {
      button.addEventListener('click', () => {
        const seatId = Number(button.dataset.seatId);
        if (!seatId) return;
        if (selectedSeatIds.includes(seatId)) {
          selectedSeatIds = selectedSeatIds.filter((id) => id !== seatId);
        } else {
          if (selectedSeatIds.length >= 10) {
            window.alert('Bạn chỉ có thể chọn tối đa 10 ghế cho một booking.');
            return;
          }
          selectedSeatIds.push(seatId);
        }
        renderSeatMap();
        renderSummary();
      });
    });
  };

  const attachProductEvents = () => {
    productCatalog.querySelectorAll('[data-product-action]').forEach((button) => {
      button.addEventListener('click', () => {
        const productId = String(button.dataset.productId || '');
        const product = (bookingConfig.products || []).find((item) => String(item.id) === productId);
        if (!product) return;
        const currentQty = Number(selectedProductQty[productId] || 0);
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        selectedProductQty[productId] = button.dataset.productAction === 'increase'
          ? Math.min(maxQty, currentQty + 1)
          : Math.max(0, currentQty - 1);
        renderProducts();
        renderSummary();
      });
    });

    productCatalog.querySelectorAll('[data-product-input]').forEach((input) => {
      input.addEventListener('input', () => {
        const productId = String(input.dataset.productId || '');
        const product = (bookingConfig.products || []).find((item) => String(item.id) === productId);
        if (!product) return;
        const maxQty = Math.min(20, Number(product.qty_on_hand || 0));
        selectedProductQty[productId] = Math.max(0, Math.min(maxQty, Number(input.value || 0)));
        renderProducts();
        renderSummary();
      });
    });
  };

  ticketTypeSelect.addEventListener('change', renderSummary);
  renderSeatMap();
  renderProducts();
  renderSummary();
})();
</script>
@endpush
