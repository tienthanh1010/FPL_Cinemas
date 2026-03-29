<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\BookingTicket;
use App\Models\Movie;
use App\Models\Review;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\Show;
use Illuminate\Support\Collection;
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

        $movie->load(['genres', 'contentRating', 'versions', 'reviews']);

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

        $seatMaps = $bookableShows->mapWithKeys(function (Show $show) {
            $seats = Seat::query()
                ->where('auditorium_id', $show->auditorium_id)
                ->where('is_active', 1)
                ->orderBy('row_label')
                ->orderBy('col_number')
                ->get();

            $reservedSeatIds = BookingTicket::query()
                ->where('show_id', $show->id)
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $blockedSeatIds = SeatBlock::query()
                ->where('auditorium_id', $show->auditorium_id)
                ->where('start_at', '<', $show->end_time)
                ->where('end_at', '>', $show->start_time)
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            return [$show->id => [
                'show_id' => $show->id,
                'auditorium' => $show->auditorium->name,
                'cinema' => $show->auditorium->cinema->name,
                'rows' => $seats->groupBy('row_label')->map(function (Collection $rowSeats) use ($reservedSeatIds, $blockedSeatIds) {
                    return $rowSeats->map(function (Seat $seat) use ($reservedSeatIds, $blockedSeatIds) {
                        $status = 'available';
                        if (in_array((int) $seat->id, $blockedSeatIds, true)) {
                            $status = 'blocked';
                        } elseif (in_array((int) $seat->id, $reservedSeatIds, true)) {
                            $status = 'reserved';
                        }

                        return [
                            'id' => $seat->id,
                            'code' => $seat->seat_code ?: ($seat->row_label . $seat->col_number),
                            'row' => $seat->row_label,
                            'col' => $seat->col_number,
                            'status' => $status,
                            'seat_type_id' => $seat->seat_type_id,
                        ];
                    })->values();
                })->values(),
            ]];
        });

        $reviews = Review::query()
            ->where('movie_id', $movie->id)
            ->where('is_approved', 1)
            ->latest('id')
            ->limit(12)
            ->get();

        $reviewStats = [
            'count' => $reviews->count(),
            'average' => round((float) $reviews->avg('rating'), 1),
        ];

        $paymentMethods = [
            'COUNTER' => 'Giữ chỗ - thanh toán tại quầy',
            'BANK_TRANSFER' => 'Chuyển khoản mô phỏng',
            'CARD' => 'Thẻ / ví điện tử mô phỏng',
            'CASH' => 'Thanh toán tiền mặt khi nhận vé',
        ];

        $trailerEmbedUrl = $this->buildTrailerEmbedUrl($movie->trailer_url);

        return view('frontend.showtimes', compact(
            'movie', 'shows', 'showsByDate', 'bookableShows', 'seatMaps', 'reviews', 'reviewStats', 'paymentMethods', 'trailerEmbedUrl'
        ));
    }

    private function buildTrailerEmbedUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        parse_str($parts['query'] ?? '', $query);

        if (str_contains($host, 'youtube.com')) {
            $id = $query['v'] ?? null;
            return $id ? 'https://www.youtube.com/embed/' . $id : null;
        }

        if (str_contains($host, 'youtu.be')) {
            $path = trim($parts['path'] ?? '', '/');
            return $path ? 'https://www.youtube.com/embed/' . $path : null;
        }

        if (str_contains($host, 'vimeo.com')) {
            $path = trim($parts['path'] ?? '', '/');
            return $path ? 'https://player.vimeo.com/video/' . $path : null;
        }

        return null;
    }
}
