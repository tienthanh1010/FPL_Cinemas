<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\BookingTicket;
use App\Models\Category;
use App\Models\CustomerFeedback;
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
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly ProductPricingService $productPricingService,
    ) {
    }

    public function index(): View
    {
        $categories = Category::query()
            ->withCount('movies')
            ->orderBy('name')
            ->get();

        $movies = Movie::query()
            ->active()
            ->with(['genres', 'contentRating', 'versions'])
            ->orderByDesc('is_hot')
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->limit(24)
            ->get();

        $currentCinemaId = current_cinema_id();

        $upcomingShows = Show::query()
            ->frontendVisible()
            ->when($currentCinemaId, fn ($query) => $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $currentCinemaId)))
            ->whereHas('movieVersion', fn ($query) => $query->whereIn('movie_id', $movies->pluck('id')))
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
                'has_on_sale' => false,
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

            $isOnSale = $show->isOnSaleNow();
            $showtimesByMovie[$movieId]['groups'][$dateKey]['shows'][] = [
                'id' => (int) $show->id,
                'time' => $show->start_time->format('H:i'),
                'end_time' => $show->end_time?->format('H:i'),
                'status' => $show->status,
                'status_label' => $show->frontendStatusLabel(),
                'format' => $show->movieVersion?->format ?: '2D',
                'auditorium' => $show->auditorium?->name ?: 'Phòng chiếu',
                'cinema' => $show->auditorium?->cinema?->name ?: config('app.name', 'FPL Cinemas'),
                'is_on_sale' => $isOnSale,
            ];

            $showtimesByMovie[$movieId]['count']++;
            $showtimesByMovie[$movieId]['has_on_sale'] = $showtimesByMovie[$movieId]['has_on_sale'] || $isOnSale;
            $showtimesByMovie[$movieId]['first_show_at'] ??= $show->start_time?->format('d/m H:i');
        }

        foreach ($showtimesByMovie as &$payload) {
            $payload['groups'] = array_values($payload['groups']);
        }
        unset($payload);

        $sliderMovies = $movies
            ->filter(fn (Movie $movie) => (bool) $movie->is_on_slider)
            ->take(3)
            ->values();

        if ($sliderMovies->isEmpty()) {
            $sliderMovies = $movies->take(3)->values();
        }

        $hotMovies = $movies
            ->filter(fn (Movie $movie) => (bool) $movie->is_hot)
            ->take(6)
            ->values();

        $nowShowing = $movies
            ->filter(fn (Movie $movie) => (bool) data_get($showtimesByMovie, $movie->id . '.has_on_sale', false))
            ->take(9)
            ->values();

        $comingSoon = $movies
            ->filter(fn (Movie $movie) => ! isset($showtimesByMovie[$movie->id]))
            ->take(9)
            ->values();

        $stats = [
            'movie_count' => $movies->count(),
            'category_count' => $categories->count(),
            'show_count' => Show::query()
                ->when($currentCinemaId, fn ($query) => $query->whereHas('auditorium', fn ($auditoriumQuery) => $auditoriumQuery->where('cinema_id', $currentCinemaId)))
                ->whereIn('status', ['SCHEDULED', 'ON_SALE'])
                ->count(),
        ];

        return view('frontend.home', compact(
            'categories',
            'movies',
            'sliderMovies',
            'hotMovies',
            'comingSoon',
            'nowShowing',
            'showtimesByMovie',
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
            ->orderBy('start_time')
            ->with(['auditorium.cinema', 'movieVersion'])
            ->get();

        $showsByDate = $shows->groupBy(fn (Show $show) => $show->start_time->format('Y-m-d'));

        $bookableShows = $shows
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

            $reservedSeatIds = BookingTicket::query()
                ->where('show_id', $show->id)
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $heldSeatIds = DB::table('seat_holds')
                ->where('show_id', $show->id)
                ->whereIn('status', ['HELD', 'CONFIRMED'])
                ->where('expires_at', '>', now())
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $blockedSeatIds = SeatBlock::query()
                ->where('auditorium_id', $auditoriumId)
                ->where('start_at', '<', $show->end_time)
                ->where('end_at', '>', $show->start_time)
                ->pluck('seat_id')
                ->map(fn ($id) => (int) $id)
                ->all();

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

        $movieFeedbackSummary = CustomerFeedback::query()
            ->published()
            ->where('movie_id', $movie->id)
            ->selectRaw('COUNT(*) as total_reviews, AVG(movie_rating) as avg_movie_rating')
            ->first();

        $movieFeedbacks = CustomerFeedback::query()
            ->published()
            ->where('movie_id', $movie->id)
            ->where(function ($query) {
                $query->whereNotNull('movie_comment')
                    ->orWhereNotNull('overall_comment');
            })
            ->latest('id')
            ->limit(6)
            ->get(['reviewer_name', 'movie_rating', 'movie_comment', 'overall_comment', 'created_at']);

        return view('frontend.showtimes', compact(
            'movie',
            'shows',
            'showsByDate',
            'bookableShows',
            'ticketTypes',
            'bookingConfigs',
            'preselectedShowId',
            'movieFeedbackSummary',
            'movieFeedbacks'
        ));
    }
}
