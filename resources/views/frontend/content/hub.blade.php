@extends('frontend.layout')

@section('title', 'Tin tức & ưu đãi | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4">
        <span class="section-eyebrow">Tin tức & ưu đãi</span>
        <h1>Cập nhật từ FPL Cinema</h1>
        {{-- <p class="mb-0">Ưu đãi hiển thị phía trên, tin tức và thông báo rạp hiển thị phía dưới để người dùng dễ theo dõi hơn.</p> --}}
      </div>

      <section id="offers" class="content-hub-section">
        <div class="section-heading mb-3">
          <div>
            <span class="section-eyebrow">Ưu đãi đang áp dụng</span>
            <h2 class="h3 mb-0">Ưu đãi nổi bật</h2>
          </div>
          <a href="{{ route('offers.index') }}" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="content-grid content-grid--hub">
          @forelse($offers as $post)
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
                <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 130) }}</p>
                <a href="{{ route('offers.show', $post) }}" class="content-card__link">Xem chi tiết <i class="bi bi-arrow-right-short"></i></a>
              </div>
            </article>
          @empty
            <div class="glass-panel empty-panel w-100">Hiện chưa có ưu đãi nào được xuất bản.</div>
          @endforelse
        </div>
      </section>

      <section id="news" class="content-hub-section mt-5">
        <div class="section-heading mb-3">
          <div>
            <span class="section-eyebrow">Tin tức mới nhất</span>
            <h2 class="h3 mb-0">Tin tức điện ảnh & thông báo từ rạp</h2>
          </div>
          <a href="{{ route('news.index') }}" class="section-link">Xem tất cả <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="content-grid content-grid--hub">
          @forelse($news as $post)
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
                <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 130) }}</p>
                <a href="{{ route('news.show', $post) }}" class="content-card__link">Đọc bài viết <i class="bi bi-arrow-right-short"></i></a>
              </div>
            </article>
          @empty
            <div class="glass-panel empty-panel w-100">Hiện chưa có bài viết nào được xuất bản.</div>
          @endforelse
        </div>
      </section>
    </div>
  </section>
@endsection
