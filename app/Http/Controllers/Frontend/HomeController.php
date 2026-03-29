<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Promotion;
use App\Models\Show;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        // Get all active movies with eager loading
        $moviesQuery = Movie::query()
            ->active()
            ->with(['genres', 'contentRating', 'versions.shows' => function ($q) {
                $q->where('status', 'ON_SALE')
                  ->where('end_time', '>', now())
                  ->orderBy('start_time')
                  ->limit(3);
            }, 'versions.shows.auditorium.cinema'])
            ->orderByDesc('release_date')
            ->limit(50);

        $movies = $moviesQuery->get();

        $today = now()->startOfDay();

        // Filter now showing movies
        $nowShowingMovies = $movies
            ->filter(fn (Movie $movie) => ! $movie->release_date || $movie->release_date->lessThanOrEqualTo($today))
            ->values();

        if ($nowShowingMovies->isEmpty()) {
            $nowShowingMovies = $movies->take(20)->values();
        }

        // Get filter options
        $formats = $this->getAvailableFormats($nowShowingMovies);
        $cinemas = Cinema::query()
            ->whereHas('auditoriums.shows', function ($q) {
                $q->where('end_time', '>', now())->where('status', 'ON_SALE');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);

        // Apply filters if requested
        $filterGenre = $request->get('genre');
        $filterFormat = $request->get('format');
        $filterCinema = $request->get('cinema');
        $sortBy = $request->get('sort', 'release_date');

        $filteredNowShowing = $nowShowingMovies
            ->when($filterGenre, fn ($items) => $items->filter(
                fn ($m) => $m->genres->pluck('id')->contains((int)$filterGenre)
            ))
            ->when($filterFormat, fn ($items) => $items->filter(
                fn ($m) => $m->versions->pluck('format')->contains($filterFormat)
            ))
            ->when($filterCinema, fn ($items) => $items->filter(
                fn ($m) => $m->versions
                    ->flatMap(fn ($v) => $v->shows)
                    ->pluck('auditorium.cinema_id')
                    ->unique()
                    ->contains((int)$filterCinema)
            ));

        // Apply sorting
        $filteredNowShowing = match($sortBy) {
            'release_date' => $filteredNowShowing->sortByDesc('release_date'),
            'title' => $filteredNowShowing->sortBy('title'),
            'popular' => $filteredNowShowing->sortByDesc(
                fn ($m) => $m->versions->sum(fn ($v) => $v->shows->count())
            ),
            default => $filteredNowShowing->sortByDesc('release_date')
        };

        // Get hero and special movies (not filtered)
        $heroMovies = $movies->take(3)->values();
        $comingSoon = $movies
            ->filter(fn (Movie $movie) => $movie->release_date && $movie->release_date->greaterThan($today))
            ->take(8)
            ->values();
        $specialMovies = $movies
            ->sortByDesc(fn (Movie $movie) => ($movie->genres->count() * 10) + $movie->duration_minutes)
            ->take(8)
            ->values();

        $stats = [
            'movie_count' => $nowShowingMovies->count(),
            'category_count' => $categories->count(),
            'show_count' => Show::query()->whereIn('status', ['SCHEDULED', 'ON_SALE'])->where('end_time', '>', now())->count(),
        ];

        // Active promotions for offers
        $promotions = Promotion::query()
            ->where('status', 'ACTIVE')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->with(['cinemas', 'movies'])
            ->get();

        $promotionTypes = ['ORDER' => 'Đơn hàng', 'TICKET' => 'Vé', 'PRODUCT' => 'Sản phẩm'];
        $promotionKinds = ['all' => 'Tất cả', 'coupon' => 'Thẻ', 'combo' => 'Combo', 'price' => 'Giá/tặng'];

        $nowShowing = $filteredNowShowing;

        return view('frontend.home', compact(
            'categories',
            'movies',
            'heroMovies',
            'comingSoon',
            'nowShowing',
            'specialMovies',
            'stats',
            'formats',
            'cinemas',
            'filterGenre',
            'filterFormat',
            'filterCinema',
            'sortBy',
            'promotions',
            'promotionTypes',
            'promotionKinds'
        ));
    }

    /**
     * Get available formats from movies
     */
    private function getAvailableFormats($movies)
    {
        return $movies
            ->flatMap(fn ($m) => $m->versions->pluck('format'))
            ->filter()
            ->unique()
            ->values()
            ->map(fn ($f) => ['value' => $f, 'label' => $f])
            ->all();
    }

    public function category(Category $category): View
    {
        $movies = $category->movies()
            ->where('movies.status', 'ACTIVE')
            ->with(['genres', 'contentRating'])
            ->orderByDesc('release_date')
            ->paginate(12);

        return view('frontend.category', compact('category', 'movies'));
    }

    public function showtimes(Movie $movie): View
    {
        abort_if($movie->status !== 'ACTIVE', 404);

        // Load all shows for this movie to display cinema/format options
        $allShows = Show::query()
            ->whereHas('movieVersion', fn ($query) => $query->where('movie_id', $movie->id))
            ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

        // Filter bookable shows (future and on sale)
        $shows = $allShows->filter(fn ($show) => $show->start_time >= now()->startOfDay());

        // If no future shows, use all shows for displaying filters
        if ($shows->isEmpty()) {
            $shows = $allShows->take(50);
        }

        $showsByDate = $shows->groupBy(fn (Show $show) => $show->start_time->format('Y-m-d'));

        $bookableShows = $shows
            ->filter(function (Show $show) {
                if ($show->status !== 'ON_SALE') {
                    return false;
                }

                if ($show->on_sale_from && now()->lt($show->on_sale_from)) {
                    return false;
                }

                if ($show->on_sale_until && now()->gt($show->on_sale_until)) {
                    return false;
                }

                return true;
            })
            ->values();

        return view('frontend.showtimes', compact('movie', 'shows', 'showsByDate', 'bookableShows'));
    }

    public function nowShowing(Request $request): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        // Get base query for active movies
        $baseQuery = Movie::query()
            ->active()
            ->with(['genres', 'contentRating', 'versions.shows' => function ($q) {
                $q->where('status', 'ON_SALE')
                  ->where('end_time', '>', now())
                  ->orderBy('start_time')
                  ->limit(3);
            }, 'versions.shows.auditorium.cinema']);

        $today = now()->startOfDay();

        // Get all active movies for filter options
        $moviesQuery = clone $baseQuery;
        $movies = $moviesQuery->orderByDesc('release_date')->limit(100)->get();

        // Filter now showing movies
        $nowShowingMovies = $movies
            ->filter(fn (Movie $movie) => ! $movie->release_date || $movie->release_date->lessThanOrEqualTo($today))
            ->values();

        if ($nowShowingMovies->isEmpty()) {
            $nowShowingMovies = $movies->take(50)->values();
        }

        // Get filter options
        $formats = $this->getAvailableFormats($nowShowingMovies);
        $cinemas = Cinema::query()
            ->whereHas('auditoriums.shows', function ($q) {
                $q->where('end_time', '>', now())->where('status', 'ON_SALE');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);

        // Apply filters if requested
        $filterGenre = $request->get('genre');
        $filterFormat = $request->get('format');
        $filterCinema = $request->get('cinema');
        $sortBy = $request->get('sort', 'release_date');

        $filteredNowShowing = $nowShowingMovies
            ->when($filterGenre, fn ($items) => $items->filter(
                fn ($m) => $m->genres->pluck('id')->contains((int)$filterGenre)
            ))
            ->when($filterFormat, fn ($items) => $items->filter(
                fn ($m) => $m->versions->pluck('format')->contains($filterFormat)
            ))
            ->when($filterCinema, fn ($items) => $items->filter(
                fn ($m) => $m->versions
                    ->flatMap(fn ($v) => $v->shows)
                    ->pluck('auditorium.cinema_id')
                    ->unique()
                    ->contains((int)$filterCinema)
            ));

        // Apply sorting
        $filteredNowShowing = match($sortBy) {
            'release_date' => $filteredNowShowing->sortByDesc('release_date'),
            'title' => $filteredNowShowing->sortBy('title'),
            'popular' => $filteredNowShowing->sortByDesc(
                fn ($m) => $m->versions->sum(fn ($v) => $v->shows->count())
            ),
            default => $filteredNowShowing->sortByDesc('release_date')
        };

        // Paginate the collection manually
        $perPage = 12;
        $currentPage = $request->get('page', 1);
        $items = $filteredNowShowing->forPage($currentPage, $perPage);
        
        $nowShowing = new LengthAwarePaginator(
            $items->values(),
            $filteredNowShowing->count(),
            $perPage,
            $currentPage,
            ['path' => route('movies.now_showing'), 'query' => $request->query()]
        );

        return view('frontend.now-showing', compact(
            'categories',
            'nowShowing',
            'formats',
            'cinemas',
            'filterGenre',
            'filterFormat',
            'filterCinema',
            'sortBy'
        ));
    }

    public function comingSoon(Request $request): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        $baseQuery = Movie::query()
            ->active()
            ->with(['genres', 'contentRating', 'versions.shows' => function ($q) {
                $q->where('status', 'ON_SALE')
                  ->where('end_time', '>', now())
                  ->orderBy('start_time')
                  ->limit(3);
            }, 'versions.shows.auditorium.cinema']);

        $today = now()->startOfDay();

        $movies = (clone $baseQuery)->orderByDesc('release_date')->limit(100)->get();

        $comingSoonMovies = $movies
            ->filter(fn (Movie $movie) => $movie->release_date && $movie->release_date->greaterThan($today))
            ->values();

        if ($comingSoonMovies->isEmpty()) {
            $comingSoonMovies = $movies->take(20)->values();
        }

        $formats = $this->getAvailableFormats($comingSoonMovies);
        $cinemas = Cinema::query()
            ->whereHas('auditoriums.shows', function ($q) {
                $q->where('end_time', '>', now())->where('status', 'ON_SALE');
            })
            ->orderBy('name')
            ->get()
            ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name]);

        $filterGenre = $request->get('genre');
        $filterFormat = $request->get('format');
        $filterCinema = $request->get('cinema');
        $sortBy = $request->get('sort', 'release_date');

        $filteredComingSoon = $comingSoonMovies
            ->when($filterGenre, fn ($items) => $items->filter(
                fn ($m) => $m->genres->pluck('id')->contains((int) $filterGenre)
            ))
            ->when($filterFormat, fn ($items) => $items->filter(
                fn ($m) => $m->versions->pluck('format')->contains($filterFormat)
            ))
            ->when($filterCinema, fn ($items) => $items->filter(
                fn ($m) => $m->versions
                    ->flatMap(fn ($v) => $v->shows)
                    ->pluck('auditorium.cinema_id')
                    ->unique()
                    ->contains((int) $filterCinema)
            ));

        $filteredComingSoon = match ($sortBy) {
            'release_date' => $filteredComingSoon->sortBy('release_date'),
            'title' => $filteredComingSoon->sortBy('title'),
            'popular' => $filteredComingSoon->sortByDesc(
                fn ($m) => $m->versions->sum(fn ($v) => $v->shows->count())
            ),
            default => $filteredComingSoon->sortBy('release_date')
        };

        $perPage = 12;
        $currentPage = $request->get('page', 1);
        $items = $filteredComingSoon->forPage($currentPage, $perPage);

        $comingSoon = new LengthAwarePaginator(
            $items->values(),
            $filteredComingSoon->count(),
            $perPage,
            $currentPage,
            ['path' => route('movies.coming_soon'), 'query' => $request->query()]
        );

        return view('frontend.coming-soon', compact(
            'categories',
            'comingSoon',
            'formats',
            'cinemas',
            'filterGenre',
            'filterFormat',
            'filterCinema',
            'sortBy'
        ));
    }

    public function offers(): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        $promotions = Promotion::query()
            ->where('status', 'ACTIVE')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->with(['cinemas', 'movies'])
            ->orderBy('start_at')
            ->get();

        $promotionTypes = ['ORDER' => 'Đơn hàng', 'TICKET' => 'Vé', 'PRODUCT' => 'Sản phẩm'];
        $groupedPromotions = $promotions->groupBy(fn ($promo) => $promo->customer_scope ?: 'Tất cả');

        return view('frontend.offers', compact('categories', 'promotions', 'promotionTypes', 'groupedPromotions'));
    }

    public function apiPromotions(Request $request)
    {
        $kind = $request->get('kind');
        $type = $request->get('type');
        $code = $request->get('code');
        $scope = $request->get('scope');

        $query = Promotion::query()
            ->where('status', 'ACTIVE')
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->with(['cinemas', 'movies']);

        if ($type && $type !== 'all') {
            $query->where('applies_to', $type);
        }

        $query->where(function ($q) use ($kind) {
            if (! $kind || $kind === 'all') {
                return;
            }

            if ($kind === 'coupon') {
                $q->where('coupon_required', true)->orWhere('code', 'like', '%COUPON%');
            } elseif ($kind === 'combo') {
                $q->where('name', 'like', '%combo%')->orWhere('description', 'like', '%combo%')->orWhere('code', 'like', '%COMBO%');
            } elseif ($kind === 'price') {
                $q->where('promo_type', 'PERCENT')->orWhere('promo_type', 'FIXED');
            }
        });

        if ($code) {
            $query->where('code', 'like', '%' . $code . '%');
        }

        if ($scope && $scope !== 'all') {
            $query->where('customer_scope', $scope);
        }

        $promotions = $query->orderBy('start_at', 'desc')->get()->map(function ($promo) {
            $kind = 'price';
            if ($promo->coupon_required || str_contains(strtoupper($promo->code), 'COUPON')) {
                $kind = 'coupon';
            } elseif (str_contains(strtolower($promo->name), 'combo') || str_contains(strtolower($promo->description), 'combo')) {
                $kind = 'combo';
            }

            return [
                'id' => $promo->id,
                'code' => $promo->code,
                'name' => $promo->name,
                'description' => $promo->description,
                'promo_type' => $promo->promo_type,
                'discount_value' => $promo->discount_value,
                'applies_to' => $promo->applies_to,
                'kind' => $kind,
                'customer_scope' => $promo->customer_scope ?: 'Tất cả',
                'cinemas' => $promo->cinemas->pluck('name'),
                'movies' => $promo->movies->pluck('title'),
                'start_at' => $promo->start_at->toIso8601String(),
                'end_at' => $promo->end_at->toIso8601String(),
            ];
        });

        return response()->json(['data' => $promotions]);
    }
}
