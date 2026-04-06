<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTicket;
use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $start = $request->filled('start_date')
            ? Carbon::parse($request->string('start_date'))->startOfDay()
            : now()->startOfMonth();
        $end = $request->filled('end_date')
            ? Carbon::parse($request->string('end_date'))->endOfDay()
            : now()->endOfDay();

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        $showsInRange = Show::query()->whereBetween('start_time', [$start, $end]);
        $bookingsInRange = Booking::query()->whereBetween('created_at', [$start, $end]);
        $ticketsInRange = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->whereBetween('shows.start_time', [$start, $end]);

        $summary = [
            'shows' => (clone $showsInRange)->count(),
            'tickets' => (clone $ticketsInRange)->count(),
            'revenue' => (int) (clone $bookingsInRange)->whereIn('status', ['PAID', 'CONFIRMED', 'COMPLETED'])->sum('paid_amount'),
            'occupancy' => $this->calculateOccupancy($start, $end),
        ];

        $revenueByMovie = Booking::query()
            ->join('shows', 'shows.id', '=', 'bookings.show_id')
            ->join('movie_versions', 'movie_versions.id', '=', 'shows.movie_version_id')
            ->join('movies', 'movies.id', '=', 'movie_versions.movie_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('bookings.status', ['PAID', 'CONFIRMED', 'COMPLETED'])
            ->groupBy('movies.id', 'movies.title')
            ->selectRaw('movies.id, movies.title, COUNT(DISTINCT bookings.id) as booking_count, COALESCE(SUM(bookings.paid_amount),0) as revenue')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $revenueByAuditorium = Booking::query()
            ->join('shows', 'shows.id', '=', 'bookings.show_id')
            ->join('auditoriums', 'auditoriums.id', '=', 'shows.auditorium_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('bookings.status', ['PAID', 'CONFIRMED', 'COMPLETED'])
            ->groupBy('auditoriums.id', 'auditoriums.name', 'auditoriums.screen_type')
            ->selectRaw('auditoriums.id, auditoriums.name, auditoriums.screen_type, COUNT(DISTINCT bookings.id) as booking_count, COALESCE(SUM(bookings.paid_amount),0) as revenue')
            ->orderByDesc('revenue')
            ->get();

        $ticketsByHour = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->groupBy(DB::raw('HOUR(shows.start_time)'))
            ->selectRaw('HOUR(shows.start_time) as hour_slot, COUNT(*) as tickets_sold')
            ->orderBy('hour_slot')
            ->get();

        $occupancyByDay = DB::table('shows')
            ->join('auditoriums', 'auditoriums.id', '=', 'shows.auditorium_id')
            ->leftJoin('seats', function ($join) {
                $join->on('seats.auditorium_id', '=', 'auditoriums.id')
                    ->where('seats.is_active', '=', 1);
            })
            ->leftJoin('booking_tickets', function ($join) {
                $join->on('booking_tickets.show_id', '=', 'shows.id')
                    ->whereIn('booking_tickets.status', ['RESERVED', 'ISSUED']);
            })
            ->whereBetween('shows.start_time', [$start, $end])
            ->groupBy(DB::raw('DATE(shows.start_time)'))
            ->selectRaw('DATE(shows.start_time) as report_date, COUNT(DISTINCT shows.id) as shows_count, COUNT(DISTINCT seats.id) as seats_per_day, COUNT(DISTINCT booking_tickets.id) as tickets_sold')
            ->orderBy('report_date')
            ->get()
            ->map(function ($row) {
                $capacity = max((int) $row->shows_count * (int) $row->seats_per_day, 0);
                $row->occupancy_rate = $capacity > 0 ? round(((int) $row->tickets_sold / $capacity) * 100, 1) : 0;
                return $row;
            });

        $topMovies = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->join('movie_versions', 'movie_versions.id', '=', 'shows.movie_version_id')
            ->join('movies', 'movies.id', '=', 'movie_versions.movie_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->groupBy('movies.id', 'movies.title')
            ->selectRaw('movies.id, movies.title, COUNT(booking_tickets.id) as tickets_sold, COALESCE(SUM(booking_tickets.final_price_amount),0) as revenue')
            ->orderByDesc('tickets_sold')
            ->limit(10)
            ->get();

        $topShows = Show::query()
            ->with(['movieVersion.movie', 'auditorium'])
            ->withCount(['tickets as tickets_sold' => function ($query) {
                $query->whereIn('status', ['RESERVED', 'ISSUED']);
            }])
            ->whereBetween('start_time', [$start, $end])
            ->orderByDesc('tickets_sold')
            ->limit(10)
            ->get()
            ->map(function (Show $show) {
                $capacity = DB::table('seats')
                    ->where('auditorium_id', $show->auditorium_id)
                    ->where('is_active', 1)
                    ->count();
                $show->occupancy_rate = $capacity > 0 ? round(($show->tickets_sold / $capacity) * 100, 1) : 0;
                $show->gross_revenue = (int) DB::table('booking_tickets')
                    ->where('show_id', $show->id)
                    ->sum('final_price_amount');
                return $show;
            });

        $dailySummary = [
            'today' => $this->buildPeriodSummary(now()->startOfDay(), now()->endOfDay()),
            'month' => $this->buildPeriodSummary(now()->startOfMonth(), now()->endOfMonth()),
        ];

        return view('admin.reports.index', compact(
            'start',
            'end',
            'summary',
            'revenueByMovie',
            'revenueByAuditorium',
            'ticketsByHour',
            'occupancyByDay',
            'topMovies',
            'topShows',
            'dailySummary'
        ));
    }

    private function buildPeriodSummary(Carbon $start, Carbon $end): array
    {
        $showCount = Show::query()->whereBetween('start_time', [$start, $end])->count();
        $tickets = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->count();
        $revenue = (int) Booking::query()
            ->join('shows', 'shows.id', '=', 'bookings.show_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('bookings.status', ['PAID', 'CONFIRMED', 'COMPLETED'])
            ->sum('paid_amount');

        return [
            'shows' => $showCount,
            'tickets' => $tickets,
            'revenue' => $revenue,
            'occupancy' => $this->calculateOccupancy($start, $end),
        ];
    }

    private function calculateOccupancy(Carbon $start, Carbon $end): float
    {
        $shows = Show::query()
            ->whereBetween('start_time', [$start, $end])
            ->get(['id', 'auditorium_id']);

        if ($shows->isEmpty()) {
            return 0;
        }

        $auditoriumIds = $shows->pluck('auditorium_id')->unique()->values();
        $seatCapacities = DB::table('seats')
            ->selectRaw('auditorium_id, COUNT(*) as capacity')
            ->whereIn('auditorium_id', $auditoriumIds)
            ->where('is_active', 1)
            ->groupBy('auditorium_id')
            ->pluck('capacity', 'auditorium_id');

        $totalCapacity = $shows->sum(fn ($show) => (int) ($seatCapacities[$show->auditorium_id] ?? 0));
        $soldTickets = BookingTicket::query()
            ->whereIn('show_id', $shows->pluck('id'))
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->count();

        return $totalCapacity > 0 ? round(($soldTickets / $totalCapacity) * 100, 1) : 0;
    }
}
