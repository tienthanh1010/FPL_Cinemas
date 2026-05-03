@extends('frontend.layout')

@section('title', 'Thể loại ' . $category->name)


@section('content')
  <section class="section-space pt-4 pt-lg-5">
    <div class="container-fluid app-container">
      <div class="inner-hero glass-panel mb-4">
        <div>
          <span class="section-eyebrow">Bộ lọc theo thể loại</span>
          <h1>{{ $category->name }}</h1>

       </div>
        <div class="inner-hero__meta">
          <div>
            <strong>{{ $movies->total() }}</strong>
            <span>tựa phim</span>
          </div>
          <a class="btn btn-cinema-secondary" href="{{ route('home') }}"><i class="bi bi-arrow-left me-2"></i>Về trang chủ</a>

        </div>
      </div>

      <div class="row g-4">
        @forelse($movies as $movie)
          <div class="col-md-6 col-xl-4">
          <div class="col-md-6 col-xl-4">

            @include('frontend.partials.movie-card', ['movie' => $movie, 'badge' => 'Genre'])
          </div>
        @empty
          <div class="col-12"><div class="glass-panel empty-panel">Không có phim trong thể loại này.</div></div>
        @endforelse
      </div>

      <div class="app-pagination mt-4">
        {{ $movies->links() }}
      </div>
    </div>
  </section>

@endsection
