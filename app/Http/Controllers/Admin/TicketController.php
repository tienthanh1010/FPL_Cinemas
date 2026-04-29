<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Movie;
use App\Models\Promotion;
use App\Models\SeatBlock;
use App\Models\Show;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    private const STATUS_OPTIONS = [
        'ISSUED' => 'Chưa check-in',
        'USED' => 'Đã check-in',
        'PRINTED' => 'Đã in vé',
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
            'bookingTicket.booking.payments',
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
            'used' => (clone $summaryQuery)->whereIn('status', ['USED', 'PRINTED'])->count(),
            'printed' => (clone $summaryQuery)->where('status', 'PRINTED')->count(),
            'invalid' => (clone $summaryQuery)->whereIn('status', ['VOID', 'REFUNDED'])->count(),
        ];

        $tickets = $query
            ->orderByRaw("CASE WHEN status = 'ISSUED' THEN 0 WHEN status = 'USED' THEN 1 WHEN status = 'PRINTED' THEN 2 ELSE 3 END")
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
            'bookingTicket.booking.payments',
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
            'used_count' => $bookingTickets->whereIn('status', ['USED', 'PRINTED'])->count(),
            'issued_count' => $bookingTickets->where('status', 'ISSUED')->count(),
            'printed_count' => $bookingTickets->where('status', 'PRINTED')->count(),
        ];

        $compensationLog = null;
        $compensationMeta = null;
        if ($ticket->bookingTicket) {
            $compensationLog = DB::table('audit_logs')
                ->where('action', 'TICKET_COMPENSATION_VOUCHER')
                ->where('entity_type', 'booking_tickets')
                ->where('entity_id', $ticket->bookingTicket->id)
                ->latest('id')
                ->first();

            $compensationMeta = $compensationLog && ! empty($compensationLog->meta)
                ? json_decode((string) $compensationLog->meta, true)
                : null;
        }

        return view('admin.tickets.show', [
            'ticket' => $ticket,
            'booking' => $booking,
            'bookingTickets' => $bookingTickets,
            'metrics' => $metrics,
            'statusOptions' => self::STATUS_OPTIONS,
            'bookingStatusOptions' => self::BOOKING_STATUS_OPTIONS,
            'compensationLog' => $compensationLog,
            'compensationMeta' => $compensationMeta,
        ]);
    }

    public function quickCheckIn(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ticket_code' => ['required', 'string', 'max:10000'],
        ]);

        $ticket = $this->resolveTicketFromScannerInput(trim($data['ticket_code']));

        if (! $ticket) {
            return back()->with('error', 'Không tìm thấy vé với dữ liệu đã nhập / quét.');
        }

        return $this->performCheckIn($ticket);
    }

    public function checkIn(Ticket $ticket): RedirectResponse
    {
        return $this->performCheckIn($ticket);
    }

    public function print(Ticket $ticket): View|RedirectResponse
    {
        try {
            $booking = DB::transaction(function () use ($ticket) {
                /** @var Ticket $lockedTicket */
                $lockedTicket = Ticket::query()
                    ->with([
                        'bookingTicket.seat',
                        'bookingTicket.ticket',
                        'bookingTicket.ticketType',
                        'bookingTicket.seatType',
                        'bookingTicket.booking.customer',
            'bookingTicket.booking.payments',
                        'bookingTicket.booking.payments',
                        'bookingTicket.booking.show.movieVersion.movie.contentRating',
                        'bookingTicket.booking.show.auditorium.cinema',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $bookingTicket = $lockedTicket->bookingTicket;
                $booking = $bookingTicket?->booking;

                if (! $bookingTicket || ! $booking) {
                    abort(422, 'Vé này chưa liên kết booking hợp lệ để in.');
                }

                if (! in_array((string) $lockedTicket->status, ['USED', 'PRINTED'], true)) {
                    abort(422, 'Chỉ vé đã check-in mới có thể in vé cứng tại quầy.');
                }

                if ($lockedTicket->status === 'USED') {
                    $lockedTicket->update([
                        'status' => 'PRINTED',
                        'printed_at' => now(),
                    ]);

                    DB::table('audit_logs')->insert([
                        'actor_type' => 'admin_user',
                        'actor_id' => (int) request()->session()->get('admin_user_id', 0) ?: null,
                        'action' => 'TICKET_PRINTED',
                        'entity_type' => 'tickets',
                        'entity_id' => $lockedTicket->id,
                        'ip_address' => request()->ip(),
                        'user_agent' => substr((string) request()->userAgent(), 0, 255),
                        'meta' => json_encode([
                            'ticket_code' => $lockedTicket->ticket_code,
                            'booking_code' => $booking->booking_code,
                            'printed_at' => now()->toDateTimeString(),
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => now(),
                    ]);
                }

                $printableTickets = collect([$bookingTicket])->filter(function ($item) {
                    return $item->ticket && $item->ticket->ticket_code;
                })->values();

                if ($printableTickets->isEmpty()) {
                    abort(404, 'Vé này chưa có mã điện tử để in.');
                }

                $booking->setRelation('tickets', $printableTickets);

                return $booking;
            }, 3);
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.tickets.show', $ticket)
                ->with('error', $e->getMessage());
        }

        $lastSuccessfulPayment = $booking->payments
            ->filter(fn ($payment) => in_array(strtoupper((string) $payment->status), ['CAPTURED', 'REFUNDED'], true))
            ->sortByDesc(fn ($payment) => optional($payment->paid_at)->getTimestamp() ?: 0)
            ->first();

        return view('frontend.print_ticket', [
            'booking' => $booking,
            'printedAt' => $ticket->fresh()->printed_at ?: now(),
            'lastSuccessfulPayment' => $lastSuccessfulPayment,
        ]);
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

                if (! in_array($lockedTicket->status, ['USED', 'PRINTED'], true)) {
                    abort(422, 'Chỉ có thể mở lại vé đã check-in hoặc đã in.');
                }

                if ((string) $lockedTicket->bookingTicket?->status !== 'ISSUED') {
                    abort(422, 'Chỉ có thể mở lại vé đang còn hiệu lực nghiệp vụ.');
                }

                $lockedTicket->update([
                    'status' => 'ISSUED',
                    'used_at' => null,
                    'printed_at' => null,
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã mở lại vé để kiểm vé lại.');
    }

    public function compensate(Ticket $ticket): RedirectResponse
    {
        $voucherCode = null;
        $voucherAmount = 0;

        try {
            DB::transaction(function () use ($ticket, &$voucherCode, &$voucherAmount) {
                /** @var Ticket $lockedTicket */
                $lockedTicket = Ticket::query()
                    ->with([
                        'bookingTicket.booking.customer',
            'bookingTicket.booking.payments',
                        'bookingTicket.booking.show.auditorium.cinema',
                        'bookingTicket.ticketType',
                        'bookingTicket.seat',
                    ])
                    ->lockForUpdate()
                    ->findOrFail($ticket->id);

                $bookingTicket = $lockedTicket->bookingTicket;
                $booking = $bookingTicket?->booking;

                if (! $bookingTicket || ! $booking) {
                    abort(422, 'Vé này chưa liên kết booking hợp lệ để hoàn ticket.');
                }

                if (! in_array((string) $lockedTicket->status, ['ISSUED', 'USED', 'PRINTED'], true)) {
                    abort(422, 'Chỉ vé đã phát hành hoặc đã check-in mới có thể hoàn ticket.');
                }

                if (! in_array((string) $booking->status, self::CHECKIN_ALLOWED_BOOKING_STATUSES, true) || (int) $booking->paid_amount <= 0) {
                    abort(422, 'Chỉ có thể hoàn ticket cho booking đã thanh toán hợp lệ.');
                }

                $hasSuccessfulPayment = $booking->payments->contains(function ($payment) {
                    return in_array(strtoupper((string) $payment->status), ['CAPTURED', 'REFUNDED'], true);
                });

                if (! $hasSuccessfulPayment) {
                    abort(422, 'Không tìm thấy giao dịch thanh toán hợp lệ để hoàn ticket.');
                }

                $show = $booking->show;
                $seat = $bookingTicket->seat;
                $hasSeatBlock = $show && $seat
                    ? SeatBlock::query()
                        ->where('auditorium_id', (int) $show->auditorium_id)
                        ->where('seat_id', (int) $seat->id)
                        ->where('start_at', '<', $show->end_time)
                        ->where('end_at', '>', $show->start_time)
                        ->exists()
                    : false;

                if (! $hasSeatBlock) {
                    abort(422, 'Chỉ có thể hoàn ticket cho đúng vé nằm trên ghế đang bị khoá trong chính suất chiếu này.');
                }

                $alreadyCompensated = DB::table('audit_logs')
                    ->where('action', 'TICKET_COMPENSATION_VOUCHER')
                    ->where('entity_type', 'booking_tickets')
                    ->where('entity_id', $bookingTicket->id)
                    ->exists();

                if ($alreadyCompensated) {
                    abort(422, 'Vé này đã được hoàn ticket trước đó.');
                }

                $voucherAmount = (int) $bookingTicket->final_price_amount;
                if ($voucherAmount <= 0) {
                    abort(422, 'Không thể tạo voucher cho vé có giá trị bằng 0.');
                }

                $promotionCode = 'COMP_TICKET_CREDIT_' . $voucherAmount;
                $promotion = Promotion::query()->firstOrCreate(
                    ['code' => $promotionCode],
                    [
                        'name' => 'Hoàn ticket vé hỏng ' . number_format($voucherAmount) . 'đ',
                        'description' => 'Ticket credit do ghế gặp sự cố kỹ thuật.',
                        'promo_type' => 'FIXED',
                        'discount_value' => $voucherAmount,
                        'max_discount_amount' => $voucherAmount,
                        'min_order_amount' => 0,
                        'applies_to' => 'ORDER',
                        'is_stackable' => 0,
                        'start_at' => now()->subMinute(),
                        'end_at' => now()->addMonths(6),
                        'usage_limit_total' => null,
                        'usage_limit_per_customer' => 1,
                        'status' => 'ACTIVE',
                        'day_of_week' => null,
                        'show_start_from' => null,
                        'show_start_to' => null,
                        'customer_scope' => 'ALL',
                        'auto_apply' => 0,
                        'coupon_required' => 1,
                    ]
                );

                $voucherCode = 'HOANTICKET' . strtoupper(Str::random(8));
                $coupon = Coupon::query()->create([
                    'promotion_id' => $promotion->id,
                    'code' => $voucherCode,
                    'customer_id' => $booking->customer_id,
                    'status' => 'ISSUED',
                    'issued_at' => now(),
                    'expires_at' => now()->addMonths(6),
                ]);

                $lockedTicket->update([
                    'status' => 'REFUNDED',
                    'used_at' => null,
                ]);

                $bookingTicket->update([
                    'status' => 'CANCELLED',
                ]);

                DB::table('audit_logs')->insert([
                    'actor_type' => 'admin_user',
                    'actor_id' => (int) request()->session()->get('admin_user_id', 0) ?: null,
                    'action' => 'TICKET_COMPENSATION_VOUCHER',
                    'entity_type' => 'booking_tickets',
                    'entity_id' => $bookingTicket->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => substr((string) request()->userAgent(), 0, 255),
                    'meta' => json_encode([
                        'coupon_id' => $coupon->id,
                        'coupon_code' => $coupon->code,
                        'promotion_id' => $promotion->id,
                        'amount' => $voucherAmount,
                        'ticket_code' => $lockedTicket->ticket_code,
                        'booking_code' => $booking->booking_code,
                        'reason' => 'TICKET_CREDIT_FOR_DAMAGED_SEAT',
                    ], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã tạo hoàn ticket ' . $voucherCode . ' trị giá ' . number_format($voucherAmount) . 'đ cho khách dưới dạng ticket credit.');
    }

    private function performCheckIn(Ticket $ticket): RedirectResponse
    {
        try {
            DB::transaction(function () use ($ticket) {
                /** @var Ticket $lockedTicket */
                $lockedTicket = Ticket::query()
                    ->with([
                        'bookingTicket.booking.customer',
            'bookingTicket.booking.payments',
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

                if (in_array($lockedTicket->status, ['USED', 'PRINTED'], true)) {
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
                    'printed_at' => null,
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.tickets.show', $ticket)
            ->with('success', 'Check-in vé thành công.');
    }

    private function resolveTicketFromScannerInput(string $rawInput): ?Ticket
    {
        $rawInput = trim($rawInput);
        if ($rawInput === '') {
            return null;
        }

        if ($ticket = Ticket::query()->where('ticket_code', $rawInput)->first()) {
            return $ticket;
        }

        if (Str::startsWith($rawInput, '{') && Str::endsWith($rawInput, '}')) {
            $payload = json_decode($rawInput, true);
            if (is_array($payload) && ! empty($payload['ticket_code'])) {
                return Ticket::query()->where('ticket_code', trim((string) $payload['ticket_code']))->first();
            }
        }

        if (str_contains($rawInput, 'FPLTICKET|')) {
            $parts = explode('|', $rawInput);
            $candidate = trim((string) ($parts[1] ?? ''));
            if ($candidate !== '') {
                return Ticket::query()->where('ticket_code', $candidate)->first();
            }
        }

        if (filter_var($rawInput, FILTER_VALIDATE_URL)) {
            $queryString = parse_url($rawInput, PHP_URL_QUERY);
            if ($queryString) {
                parse_str($queryString, $queryParams);
                $ticketCode = trim((string) ($queryParams['ticket_code'] ?? $queryParams['code'] ?? ''));
                if ($ticketCode !== '') {
                    return Ticket::query()->where('ticket_code', $ticketCode)->first();
                }
            }
        }

        return Ticket::query()->where('qr_payload', $rawInput)->first();
    }
}
