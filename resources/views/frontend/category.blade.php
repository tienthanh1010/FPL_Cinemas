@extends('frontend.layout')

@section('title', 'Danh mục: ' . $category->name)

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Danh mục: {{ $category->name }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('home') }}">← Trang chủ</a>
  </div>

  <div class="row g-3">
    @forelse($movies as $m)
      <div class="col-md-3">
        <div class="card h-100">
          @if($m->poster_url)
            <img src="{{ $m->poster_url }}" class="card-img-top" style="height:180px;object-fit:cover;" alt="poster">
          @endif
          <div class="card-body">
            <div class="fw-semibold">{{ $m->title }}</div>
            <div class="text-muted small mb-2">{{ $m->duration_minutes }} phút</div>
            <a class="btn btn-sm btn-dark" href="{{ route('movies.showtimes', $m) }}">Suất chiếu</a>
          </div>
        </div>
      </div>
    @empty
      <div class="col-12"><div class="alert alert-warning mb-0">Không có phim trong danh mục này.</div></div>
    @endforelse
  </div>

  <div class="mt-3">{{ $movies->links() }}</div>
@endsection
