@extends('frontend.layout')

@section('title', 'Trang chủ')

@section('content')
  <div class="row g-4">
    <div class="col-lg-3">
      <div class="card">
        <div class="card-header fw-semibold">Danh mục</div>
        <div class="list-group list-group-flush">
          @forelse($categories as $c)
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" href="{{ route('category.show', $c) }}">
              <span>{{ $c->name }}</span>
              <span class="badge text-bg-secondary">{{ $c->active_movies_count }}</span>
            </a>
          @empty
            <div class="list-group-item text-muted">Chưa có danh mục</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-lg-9">
      <h1 class="h4 mb-3">Phim đang chiếu (ACTIVE)</h1>

      <div class="row g-3">
        @forelse($movies as $m)
          <div class="col-md-4">
            <div class="card h-100">
              @if($m->poster_url)
                <img src="{{ $m->poster_url }}" class="card-img-top" style="height:220px;object-fit:cover;" alt="poster">
              @endif
              <div class="card-body">
                <h2 class="h6 mb-1">{{ $m->title }}</h2>
                <div class="text-muted small mb-2">{{ $m->duration_minutes }} phút · {{ optional($m->release_date)->format('Y-m-d') }}</div>
                <div class="mb-2">
                  @foreach($m->categories as $c)
                    <span class="badge text-bg-light">{{ $c->name }}</span>
                  @endforeach
                </div>
                <a class="btn btn-sm btn-dark" href="{{ route('movies.showtimes', $m) }}">Xem suất chiếu</a>
              </div>
            </div>
          </div>
        @empty
          <div class="col-12"><div class="alert alert-warning mb-0">Chưa có phim ACTIVE</div></div>
        @endforelse
      </div>
    </div>
  </div>
@endsection
