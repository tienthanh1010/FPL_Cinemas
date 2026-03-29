@extends('frontend.layout')

@section('title', 'Ưu đãi thành viên | Aurora Cinema')

@push('styles')
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />
  <style>
    .offer-card .offer-badge { background: rgba(87,209,255,0.18); color: #57d1ff; }
    .offer-accordion .card-body { background: rgba(8, 13, 25, 0.72); }
    .offer-swiper .swiper-slide { width: 280px; }
  </style>
@endpush

@section('content')
  <section class="section-space" id="offers-section">
    <div class="container-fluid app-container">
      <div class="section-head text-center mb-5">
        <span class="section-eyebrow">Ưu đãi thành viên</span>
        <h2>Không chỉ là giá vé - mà là trải nghiệm</h2>
        <p>Khởi tạo hành trình khách hàng thân thiết với ưu đãi thành viên thiết thực, quà tặng sinh nhật và combo ngon bổ rẻ.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-6 col-lg-3">
          <div class="card offer-card h-100">
            <div class="card-body">
              <div class="offer-badge">Thành viên</div>
              <h5 class="card-title">Điểm tích lũy</h5>
              <p class="card-text">Tích điểm sau mỗi giao dịch để đổi vé, đồ ăn và ưu đãi đặc quyền. Hạng càng cao, ưu đãi càng lớn.</p>
              <a href="#" class="btn btn-cinema-primary btn-sm">Xem chi tiết</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3">
          <div class="card offer-card h-100">
            <div class="card-body">
              <div class="offer-badge">Sinh nhật</div>
              <h5 class="card-title">Quà sinh nhật VIP</h5>
              <p class="card-text">Thưởng 1 voucher bắp + nước hoặc 1 vé miễn phí khi đăng ký sinh nhật thành viên.</p>
              <a href="#" class="btn btn-cinema-primary btn-sm">Nhận ưu đãi</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3">
          <div class="card offer-card h-100">
            <div class="card-body">
              <div class="offer-badge">Combo</div>
              <h5 class="card-title">Combo vàng</h5>
              <p class="card-text">Giảm đến 20% cho combo bắp kèm nước, áp dụng cho tất cả các suất chiếu.</p>
              <a href="#" class="btn btn-cinema-primary btn-sm">Mua ngay</a>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-lg-3">
          <div class="card offer-card h-100">
            <div class="card-body">
              <div class="offer-badge">Rạp ưu đãi</div>
              <h5 class="card-title">Rạp đặt biệt</h5>
              <p class="card-text">Giảm giá 15% tại rạp theo tuần (CGV, BHD, Lotte, Galaxy...) khi là thành viên Gold/Platinum.</p>
              <a href="#" class="btn btn-cinema-primary btn-sm">Tìm rạp</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="section-space">
    <div class="container-fluid app-container">
      <div class="section-head text-center mb-5">
        <span class="section-eyebrow">Ưu đãi thành viên</span>
        <h2>Ưu đãi mới nhất dành cho bạn</h2>
        <p>Các chương trình khuyến mãi đang chạy tốt nhất hiện tại. Chọn bộ lọc để cập nhật tức thì.</p>
      </div>

      <div class="row mb-4 g-3 align-items-center">
        <div class="col-sm-6 col-md-4 col-lg-3">
          <select id="offer-kind-filter" class="form-select form-select-sm">
            <option value="all">Tất cả loại ưu đãi</option>
            <option value="coupon">Thẻ</option>
            <option value="combo">Combo</option>
            <option value="price">Giảm giá/Quà tặng</option>
          </select>
        </div>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <select id="offer-type-filter" class="form-select form-select-sm">
            <option value="all">Tất cả áp dụng</option>
            @foreach($promotionTypes as $code => $label)
              <option value="{{ $code }}">{{ $label }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-4 col-lg-3">
          <div class="input-group input-group-sm">
            <input id="voucher-code-input" type="text" class="form-control" placeholder="Nhập mã voucher" aria-label="Voucher code">
            <button id="voucher-check-button" class="btn btn-cinema-primary">Kiểm tra</button>
          </div>
          <div id="voucher-message" class="form-text text-white-50 mt-1"></div>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-lg-8">
          <div class="accordion offer-accordion" id="offerAccordion">
            @foreach($groupedPromotions as $scope => $group)
              <div class="accordion-item mb-2">
                <h2 class="accordion-header" id="heading-{{ Str::slug($scope) }}">
                  <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ Str::slug($scope) }}" aria-expanded="false" aria-controls="collapse-{{ Str::slug($scope) }}">
                    {{ $scope }} ({{ $group->count() }})
                  </button>
                </h2>
                <div id="collapse-{{ Str::slug($scope) }}" class="accordion-collapse collapse" aria-labelledby="heading-{{ Str::slug($scope) }}" data-bs-parent="#offerAccordion">
                  <div class="accordion-body p-0">
                    <div class="d-flex offer-swiper-container overflow-auto gap-3 p-3" id="offerPlaceholder-{{ Str::slug($scope) }}">
                      @foreach($group as $promo)
                        <article class="offer-panel card" data-kind="{{ $promo->applies_to == 'ORDER' ? 'price' : (strtolower($promo->applies_to) === 'ticket' ? 'ticket' : 'product') }}" data-type="{{ $promo->applies_to }}" data-scope="{{ $promo->customer_scope ?: 'Tất cả' }}">
                          <div class="card-body">
                            <div class="offer-badge mb-2">{{ $promo->code }}</div>
                            <h5 class="card-title">{{ $promo->name }}</h5>
                            <p class="card-text">{{ $promo->description ?: 'Ưu đãi hấp dẫn dành cho khách hàng Aurora.' }}</p>
                            <div class="small text-muted mb-2">Giảm {{ $promo->promo_type == 'PERCENT' ? $promo->discount_value . '%' : number_format($promo->discount_value, 0, ',', '.') . 'đ' }} · {{ $promotionTypes[$promo->applies_to] ?? $promo->applies_to }}</div>
                            <div class="d-flex gap-1 flex-wrap mb-2">
                              @if($promo->cinemas->isNotEmpty())
                                <span class="badge bg-info text-dark">{{ $promo->cinemas->count() }} rạp</span>
                              @endif
                              @if($promo->movies->isNotEmpty())
                                <span class="badge bg-warning text-dark">{{ $promo->movies->count() }} phim</span>
                              @endif
                            </div>
                            <a href="#" class="btn btn-cinema-primary btn-sm">Xem chi tiết</a>
                          </div>
                        </article>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>
            @endforeach
          </div>
        </div>

        <div class="col-lg-4">
          <div class="card h-100 border-0" style="background: rgba(5, 11, 23, 0.62);">
            <div class="card-body">
              <h5>Chia sẻ link bộ lọc</h5>
              <p class="small text-white-75">Sao chép URL với bộ lọc đang chọn để chia sẻ ưu đãi ngay.</p>
              <button id="copy-link-btn" class="btn btn-outline-light btn-sm">Sao chép liên kết</button>
              <div id="copy-result" class="form-text text-success mt-2" style="display:none;">Đã sao chép!</div>
            </div>
          </div>
        </div>
      </div>

      <div class="swiper offer-swiper mt-4">
        <div class="swiper-wrapper" id="offer-swiper-wrapper">
          @foreach($promotions as $promo)
            <div class="swiper-slide">
              <div class="card h-100 offer-panel" data-kind="{{ $promo->applies_to == 'ORDER' ? 'price' : (strtolower($promo->applies_to) === 'ticket' ? 'ticket' : 'product') }}" data-type="{{ $promo->applies_to }}" data-scope="{{ $promo->customer_scope ?: 'Tất cả' }}">
                <div class="card-body">
                  <div class="offer-badge mb-2">{{ $promo->code }}</div>
                  <h5 class="card-title">{{ $promo->name }}</h5>
                  <p class="card-text">{{ $promo->description ?: 'Ưu đãi hấp dẫn dành cho khách hàng Aurora.' }}</p>
                  <div class="small text-muted mb-2">Giảm {{ $promo->promo_type == 'PERCENT' ? $promo->discount_value . '%' : number_format($promo->discount_value, 0, ',', '.') . 'đ' }} · {{ $promotionTypes[$promo->applies_to] ?? $promo->applies_to }}</div>
                  <div class="d-flex gap-1 flex-wrap mb-2">
                    @if($promo->cinemas->isNotEmpty())
                      <span class="badge bg-info text-dark">{{ $promo->cinemas->count() }} rạp</span>
                    @endif
                    @if($promo->movies->isNotEmpty())
                      <span class="badge bg-warning text-dark">{{ $promo->movies->count() }} phim</span>
                    @endif
                  </div>
                  <a href="#" class="btn btn-cinema-primary btn-sm">Xem chi tiết</a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="swiper-pagination"></div>
      </div>

    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
  <script>
    const getSearchParams = () => new URLSearchParams(window.location.search);
    const setSearchParams = (params) => window.history.replaceState({}, '', `${window.location.pathname}?${params.toString()}`);

    const offerKindFilter = document.getElementById('offer-kind-filter');
    const offerTypeFilter = document.getElementById('offer-type-filter');
    const voucherInput = document.getElementById('voucher-code-input');
    const voucherMessage = document.getElementById('voucher-message');

    const updateFiltersFromQuery = () => {
      const params = getSearchParams();
      if (params.has('kind')) offerKindFilter.value = params.get('kind');
      if (params.has('type')) offerTypeFilter.value = params.get('type');
      if (params.has('code')) voucherInput.value = params.get('code');
    };

    const syncQuery = () => {
      const params = getSearchParams();
      const kind = offerKindFilter.value;
      const type = offerTypeFilter.value;
      if (kind !== 'all') params.set('kind', kind); else params.delete('kind');
      if (type !== 'all') params.set('type', type); else params.delete('type');
      if (voucherInput.value.trim()) params.set('code', voucherInput.value.trim()); else params.delete('code');
      setSearchParams(params);
    };

    const applyFilters = () => {
      const kind = offerKindFilter.value;
      const type = offerTypeFilter.value;

      const panels = document.querySelectorAll('[data-kind][data-type]');
      panels.forEach(panel => {
        const panelKind = panel.dataset.kind;
        const panelType = panel.dataset.type;

        const okKind = (kind === 'all') || (panelKind === kind);
        const okType = (type === 'all') || (panelType === type);

        panel.style.display = (okKind && okType) ? '' : 'none';
      });

      updateAccordionCounts();
    };

    const updateAccordionCounts = () => {
      document.querySelectorAll('.offer-accordion .accordion-item').forEach(item => {
        const button = item.querySelector('.accordion-button');
        const collapseId = button.getAttribute('data-bs-target');
        const container = document.querySelector(collapseId + ' .offer-swiper-container');
        const visible = container.querySelectorAll('article[style*="display: none"]:not([data-kind])');
        // no-op
      });
    };

    const initSwiper = () => {
      new Swiper('.offer-swiper', {
        slidesPerView: 'auto',
        spaceBetween: 16,
        loop: true,
        autoplay: { delay: 3800, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true},
      });
    };

    const fetchPromotions = async () => {
      const params = getSearchParams();
      const kind = params.get('kind') || 'all';
      const type = params.get('type') || 'all';
      const code = params.get('code') || '';
      const scope = params.get('scope') || 'all';

      const query = new URLSearchParams();
      if (kind !== 'all') query.set('kind', kind);
      if (type !== 'all') query.set('type', type);
      if (code) query.set('code', code);
      if (scope !== 'all') query.set('scope', scope);

      const response = await fetch(`/api/promotions?${query.toString()}`);
      if (! response.ok) return;
      const data = await response.json();
      renderPromotions(data.data);
    };

    const renderPromotions = (promotions) => {
      const wrapper = document.getElementById('offer-swiper-wrapper');
      if (! wrapper) return;
      wrapper.innerHTML = promotions.map(promo => {
        const amount = promo.promo_type === 'PERCENT' ? `${promo.discount_value}%` : new Intl.NumberFormat('vi-VN').format(promo.discount_value) + 'đ';
        const scopeLabel = promo.customer_scope || 'Tất cả';

        return `
          <div class="swiper-slide">
            <div class="card h-100 offer-panel" data-kind="${promo.kind}" data-type="${promo.applies_to}" data-scope="${scopeLabel}">
              <div class="card-body">
                <div class="offer-badge mb-2">${promo.code}</div>
                <h5 class="card-title">${promo.name}</h5>
                <p class="card-text">${promo.description || 'Ưu đãi hấp dẫn dành cho khách hàng Aurora.'}</p>
                <div class="small text-muted mb-2">Giảm ${amount} · ${promotionTypes[promo.applies_to] || promo.applies_to}</div>
                <div class="d-flex gap-1 flex-wrap mb-2">
                  ${promo.cinemas.length ? `<span class="badge bg-info text-dark">${promo.cinemas.length} rạp</span>` : ''}
                  ${promo.movies.length ? `<span class="badge bg-warning text-dark">${promo.movies.length} phim</span>` : ''}
                </div>
                <a href="#" class="btn btn-cinema-primary btn-sm">Xem chi tiết</a>
              </div>
            </div>
          </div>
        `;
      }).join('');

      initSwiper();
    };

    offerKindFilter.addEventListener('change', () => { syncQuery(); fetchPromotions(); });
    offerTypeFilter.addEventListener('change', () => { syncQuery(); fetchPromotions(); });

    document.getElementById('voucher-check-button').addEventListener('click', async () => {
      const code = voucherInput.value.trim();
      if (! code) { voucherMessage.textContent = 'Vui lòng nhập mã voucher.'; return; }

      const res = await fetch(`/api/promotions?code=${encodeURIComponent(code)}`);
      const json = await res.json();
      if (json.data && json.data.length > 0) {
        const match = json.data[0];
        voucherMessage.textContent = `Mã hợp lệ: ${match.name} - giảm ${match.promo_type === 'PERCENT' ? match.discount_value + '%' : new Intl.NumberFormat('vi-VN').format(match.discount_value) + 'đ'}`;
        voucherMessage.className = 'form-text text-success';
      } else {
        voucherMessage.textContent = 'Mã không hợp lệ hoặc đã hết hạn.';
        voucherMessage.className = 'form-text text-danger';
      }

      syncQuery();
    });

    document.getElementById('copy-link-btn').addEventListener('click', () => {
      navigator.clipboard.writeText(window.location.href).then(() => {
        const copyResult = document.getElementById('copy-result');
        copyResult.style.display = 'block';
        setTimeout(() => copyResult.style.display = 'none', 2000);
      });
    });

    updateFiltersFromQuery();
    applyFilters();
    initSwiper();
    fetchPromotions();
  </script>
@endpush