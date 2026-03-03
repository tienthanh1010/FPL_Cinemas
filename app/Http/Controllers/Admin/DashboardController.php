<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Cinema;
use App\Models\CinemaChain;
use App\Models\Movie;
use App\Models\Show;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'movies' => Movie::count(),
            'chains' => CinemaChain::count(),
            'cinemas' => Cinema::count(),
            'auditoriums' => Auditorium::count(),
            'shows' => Show::count(),
        ];

        $latestShows = Show::with(['movieVersion.movie', 'auditorium.cinema'])
            ->orderByDesc('start_time')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'latestShows'));
    }
}
