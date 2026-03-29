@extends('frontend.layout')

@section('title', 'Phim Đang Chiếu | Aurora Cinema')

@section('content')
  <section class="now-showing-hero">
    <div class="container-fluid app-container">
      <div class="now-showing-hero__content">
        <span class="eyebrow"><i class="bi bi-film me-2"></i>Danh sách đầy đủ</span>
        <h1>Phim Đang Chiếu</h1>
        <p>Khám phá tất cả những bộ phim đang phát hành tại Aurora Cinema. Chọn lọc theo thể loại, định dạng và rạp chiếu yêu thích.</p>
      </div>
    </div>
  </section>

  <section class="section-space">
    <div class="container-fluid app-container">
      <!-- Filters Panel -->
      <div class="now-showing-controls mb-4">
        <form method="GET" action="{{ route('movies.now_showing') }}" class="now-showing-filters">
          <div class="filters-row">
            <div class="filter-group">
              <label for="filterGenre" class="filter-label"><i class="bi bi-tag"></i> Thể loại</label>
              <select id="filterGenre" name="genre" class="filter-select" onchange="this.form.submit()">
                <option value="">Tất cả thể loại</option>
                @foreach($categories as $category)
                  <option value="{{ $category->id }}" @selected($filterGenre == $category->id)>
                    {{ $category->name }}
                  </option>
                @endforeach
              </select>
            </div>
            
            @if($formats && count($formats) > 0)
              <div class="filter-group">
                <label for="filterFormat" class="filter-label"><i class="bi bi-projector"></i> Định dạng</label>
                <select id="filterFormat" name="format" class="filter-select" onchange="this.form.submit()">
                  <option value="">Tất cả định dạng</option>
                  @foreach($formats as $format)
                    <option value="{{ $format['value'] }}" @selected($filterFormat == $format['value'])>
                      {{ $format['label'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif
            
            @if($cinemas && count($cinemas) > 0)
              <div class="filter-group">
                <label for="filterCinema" class="filter-label"><i class="bi bi-building"></i> Rạp chiếu</label>
                <select id="filterCinema" name="cinema" class="filter-select" onchange="this.form.submit()">
                  <option value="">Tất cả rạp</option>
                  @foreach($cinemas as $cinema)
                    <option value="{{ $cinema['id'] }}" @selected($filterCinema == $cinema['id'])>
                      {{ $cinema['name'] }}
                    </option>
                  @endforeach
                </select>
              </div>
            @endif
            
            <div class="filter-group">
              <label for="sortBy" class="filter-label"><i class="bi bi-arrow-down-up"></i> Sắp xếp</label>
              <select id="sortBy" name="sort" class="filter-select" onchange="this.form.submit()">
                <option value="release_date" @selected($sortBy == 'release_date')>Mới nhất phát hành</option>
                <option value="title" @selected($sortBy == 'title')>Tên phim (A-Z)</option>
                <option value="popular" @selected($sortBy == 'popular')>Suất chiếu nhiều nhất</option>
              </select>
            </div>
            
            @if($filterGenre || $filterFormat || $filterCinema)
              <div class="filter-group">
                <a href="{{ route('movies.now_showing') }}" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-x"></i> Xoá bộ lọc
                </a>
              </div>
            @endif
          </div>
        </form>
      </div>

      <!-- Movies Grid -->
      <div class="row g-4">
        @forelse($nowShowing as $movie)
          <div class="col-sm-6 col-lg-4 col-xl-3">
            @include('frontend.partials.movie-card', ['movie' => $movie])
          </div>
        @empty
          <div class="col-12">
            <div class="glass-panel empty-panel">
              <i class="bi bi-search"></i>
              <p>Không có kết quả phù hợp. Hãy thử thay đổi bộ lọc.</p>
            </div>
          </div>
        @endforelse
      </div>

      <!-- Pagination -->
      @if($nowShowing->total() > 0)
        <div class="app-pagination mt-5">
          {{ $nowShowing->links('pagination::bootstrap-5') }}
        </div>
      @endif
    </div>
  </section>
@endsection
