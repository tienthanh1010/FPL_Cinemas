<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingTicket;
use App\Models\Show;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const SOLD_TICKET_STATUSES = ['ISSUED'];
    private const CAPTURED_PAYMENT_STATUSES = ['CAPTURED'];
    private const SUCCESS_REFUND_STATUS = 'SUCCESS';

    public function index(Request $request): View
    {
        [$start, $end, $period, $periodLabel] = $this->resolvePeriod($request);

        $summary = $this->buildPeriodSummary($start, $end);
        $quickSummaries = [
            'day' => $this->buildPeriodSummary(now()->startOfDay(), now()->endOfDay()),
            'month' => $this->buildPeriodSummary(now()->startOfMonth(), now()->endOfMonth()),
            'quarter' => $this->buildPeriodSummary(now()->startOfQuarter(), now()->endOfQuarter()),
            'year' => $this->buildPeriodSummary(now()->startOfYear(), now()->endOfYear()),
        ];

        $revenueByMovie = $this->paymentRevenueQuery($start, $end)
            ->join('shows', 'shows.id', '=', 'bookings.show_id')
            ->join('movie_versions', 'movie_versions.id', '=', 'shows.movie_version_id')
            ->join('movies', 'movies.id', '=', 'movie_versions.movie_id')
            ->groupBy('movies.id', 'movies.title')
            ->selectRaw('movies.id, movies.title, COUNT(DISTINCT bookings.id) as booking_count, COALESCE(SUM(payments.amount),0) - COALESCE(SUM(success_refunds.refund_amount),0) as revenue')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $revenueByAuditorium = $this->paymentRevenueQuery($start, $end)
            ->join('shows', 'shows.id', '=', 'bookings.show_id')
            ->join('auditoriums', 'auditoriums.id', '=', 'shows.auditorium_id')
            ->groupBy('auditoriums.id', 'auditoriums.name', 'auditoriums.screen_type')
            ->selectRaw('auditoriums.id, auditoriums.name, auditoriums.screen_type, COUNT(DISTINCT bookings.id) as booking_count, COALESCE(SUM(payments.amount),0) - COALESCE(SUM(success_refunds.refund_amount),0) as revenue')
            ->orderByDesc('revenue')
            ->get();

        $ticketsByHour = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('booking_tickets.status', self::SOLD_TICKET_STATUSES)
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
                    ->whereIn('booking_tickets.status', self::SOLD_TICKET_STATUSES);
            })
            ->whereBetween('shows.start_time', [$start, $end])
            ->groupBy(DB::raw('DATE(shows.start_time)'))
            ->selectRaw('DATE(shows.start_time) as report_date, COUNT(DISTINCT shows.id) as shows_count, COUNT(DISTINCT seats.id) as seats_per_show, COUNT(DISTINCT booking_tickets.id) as tickets_sold')
            ->orderBy('report_date')
            ->get()
            ->map(function ($row) {
                $capacity = max((int) $row->shows_count * (int) $row->seats_per_show, 0);
                $row->occupancy_rate = $capacity > 0 ? round(((int) $row->tickets_sold / $capacity) * 100, 1) : 0;
                return $row;
            });

        $topMovies = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->join('movie_versions', 'movie_versions.id', '=', 'shows.movie_version_id')
            ->join('movies', 'movies.id', '=', 'movie_versions.movie_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('booking_tickets.status', self::SOLD_TICKET_STATUSES)
            ->groupBy('movies.id', 'movies.title')
            ->selectRaw('movies.id, movies.title, COUNT(booking_tickets.id) as tickets_sold, COALESCE(SUM(booking_tickets.final_price_amount),0) as ticket_gross')
            ->orderByDesc('tickets_sold')
            ->limit(10)
            ->get();

        $topShows = Show::query()
            ->with(['movieVersion.movie', 'auditorium'])
            ->withCount(['tickets as tickets_sold' => fn ($query) => $query->whereIn('status', self::SOLD_TICKET_STATUSES)])
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
                    ->whereIn('status', self::SOLD_TICKET_STATUSES)
                    ->sum('final_price_amount');
                return $show;
            });

        return view('admin.reports.index', compact(
            'start',
            'end',
            'period',
            'periodLabel',
            'summary',
            'quickSummaries',
            'revenueByMovie',
            'revenueByAuditorium',
            'ticketsByHour',
            'occupancyByDay',
            'topMovies',
            'topShows'
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $period = (string) $request->query('period', 'month');
        $base = $request->filled('date') ? Carbon::parse($request->string('date')) : now();

        [$start, $end, $label] = match ($period) {
            'day' => [$base->copy()->startOfDay(), $base->copy()->endOfDay(), 'Theo ngày'],
            'quarter' => [$base->copy()->startOfQuarter(), $base->copy()->endOfQuarter(), 'Theo quý'],
            'year' => [$base->copy()->startOfYear(), $base->copy()->endOfYear(), 'Theo năm'],
            'custom' => [
                $request->filled('start_date') ? Carbon::parse($request->string('start_date'))->startOfDay() : now()->startOfMonth(),
                $request->filled('end_date') ? Carbon::parse($request->string('end_date'))->endOfDay() : now()->endOfDay(),
                'Tùy chọn',
            ],
            default => [$base->copy()->startOfMonth(), $base->copy()->endOfMonth(), 'Theo tháng'],
        };

        if ($end->lt($start)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end, in_array($period, ['day', 'month', 'quarter', 'year', 'custom'], true) ? $period : 'month', $label];
    }

    private function buildPeriodSummary(Carbon $start, Carbon $end): array
    {
        $showCount = Show::query()->whereBetween('start_time', [$start, $end])->count();
        $tickets = BookingTicket::query()
            ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
            ->whereBetween('shows.start_time', [$start, $end])
            ->whereIn('booking_tickets.status', self::SOLD_TICKET_STATUSES)
            ->count();

        $grossRevenue = (int) $this->paymentRevenueQuery($start, $end)->sum('payments.amount');
        $refundAmount = (int) $this->paymentRevenueQuery($start, $end)->sum(DB::raw('COALESCE(success_refunds.refund_amount,0)'));

        return [
            'shows' => $showCount,
            'tickets' => $tickets,
            'gross_revenue' => $grossRevenue,
            'refund_amount' => $refundAmount,
            'revenue' => max(0, $grossRevenue - $refundAmount),
            'occupancy' => $this->calculateOccupancy($start, $end),
        ];
    }

    private function paymentRevenueQuery(Carbon $start, Carbon $end)
    {
        $refundSubquery = DB::table('refunds')
            ->selectRaw('payment_id, SUM(amount) as refund_amount')
            ->where('status', self::SUCCESS_REFUND_STATUS)
            ->groupBy('payment_id');

        return DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->leftJoinSub($refundSubquery, 'success_refunds', fn ($join) => $join->on('success_refunds.payment_id', '=', 'payments.id'))
            ->where('payments.status', self::CAPTURED_PAYMENT_STATUSES[0])
            ->whereBetween('payments.paid_at', [$start, $end]);
    }

    private function calculateOccupancy(Carbon $start, Carbon $end): float
    {
        $shows = Show::query()->whereBetween('start_time', [$start, $end])->get(['id', 'auditorium_id']);
        if ($shows->isEmpty()) {
            return 0;
        }

        $seatCapacities = DB::table('seats')
            ->selectRaw('auditorium_id, COUNT(*) as capacity')
            ->whereIn('auditorium_id', $shows->pluck('auditorium_id')->unique()->values())
            ->where('is_active', 1)
            ->groupBy('auditorium_id')
            ->pluck('capacity', 'auditorium_id');

        $totalCapacity = $shows->sum(fn ($show) => (int) ($seatCapacities[$show->auditorium_id] ?? 0));
        $soldTickets = BookingTicket::query()
            ->whereIn('show_id', $shows->pluck('id'))
            ->whereIn('status', self::SOLD_TICKET_STATUSES)
            ->count();

        return $totalCapacity > 0 ? round(($soldTickets / $totalCapacity) * 100, 1) : 0;
    }
}
