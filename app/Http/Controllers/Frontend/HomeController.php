<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Movie;
use App\Models\Show;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->active()
            ->withCount(['movies as active_movies_count' => function ($q) {
                $q->where('movies.status', 'ACTIVE');
            }])
            ->orderBy('name')
            ->get();

        $movies = Movie::query()
            ->active()
            ->with(['categories' => function ($q) {
                $q->where('genres.is_active', 1);
            }])
            ->orderByDesc('release_date')
            ->limit(12)
            ->get();

        return view('frontend.home', compact('categories', 'movies'));
    }

    public function category(Category $category)
    {
        if (! $category->is_active) {
            abort(404);
        }

        $movies = $category->movies()
            ->where('movies.status', 'ACTIVE')
            ->with('categories')
            ->orderByDesc('release_date')
            ->paginate(12);

        return view('frontend.category', compact('category', 'movies'));
    }

    public function showtimes(Movie $movie)
    {
        if ($movie->status !== 'ACTIVE') {
            abort(404);
        }

        $shows = Show::query()
            ->whereHas('movieVersion', fn ($q) => $q->where('movie_id', $movie->id))
            ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

        return view('frontend.showtimes', compact('movie', 'shows'));
    }
}
