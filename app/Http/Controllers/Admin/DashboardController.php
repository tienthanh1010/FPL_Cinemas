<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTicket;
use App\Models\Show;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $todayStart = now()->startOfDay();
        $todayEnd = now()->endOfDay();

        $showsToday = Show::with(['movieVersion.movie', 'auditorium.cinema'])
            ->whereBetween('start_time', [$todayStart, $todayEnd]);

        $todayShowCollection = (clone $showsToday)->get(['id', 'auditorium_id', 'start_time', 'end_time', 'status']);
        $showIds = $todayShowCollection->pluck('id');
        $auditoriumIds = $todayShowCollection->pluck('auditorium_id')->unique()->values();

        $seatCapacities = DB::table('seats')
            ->selectRaw('auditorium_id, COUNT(*) as capacity')
            ->whereIn('auditorium_id', $auditoriumIds)
            ->where('is_active', 1)
            ->groupBy('auditorium_id')
            ->pluck('capacity', 'auditorium_id');

        $ticketsByShow = DB::table('booking_tickets')
            ->selectRaw('show_id, COUNT(*) as sold')
            ->whereIn('show_id', $showIds)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->groupBy('show_id')
            ->pluck('sold', 'show_id');

        $upcomingShows = Show::with(['movieVersion.movie', 'auditorium.cinema'])
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->limit(8)
            ->get()
            ->map(function (Show $show) use ($seatCapacities, $ticketsByShow) {
                $capacity = (int) ($seatCapacities[$show->auditorium_id] ?? DB::table('seats')->where('auditorium_id', $show->auditorium_id)->where('is_active', 1)->count());
                $sold = (int) ($ticketsByShow[$show->id] ?? DB::table('booking_tickets')->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED'])->count());
                $show->occupancy_rate = $capacity > 0 ? round(($sold / $capacity) * 100, 1) : 0;
                $show->capacity = $capacity;
                $show->sold = $sold;
                return $show;
            });

        $nearlyFullShows = $todayShowCollection
            ->map(function (Show $show) use ($seatCapacities, $ticketsByShow) {
                $capacity = (int) ($seatCapacities[$show->auditorium_id] ?? 0);
                $sold = (int) ($ticketsByShow[$show->id] ?? 0);
                $show->occupancy_rate = $capacity > 0 ? round(($sold / $capacity) * 100, 1) : 0;
                $show->capacity = $capacity;
                $show->sold = $sold;
                return $show;
            })
            ->filter(fn (Show $show) => $show->occupancy_rate >= 70)
            ->sortByDesc('occupancy_rate')
            ->take(6);

        $stats = [
            'shows_today' => $todayShowCollection->count(),
            'tickets_today' => BookingTicket::query()
                ->join('shows', 'shows.id', '=', 'booking_tickets.show_id')
                ->whereBetween('shows.start_time', [$todayStart, $todayEnd])
                ->count(),
            'revenue_today' => (int) Booking::query()
                ->join('shows', 'shows.id', '=', 'bookings.show_id')
                ->whereBetween('shows.start_time', [$todayStart, $todayEnd])
                ->whereIn('bookings.status', ['PAID', 'CONFIRMED', 'COMPLETED'])
                ->sum('paid_amount'),
            'occupancy_today' => $this->calculateOccupancy($todayShowCollection, $seatCapacities, $ticketsByShow),
        ];

        $alerts = [
            'conflicts' => $this->findConflictingShows($todayStart, $todayEnd),
            'missingPrices' => Show::with(['movieVersion.movie', 'auditorium'])
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->whereDoesntHave('prices', fn ($query) => $query->where('is_active', 1))
                ->orderBy('start_time')
                ->get(),
            'notOnSale' => Show::with(['movieVersion.movie', 'auditorium'])
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->where(function ($query) {
                    $query->where('status', '!=', 'ON_SALE')
                        ->orWhereNull('on_sale_from')
                        ->orWhere('on_sale_from', '>', now());
                })
                ->orderBy('start_time')
                ->get(),
        ];

        return view('admin.dashboard', compact('stats', 'upcomingShows', 'nearlyFullShows', 'alerts'));
    }

    private function calculateOccupancy($shows, $seatCapacities, $ticketsByShow): float
    {
        $totalCapacity = $shows->sum(fn ($show) => (int) ($seatCapacities[$show->auditorium_id] ?? 0));
        $sold = $shows->sum(fn ($show) => (int) ($ticketsByShow[$show->id] ?? 0));
        return $totalCapacity > 0 ? round(($sold / $totalCapacity) * 100, 1) : 0;
    }

    private function findConflictingShows($start, $end)
    {
        $shows = Show::with(['movieVersion.movie', 'auditorium'])
            ->whereBetween('start_time', [$start, $end])
            ->orderBy('auditorium_id')
            ->orderBy('start_time')
            ->get();

        $conflicts = collect();

        foreach ($shows->groupBy('auditorium_id') as $group) {
            $ordered = $group->values();
            for ($i = 0; $i < $ordered->count(); $i++) {
                for ($j = $i + 1; $j < $ordered->count(); $j++) {
                    $first = $ordered[$i];
                    $second = $ordered[$j];
                    if ($second->start_time >= $first->end_time) {
                        break;
                    }
                    if ($first->start_time < $second->end_time && $second->start_time < $first->end_time) {
                        $conflicts->push((object) ['first' => $first, 'second' => $second]);
                    }
                }
            }
        }

        return $conflicts;
    }
}
