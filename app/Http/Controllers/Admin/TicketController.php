<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Movie;
use App\Models\Show;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    private const STATUS_OPTIONS = [
        'ISSUED' => 'Chưa check-in',
        'USED' => 'Đã check-in',
        'VOID' => 'Vô hiệu',
        'REFUNDED' => 'Đã hoàn',
    ];

    private const BOOKING_STATUS_OPTIONS = [
        'PENDING' => 'Chờ thanh toán',
        'PAID' => 'Đã thanh toán',
        'CONFIRMED' => 'Đã xác nhận',
        'COMPLETED' => 'Hoàn tất',
        'EXPIRED' => 'Hết hạn',
        'CANCELLED' => 'Đã huỷ',
    ];

    private const CHECKIN_ALLOWED_BOOKING_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'booking_status' => ['nullable', Rule::in(array_keys(self::BOOKING_STATUS_OPTIONS))],
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'show_id' => ['nullable', 'integer', 'exists:shows,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Ticket::query()
            ->with([
                'bookingTicket.seat',
                'bookingTicket.ticketType',
                'bookingTicket.seatType',
                'bookingTicket.booking.customer',
                'bookingTicket.booking.show.movieVersion.movie',
                'bookingTicket.booking.show.auditorium',
            ])
            ->when(! empty($filters['q']), function ($ticketQuery) use ($filters) {
                $q = trim((string) $filters['q']);

                $ticketQuery->where(function ($subQuery) use ($q) {
                    $subQuery->where('ticket_code', 'like', "%{$q}%")
                        ->orWhere('qr_payload', 'like', "%{$q}%")
                        ->orWhereHas('bookingTicket.booking', function ($bookingQuery) use ($q) {
                            $bookingQuery->where('booking_code', 'like', "%{$q}%")
                                ->orWhere('contact_name', 'like', "%{$q}%")
                                ->orWhere('contact_phone', 'like', "%{$q}%")
                                ->orWhere('contact_email', 'like', "%{$q}%")
                                ->orWhereHas('customer', function ($customerQuery) use ($q) {
                                    $customerQuery->where('full_name', 'like', "%{$q}%")
                                        ->orWhere('phone', 'like', "%{$q}%")
                                        ->orWhere('email', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->when(! empty($filters['status']), fn ($ticketQuery) => $ticketQuery->where('status', $filters['status']))
            ->when(! empty($filters['booking_status']), function ($ticketQuery) use ($filters) {
                $ticketQuery->whereHas('bookingTicket.booking', fn ($bookingQuery) => $bookingQuery->where('status', $filters['booking_status']));
            })
            ->when(! empty($filters['movie_id']), function ($ticketQuery) use ($filters) {
                $ticketQuery->whereHas('bookingTicket.booking.show.movieVersion', fn ($movieVersionQuery) => $movieVersionQuery->where('movie_id', $filters['movie_id']));
            })
            ->when(! empty($filters['show_id']), function ($ticketQuery) use ($filters) {
                $ticketQuery->whereHas('bookingTicket.booking', fn ($bookingQuery) => $bookingQuery->where('show_id', $filters['show_id']));
            })
            ->when(! empty($filters['date_from']), function ($ticketQuery) use ($filters) {
                $ticketQuery->whereHas('bookingTicket.booking.show', fn ($showQuery) => $showQuery->whereDate('start_time', '>=', $filters['date_from']));
            })
            ->when(! empty($filters['date_to']), function ($ticketQuery) use ($filters) {
                $ticketQuery->whereHas('bookingTicket.booking.show', fn ($showQuery) => $showQuery->whereDate('start_time', '<=', $filters['date_to']));
            });

        $summaryQuery = clone $query;

        $summary = [
            'tickets' => (clone $summaryQuery)->count(),
            'issued' => (clone $summaryQuery)->where('status', 'ISSUED')->count(),
            'used' => (clone $summaryQuery)->where('status', 'USED')->count(),
            'invalid' => (clone $summaryQuery)->whereIn('status', ['VOID', 'REFUNDED'])->count(),
        ];

        $tickets = $query
            ->orderByRaw("CASE WHEN status = 'ISSUED' THEN 0 WHEN status = 'USED' THEN 1 ELSE 2 END")
            ->orderByDesc('issued_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $movies = Movie::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        $shows = Show::query()
            ->with(['movieVersion.movie', 'auditorium'])
            ->when(! empty($filters['movie_id']), fn ($showQuery) => $showQuery->whereHas('movieVersion', fn ($movieVersionQuery) => $movieVersionQuery->where('movie_id', $filters['movie_id'])))
            ->orderByDesc('start_time')
            ->limit(200)
            ->get();

        if (! empty($filters['show_id']) && ! $shows->contains('id', (int) $filters['show_id'])) {
            $selectedShow = Show::query()
                ->with(['movieVersion.movie', 'auditorium'])
                ->find($filters['show_id']);

            if ($selectedShow) {
                $shows->prepend($selectedShow);
            }
        }

        return view('admin.tickets.index', [
            'tickets' => $tickets,
            'summary' => $summary,
            'movies' => $movies,
            'shows' => $shows,
            'statusOptions' => self::STATUS_OPTIONS,
            'bookingStatusOptions' => self::BOOKING_STATUS_OPTIONS,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'booking_status' => $filters['booking_status'] ?? '',
                'movie_id' => isset($filters['movie_id']) ? (int) $filters['movie_id'] : null,
                'show_id' => isset($filters['show_id']) ? (int) $filters['show_id'] : null,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
        ]);
    }

    public function show(Ticket $ticket): View
    {
        $ticket->load([
            'bookingTicket.seat',
            'bookingTicket.ticketType',
            'bookingTicket.seatType',
            'bookingTicket.booking.customer',
            'bookingTicket.booking.show.movieVersion.movie',
            'bookingTicket.booking.show.auditorium.cinema',
        ]);

        $booking = $ticket->bookingTicket?->booking;
        $bookingTickets = collect();
        if ($booking) {
            $bookingTickets = Ticket::query()
                ->with('bookingTicket.seat')
                ->whereHas('bookingTicket', fn ($bookingTicketQuery) => $bookingTicketQuery->where('booking_id', $booking->id))
                ->orderBy('id')
                ->get();
        }

        $metrics = [
            'booking_ticket_count' => $bookingTickets->count(),
            'used_count' => $bookingTickets->where('status', 'USED')->count(),
            'issued_count' => $bookingTickets->where('status', 'ISSUED')->count(),
        ];

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'booking' => $booking,
            'bookingTickets' => $bookingTickets,
            'metrics' => $metrics,
            'statusOptions' => self::STATUS_OPTIONS,
            'bookingStatusOptions' => self::BOOKING_STATUS_OPTIONS,
        ]);
    }

    public function quickCheckIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ticket_code' => ['required', 'string', 'max:64'],
        ]);

        $ticket = Ticket::query()->where('ticket_code', trim($data['ticket_code']))->first();

        if (! $ticket) {
            return back()->with('error', 'Không tìm thấy vé với mã đã nhập.');
        }

        return $this->performCheckIn($ticket);
    }

    public function checkIn(Ticket $ticket): RedirectResponse
    {
        return $this->performCheckIn($ticket);
    }

    public function reopen(Ticket $ticket): RedirectResponse
    {
        try {
            DB::transaction(function () use ($ticket) {
                /** @var Ticket $lockedTicket */
                $lockedTicket = Ticket::query()
                    ->with('bookingTicket.booking')
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                if ($lockedTicket->status !== 'USED') {
                    abort(422, 'Chỉ có thể mở lại vé đã check-in.');
                }

                if ((string) $lockedTicket->bookingTicket?->status !== 'ISSUED') {
                    abort(422, 'Chỉ có thể mở lại vé đang còn hiệu lực nghiệp vụ.');
                }

                $lockedTicket->update([
                    'status' => 'ISSUED',
                    'used_at' => null,
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã mở lại vé để kiểm vé lại.');
    }

    private function performCheckIn(Ticket $ticket): RedirectResponse
    {
        try {
            DB::transaction(function () use ($ticket) {
                /** @var Ticket $lockedTicket */
                $lockedTicket = Ticket::query()
                    ->with([
                        'bookingTicket.booking.customer',
                        'bookingTicket.booking.show.movieVersion.movie',
                        'bookingTicket.booking.show.auditorium',
                        'bookingTicket.seat',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $booking = $lockedTicket->bookingTicket?->booking;

                if (! $booking) {
                    abort(422, 'Vé này chưa gắn booking hợp lệ.');
                }

                if ($lockedTicket->status === 'USED') {
                    abort(422, 'Vé này đã được check-in trước đó.');
                }

                if (in_array($lockedTicket->status, ['VOID', 'REFUNDED'], true)) {
                    abort(422, 'Vé này không còn hiệu lực để check-in.');
                }

                if ((string) $lockedTicket->bookingTicket?->status !== 'ISSUED') {
                    abort(422, 'Vé nghiệp vụ chưa ở trạng thái phát hành để check-in.');
                }

                if (! in_array((string) $booking->status, self::CHECKIN_ALLOWED_BOOKING_STATUSES, true)) {
                    abort(422, 'Booking hiện chưa ở trạng thái cho phép check-in.');
                }

                $lockedTicket->update([
                    'status' => 'USED',
                    'used_at' => now(),
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Check-in vé thành công.');
    }
}
