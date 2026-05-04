@extends('frontend.layout')

@section('title', $post->title . ' | ' . ($appBrand ?? config('app.name', 'FPL Cinemas')))

@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="glass-panel content-detail-card mb-4">
        <div class="content-card__meta mb-3">
          <span class="content-tag">{{ $post->badge_label ?: $heading }}</span>
          <span>{{ optional($post->published_at ?: $post->created_at)->format('d/m/Y H:i') }}</span>
        </div>
        <h1 class="mb-3">{{ $post->title }}</h1>
        <p class="content-lead mb-0">{{ $post->excerpt ?: 'Nội dung được cập nhật từ hệ thống quản trị của rạp.' }}</p>
      </div>

      <div class="row g-4">
        <div class="col-lg-8">
          <article class="glass-panel content-detail-card h-100">
            @if($post->cover_image_url)
              <div class="content-detail-cover mb-4">
                <img src="{{ $post->cover_image_url }}" alt="{{ $post->title }}">
              </div>
            @endif
            <div class="post-body">
              @php
                $rawContent = trim((string) ($post->content ?: $post->excerpt ?: 'Nội dung đang được cập nhật.'));
                $paragraphBuffer = [];
                $flushParagraph = function () use (&$paragraphBuffer) {
                    if ($paragraphBuffer === []) {
                        return '';
                    }
                    $html = '<p>' . nl2br(e(trim(implode("\n", $paragraphBuffer)))) . '</p>';
                    $paragraphBuffer = [];
                    return $html;
                };
                $bodyHtml = '';
                foreach (preg_split('/\r?\n/', $rawContent) as $line) {
                    $trimmed = trim($line);
                    if (preg_match('/^!\[[^\]]*\]\((https?:\/\/[^)]+)\)$/i', $trimmed, $matches)) {
                        $bodyHtml .= $flushParagraph();
                        $bodyHtml .= '<figure class="content-detail-cover my-4"><img src="' . e($matches[1]) . '" alt="' . e($post->title) . '"></figure>';
                    } elseif ($trimmed === '') {
                        $bodyHtml .= $flushParagraph();
                    } else {
                        $paragraphBuffer[] = $line;
                    }
                }
                $bodyHtml .= $flushParagraph();
              @endphp
              {!! $bodyHtml !!}
            </div>
          </article>
        </div>
        <div class="col-lg-4">
          <div class="glass-panel h-100">
            <h2 class="h4 mb-3">Bài viết liên quan</h2>
            <div class="related-post-list">
              @forelse($relatedPosts as $relatedPost)
                <a class="related-post-item" href="{{ $type === 'NEWS' ? route('news.show', $relatedPost) : route('offers.show', $relatedPost) }}">
                  <strong>{{ $relatedPost->title }}</strong>
                  <span>{{ optional($relatedPost->published_at ?: $relatedPost->created_at)->format('d/m/Y') }}</span>
                </a>
              @empty
                <div class="text-muted">Chưa có bài viết liên quan.</div>
              @endforelse
            </div>
            <div class="mt-4">
              <a href="{{ $type === 'NEWS' ? route('news.index') : route('offers.index') }}" class="btn btn-cinema-secondary w-100">Quay lại danh sách {{ mb_strtolower($heading) }}</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
