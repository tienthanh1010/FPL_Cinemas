<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Movie;
use App\Models\Show;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        $movies = Movie::query()
            ->active()
            ->with(['genres', 'contentRating', 'versions'])
            ->orderByDesc('release_date')
            ->limit(18)
            ->get();

        $today = now()->startOfDay();

        $heroMovies = $movies->take(3)->values();
        $comingSoon = $movies
            ->filter(fn (Movie $movie) => $movie->release_date && $movie->release_date->greaterThan($today))
            ->take(8)
            ->values();

        $nowShowing = $movies
            ->filter(fn (Movie $movie) => ! $movie->release_date || $movie->release_date->lessThanOrEqualTo($today))
            ->take(8)
            ->values();

        if ($nowShowing->isEmpty()) {
            $nowShowing = $movies->take(8)->values();
        }

        $specialMovies = $movies
            ->sortByDesc(fn (Movie $movie) => ($movie->genres->count() * 10) + $movie->duration_minutes)
            ->take(8)
            ->values();

        $stats = [
            'movie_count' => $movies->count(),
            'category_count' => $categories->count(),
            'show_count' => Show::query()->whereIn('status', ['SCHEDULED', 'ON_SALE'])->count(),
        ];

        return view('frontend.home', compact(
            'categories',
            'movies',
            'heroMovies',
            'comingSoon',
            'nowShowing',
            'specialMovies',
            'stats'
        ));
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

        $shows = Show::query()
            ->whereHas('movieVersion', fn ($query) => $query->where('movie_id', $movie->id))
            ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

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
}
