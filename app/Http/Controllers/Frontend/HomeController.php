<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BookingTicket;
use App\Models\Category;
<<<<<<< HEAD
use App\Models\InventoryBalance;
use App\Models\Movie;
use App\Models\Product;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\SeatType;
use App\Models\Show;
use App\Models\ShowPrice;
use App\Models\TicketType;
use App\Services\ProductPricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
=======
use App\Models\BookingTicket;
use App\Models\Movie;
use App\Models\Review;
use App\Models\Seat;
use App\Models\SeatBlock;
use App\Models\Show;
<<<<<<< HEAD
=======
use Illuminate\Support\Collection;
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use Illuminate\View\View;

class HomeController extends Controller
{
<<<<<<< HEAD
    public function __construct(
        private readonly ProductPricingService $productPricingService,
    ) {
    }

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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

<<<<<<< HEAD
        $featuredMovieIds = $heroMovies
            ->concat($comingSoon)
            ->concat($nowShowing)
            ->concat($specialMovies)
            ->pluck('id')
            ->unique()
            ->values();

        $currentCinemaId = current_cinema_id();

        $upcomingShows = Show::query()
            ->frontendVisible()
            ->when($currentCinemaId, fn ($query) => $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $currentCinemaId)))
            ->whereHas('movieVersion', fn ($query) => $query->whereIn('movie_id', $featuredMovieIds))
            ->whereHas('movieVersion.movie', fn ($query) => $query->where('status', 'ACTIVE'))
            ->whereHas('auditorium', fn ($query) => $query->where('is_active', 1)->whereHas('cinema', fn ($cinemaQuery) => $cinemaQuery->where('status', 'ACTIVE')))
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

        $showtimesByMovie = [];
        foreach ($upcomingShows as $show) {
            $movieId = $show->movieVersion?->movie_id;
            if (! $movieId) {
                continue;
            }

            $dateKey = $show->start_time->format('Y-m-d');
            $showtimesByMovie[$movieId] ??= [
                'count' => 0,
                'groups' => [],
                'first_show_at' => null,
            ];

            if (count($showtimesByMovie[$movieId]['groups']) >= 3 && ! isset($showtimesByMovie[$movieId]['groups'][$dateKey])) {
                continue;
            }

            $showtimesByMovie[$movieId]['groups'][$dateKey] ??= [
                'date_key' => $dateKey,
                'date_label' => $show->start_time->translatedFormat('D, d/m'),
                'full_date' => $show->start_time->translatedFormat('l, d/m/Y'),
                'day_number' => $show->start_time->format('d'),
                'month_label' => $show->start_time->format('m'),
                'weekday_short' => mb_strtoupper($show->start_time->translatedFormat('D')),
                'shows' => [],
            ];

            if (count($showtimesByMovie[$movieId]['groups'][$dateKey]['shows']) >= 5) {
                continue;
            }

            $showtimesByMovie[$movieId]['groups'][$dateKey]['shows'][] = [
                'id' => (int) $show->id,
                'time' => $show->start_time->format('H:i'),
                'end_time' => $show->end_time?->format('H:i'),
                'status' => $show->status,
                'status_label' => $show->frontendStatusLabel(),
                'format' => $show->movieVersion?->format ?: '2D',
                'auditorium' => $show->auditorium?->name ?: 'Phòng chiếu',
                'cinema' => $show->auditorium?->cinema?->name ?: config('app.name', 'FPL Cinemas'),
                'is_on_sale' => $show->isOnSaleNow(),
            ];

            $showtimesByMovie[$movieId]['count']++;
            $showtimesByMovie[$movieId]['first_show_at'] ??= $show->start_time?->format('d/m H:i');
        }

        foreach ($showtimesByMovie as &$payload) {
            $payload['groups'] = array_values($payload['groups']);
        }
        unset($payload);

        $stats = [
            'movie_count' => $movies->count(),
            'category_count' => $categories->count(),
            'show_count' => Show::query()
                ->when($currentCinemaId, fn ($query) => $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $currentCinemaId)))
                ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
                ->count(),
=======
        $stats = [
            'movie_count' => $movies->count(),
            'category_count' => $categories->count(),
            'show_count' => Show::query()->whereIn('status', ['SCHEDULED', 'ON_SALE'])->count(),
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
        ];

        return view('frontend.home', compact(
            'categories',
            'movies',
            'heroMovies',
            'comingSoon',
            'nowShowing',
            'specialMovies',
<<<<<<< HEAD
            'showtimesByMovie',
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
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

<<<<<<< HEAD
    public function showtimes(Request $request, Movie $movie): View
    {
        abort_if($movie->status !== 'ACTIVE', 404);

        $movie->loadMissing(['genres', 'contentRating']);

        $currentCinemaId = current_cinema_id();

        $shows = Show::query()
            ->frontendVisible()
            ->when($currentCinemaId, fn ($query) => $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $currentCinemaId)))
            ->whereHas('movieVersion', fn ($query) => $query->where('movie_id', $movie->id))
            ->whereHas('movieVersion.movie', fn ($query) => $query->where('status', 'ACTIVE'))
            ->whereHas('auditorium', fn ($query) => $query->where('is_active', 1)->whereHas('cinema', fn ($cinemaQuery) => $cinemaQuery->where('status', 'ACTIVE')))
=======
    public function showtimes(Movie $movie): View
    {
        abort_if($movie->status !== 'ACTIVE', 404);
<<<<<<< HEAD
=======

        $movie->load(['genres', 'contentRating', 'versions', 'reviews']);
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2

        $shows = Show::query()
            ->whereHas('movieVersion', fn ($query) => $query->where('movie_id', $movie->id))
            ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
            ->where('start_time', '>', now())
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

        $showsByDate = $shows->groupBy(fn (Show $show) => $show->start_time->format('Y-m-d'));

        $bookableShows = $shows
<<<<<<< HEAD
            ->filter(fn (Show $show) => $show->isOnSaleNow())
            ->values();

        $ticketTypes = TicketType::query()->orderBy('id')->get(['id', 'code', 'name', 'description']);
        $seatTypes = SeatType::query()->get(['id', 'code', 'name'])->keyBy('id');
        $products = Product::query()
            ->where('is_active', 1)
            ->with('category:id,name')
            ->orderByDesc('is_combo')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'sku', 'unit', 'is_combo', 'attributes']);

        $cinemaIds = $bookableShows->pluck('auditorium.cinema_id')->filter()->unique()->values();
        $inventoryRows = InventoryBalance::query()
            ->selectRaw('inventory_balances.product_id, stock_locations.cinema_id, SUM(inventory_balances.qty_on_hand) as qty_on_hand')
            ->join('stock_locations', 'stock_locations.id', '=', 'inventory_balances.stock_location_id')
            ->whereIn('stock_locations.cinema_id', $cinemaIds)
            ->where('stock_locations.is_active', 1)
            ->groupBy('inventory_balances.product_id', 'stock_locations.cinema_id')
            ->get();

        $inventoryByCinema = [];
        foreach ($inventoryRows as $row) {
            $inventoryByCinema[(int) $row->cinema_id][(int) $row->product_id] = (int) $row->qty_on_hand;
        }

        $auditoriumSeats = [];
        $bookingConfigs = [];

        foreach ($bookableShows as $show) {
            $auditoriumId = $show->auditorium_id;

            if (! isset($auditoriumSeats[$auditoriumId])) {
                $auditoriumSeats[$auditoriumId] = Seat::query()
                    ->where('auditorium_id', $auditoriumId)
                    ->where('is_active', 1)
                    ->orderBy('row_label')
                    ->orderBy('col_number')
                    ->get(['id', 'seat_type_id', 'seat_code', 'row_label', 'col_number']);
            }
=======
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

<<<<<<< HEAD
        return view('frontend.showtimes', compact('movie', 'shows', 'showsByDate', 'bookableShows'));
=======
        $seatMaps = $bookableShows->mapWithKeys(function (Show $show) {
            $seats = Seat::query()
                ->where('auditorium_id', $show->auditorium_id)
                ->where('is_active', 1)
                ->orderBy('row_label')
                ->orderBy('col_number')
                ->get();
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

            $reservedSeatIds = BookingTicket::query()
                ->where('show_id', $show->id)
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

<<<<<<< HEAD
            $heldSeatIds = DB::table('seat_holds')
                ->where('show_id', $show->id)
                ->whereIn('status', ['HELD', 'CONFIRMED'])
                ->where('expires_at', '>', now())
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $blockedSeatIds = SeatBlock::query()
                ->where('auditorium_id', $auditoriumId)
=======
            $blockedSeatIds = SeatBlock::query()
                ->where('auditorium_id', $show->auditorium_id)
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
                ->where('start_at', '<', $show->end_time)
                ->where('end_at', '>', $show->start_time)
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

<<<<<<< HEAD
            $unavailableSeatIds = array_fill_keys(array_unique(array_merge($reservedSeatIds, $heldSeatIds, $blockedSeatIds)), true);

            $priceMatrix = [];
            $showPrices = ShowPrice::query()
                ->where('show_id', $show->id)
                ->where('is_active', 1)
                ->get(['seat_type_id', 'ticket_type_id', 'price_amount']);

            foreach ($showPrices as $price) {
                $priceMatrix[$price->seat_type_id][$price->ticket_type_id] = (int) $price->price_amount;
            }

            $seatPayload = $auditoriumSeats[$auditoriumId]
                ->map(function (Seat $seat) use ($seatTypes, $unavailableSeatIds) {
                    $seatType = $seatTypes->get($seat->seat_type_id);

                    return [
                        'id' => $seat->id,
                        'seat_code' => $seat->seat_code,
                        'row_label' => $seat->row_label,
                        'col_number' => (int) $seat->col_number,
                        'seat_type_id' => (int) $seat->seat_type_id,
                        'seat_type_name' => $seatType?->name ?? 'Ghế',
                        'seat_type_code' => $seatType?->code ?? 'REGULAR',
                        'available' => ! isset($unavailableSeatIds[$seat->id]),
                    ];
                })
                ->values()
                ->all();

            $availableCount = collect($seatPayload)->where('available', true)->count();

            $cinemaId = (int) $show->auditorium->cinema_id;
            $productPayload = $products
                ->map(function (Product $product) use ($cinemaId, $inventoryByCinema) {
                    $price = $this->productPricingService->currentPrice($product, $cinemaId);
                    if (! $price) {
                        return null;
                    }

                    $qtyOnHand = (int) ($inventoryByCinema[$cinemaId][$product->id] ?? 0);
                    $attributes = is_array($product->attributes) ? $product->attributes : [];

                    return [
                        'id' => (int) $product->id,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'category' => $product->category?->name ?? 'F&B',
                        'description' => (string) ($attributes['description'] ?? ''),
                        'unit' => $product->unit,
                        'is_combo' => (bool) $product->is_combo,
                        'price_amount' => (int) $price->price_amount,
                        'currency' => $price->currency,
                        'qty_on_hand' => $qtyOnHand,
                        'available' => $qtyOnHand > 0,
                    ];
                })
                ->filter()
                ->values()
                ->all();

            $bookingConfigs[$show->id] = [
                'id' => $show->id,
                'label' => sprintf(
                    '%s · %s · %s',
                    $show->start_time->format('d/m H:i'),
                    $show->auditorium->name,
                    $show->movieVersion->format
                ),
                'meta' => [
                    'start_time' => $show->start_time->format('d/m/Y H:i'),
                    'end_time' => $show->end_time->format('H:i'),
                    'auditorium' => $show->auditorium->name,
                    'cinema' => $show->auditorium->cinema->name,
                    'format' => $show->movieVersion->format,
                    'audio_language' => $show->movieVersion->audio_language,
                    'subtitle_language' => $show->movieVersion->subtitle_language,
                    'available_count' => $availableCount,
                ],
                'prices' => $priceMatrix,
                'seats' => $seatPayload,
                'products' => $productPayload,
            ];
        }

        $preselectedShowId = $request->integer('show') ?: null;

        return view('frontend.showtimes', compact(
            'movie',
            'shows',
            'showsByDate',
            'bookableShows',
            'ticketTypes',
            'bookingConfigs',
            'preselectedShowId'
        ));
=======
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
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    }
}
