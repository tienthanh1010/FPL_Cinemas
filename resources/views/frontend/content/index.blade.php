@extends('frontend.layout')

@section('title', $heading . ' | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-hero mb-4">
        <span class="section-eyebrow">{{ $heading }}</span>
        <h1>{{ $heading }}</h1>
        <p class="mb-0">Danh mục này được đồng bộ trực tiếp từ phần quản trị để cả khách hàng và admin cùng nhìn một nguồn nội dung thống nhất.</p>
      </div>

      <div class="content-grid">
        @forelse($posts as $post)
          <article class="content-card h-100">
            <a href="{{ $type === 'NEWS' ? route('news.show', $post) : route('offers.show', $post) }}" class="content-card__cover">
              @if($post->cover_image_url)
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
              @else
                <div class="content-card__fallback"><i class="bi {{ $type === 'NEWS' ? 'bi-newspaper' : 'bi-gift' }}"></i></div>
              @endif
            </a>
            <div class="content-card__body">
              <div class="content-card__meta">
                <span class="content-tag">{{ $post->badge_label ?: $heading }}</span>
                <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y') }}</span>
              </div>
              <h3><a href="{{ $type === 'NEWS' ? route('news.show', $post) : route('offers.show', $post) }}">{{ $post->title }}</a></h3>
              <p>{{ \Illuminate\Support\Str::limit($post->excerpt ?: strip_tags($post->content), 140) }}</p>
              <a href="{{ $type === 'NEWS' ? route('news.show', $post) : route('offers.show', $post) }}" class="content-card__link">Xem chi tiết <i class="bi bi-arrow-right-short"></i></a>
            </div>
          </article>
        @empty
          <div class="glass-panel empty-panel w-100">Hiện chưa có nội dung nào được xuất bản.</div>
        @endforelse
      </div>

      <div class="mt-4">{{ $posts->links() }}</div>
    </div>
  </section>
@endsection
