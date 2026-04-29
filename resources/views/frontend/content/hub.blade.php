@extends('frontend.layout')

@section('title', 'Tin tức & ưu đãi | ' . ($appBrand ?? config('app.name', 'FPL Cinema')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4 mb-xl-5">
        <span class="section-eyebrow">Tin tức & ưu đãi</span>
        <h1>Cập nhật thông báo rạp và ưu đãi thành viên</h1>
        <p class="mb-0">Ưu đãi được đặt ở phía trên, tin tức ở phía dưới để khách hàng theo dõi nhanh hơn mà không cần quay lại trang chủ.</p>
      </div>

      <section class="content-hub-section">
        <div class="section-heading mb-3">
          <div>
            <span class="section-eyebrow">Khuyến mãi mới</span>
            <h2 class="h3 mb-0">Ưu đãi nổi bật</h2>
          </div>
          <a href="{{ route('offers.index') }}" class="section-link">Xem thêm <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="content-feature-grid">
          @php($leadOffer = $featuredOffers->first())
          @if($leadOffer)
            <article class="content-feature-card">
              <a href="{{ route('offers.show', $leadOffer) }}" class="content-feature-card__cover">
                @if($leadOffer->cover_image_url)
                  <img src="{{ $leadOffer->cover_image_url }}" alt="{{ $leadOffer->title }}">
                @else
                  <div class="content-card__fallback"><i class="bi bi-gift"></i></div>
                @endif
              </a>
              <div class="content-feature-card__body">
                <span class="content-tag">{{ $leadOffer->badge_label ?: 'Ưu đãi' }}</span>
                <h3><a href="{{ route('offers.show', $leadOffer) }}">{{ $leadOffer->title }}</a></h3>
                <p>{{ \Illuminate\Support\Str::limit($leadOffer->excerpt ?: strip_tags($leadOffer->content), 170) }}</p>
                <a href="{{ route('offers.show', $leadOffer) }}" class="content-card__link">Xem chi tiết <i class="bi bi-arrow-right-short"></i></a>
              </div>
            </article>
          @endif

          <div class="content-mini-grid">
            @forelse($featuredOffers->skip(1) as $post)
              <article class="content-card h-100">
                <a href="{{ route('offers.show', $post) }}" class="content-card__cover">
                  @if($post->cover_image_url)
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
                  @else
                    <div class="content-card__fallback"><i class="bi bi-gift"></i></div>
                  @endif
                </a>
                <div class="content-card__body">
                  <div class="content-card__meta">
                    <span class="content-tag">{{ $post->badge_label ?: 'Ưu đãi' }}</span>
                    <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y') }}</span>
                  </div>
                  <h3><a href="{{ route('offers.show', $post) }}">{{ $post->title }}</a></h3>
                  <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 95) }}</p>
                  <a href="{{ route('offers.show', $post) }}" class="content-card__link">Xem chi tiết <i class="bi bi-arrow-right-short"></i></a>
                </div>
              </article>
            @empty
              <div class="glass-panel empty-panel">Hiện chưa có ưu đãi nào được xuất bản.</div>
            @endforelse
          </div>
        </div>
      </section>

      <section class="content-hub-section">
        <div class="section-heading mb-3">
          <div>
            <span class="section-eyebrow">Tin bên lề</span>
            <h2 class="h3 mb-0">Tin tức điện ảnh & thông báo rạp</h2>
          </div>
          <a href="{{ route('news.index') }}" class="section-link">Xem thêm <i class="bi bi-arrow-right"></i></a>
        </div>

        <div class="content-feature-grid">
          @php($leadNews = $featuredNews->first())
          @if($leadNews)
            <article class="content-feature-card">
              <a href="{{ route('news.show', $leadNews) }}" class="content-feature-card__cover">
                @if($leadNews->cover_image_url)
                  <img src="{{ $leadNews->cover_image_url }}" alt="{{ $leadNews->title }}">
                @else
                  <div class="content-card__fallback"><i class="bi bi-newspaper"></i></div>
                @endif
              </a>
              <div class="content-feature-card__body">
                <span class="content-tag">{{ $leadNews->badge_label ?: 'Tin tức' }}</span>
                <h3><a href="{{ route('news.show', $leadNews) }}">{{ $leadNews->title }}</a></h3>
                <p>{{ \Illuminate\Support\Str::limit($leadNews->excerpt ?: strip_tags($leadNews->content), 170) }}</p>
                <a href="{{ route('news.show', $leadNews) }}" class="content-card__link">Đọc bài viết <i class="bi bi-arrow-right-short"></i></a>
              </div>
            </article>
          @endif

          <div class="content-mini-grid">
            @forelse($featuredNews->skip(1) as $post)
              <article class="content-card h-100">
                <a href="{{ route('news.show', $post) }}" class="content-card__cover">
                  @if($post->cover_image_url)
                    <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
                  @else
                    <div class="content-card__fallback"><i class="bi bi-newspaper"></i></div>
                  @endif
                </a>
                <div class="content-card__body">
                  <div class="content-card__meta">
                    <span class="content-tag">{{ $post->badge_label ?: 'Tin tức' }}</span>
                    <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y') }}</span>
                  </div>
                  <h3><a href="{{ route('news.show', $post) }}">{{ $post->title }}</a></h3>
                  <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 95) }}</p>
                  <a href="{{ route('news.show', $post) }}" class="content-card__link">Đọc bài viết <i class="bi bi-arrow-right-short"></i></a>
                </div>
              </article>
            @empty
              <div class="glass-panel empty-panel">Hiện chưa có tin tức nào được xuất bản.</div>
            @endforelse
          </div>
        </div>
      </section>
    </div>
  </section>
@endsection
