<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Movie;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Show;
use App\Services\TicketLifecycleService;
use App\Services\LoyaltyPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    private const STATUS_OPTIONS = [
        'INITIATED' => 'Khởi tạo',
        'AUTHORIZED' => 'Đã uỷ quyền',
        'CAPTURED' => 'Đã thu tiền',
        'FAILED' => 'Thất bại',
        'CANCELLED' => 'Đã huỷ',
        'REFUNDED' => 'Đã hoàn tiền',
    ];

    private const BOOKING_TERMINAL_STATUSES = ['CANCELLED', 'EXPIRED'];

    public function __construct(private readonly TicketLifecycleService $ticketLifecycleService,
        private readonly LoyaltyPointService $loyaltyPointService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'provider' => ['nullable', 'string', 'max:32'],
            'method' => ['nullable', 'string', 'max:32'],
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'show_id' => ['nullable', 'integer', 'exists:shows,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Payment::query()
            ->with([
                'booking.customer',
                'booking.show.movieVersion.movie',
                'booking.show.auditorium',
                'refunds',
            ])
            ->when(! empty($filters['q']), function ($paymentQuery) use ($filters) {
                $q = trim((string) $filters['q']);

                $paymentQuery->where(function ($subQuery) use ($q) {
                    $subQuery->where('external_txn_ref', 'like', "%{$q}%")
                        ->orWhere('provider', 'like', "%{$q}%")
                        ->orWhere('method', 'like', "%{$q}%")
                        ->orWhereHas('booking', function ($bookingQuery) use ($q) {
                            $bookingQuery->where('booking_code', 'like', "%{$q}%")
                                ->orWhere('contact_phone', 'like', "%{$q}%")
                                ->orWhere('contact_email', 'like', "%{$q}%")
                                ->orWhere('contact_name', 'like', "%{$q}%")
                                ->orWhereHas('customer', function ($customerQuery) use ($q) {
                                    $customerQuery->where('full_name', 'like', "%{$q}%")
                                        ->orWhere('phone', 'like', "%{$q}%")
                                        ->orWhere('email', 'like', "%{$q}%");
                                });
                        });
                });
            })
            ->when(! empty($filters['status']), fn ($paymentQuery) => $paymentQuery->where('status', $filters['status']))
            ->when(! empty($filters['provider']), fn ($paymentQuery) => $paymentQuery->where('provider', $filters['provider']))
            ->when(! empty($filters['method']), fn ($paymentQuery) => $paymentQuery->where('method', $filters['method']))
            ->when(! empty($filters['movie_id']), function ($paymentQuery) use ($filters) {
                $paymentQuery->whereHas('booking.show.movieVersion', fn ($movieVersionQuery) => $movieVersionQuery->where('movie_id', $filters['movie_id']));
            })
            ->when(! empty($filters['show_id']), function ($paymentQuery) use ($filters) {
                $paymentQuery->whereHas('booking', fn ($bookingQuery) => $bookingQuery->where('show_id', $filters['show_id']));
            })
            ->when(! empty($filters['date_from']), fn ($paymentQuery) => $paymentQuery->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($paymentQuery) => $paymentQuery->whereDate('created_at', '<=', $filters['date_to']));

        $summaryQuery = clone $query;

        $summary = [
            'payments' => (clone $summaryQuery)->count(),
            'captured_amount' => (clone $summaryQuery)->where('status', 'CAPTURED')->sum('amount'),
            'refund_amount' => Refund::query()
                ->where('status', 'SUCCESS')
                ->whereIn('payment_id', (clone $summaryQuery)->select('payments.id'))
                ->sum('amount'),
            'initiated_count' => (clone $summaryQuery)->whereIn('status', ['INITIATED', 'AUTHORIZED'])->count(),
            'failed_count' => (clone $summaryQuery)->where('status', 'FAILED')->count(),
        ];

        $payments = $query
            ->orderByDesc(DB::raw('COALESCE(paid_at, created_at)'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $providers = Payment::query()
            ->select('provider')
            ->whereNotNull('provider')
            ->distinct()
            ->orderBy('provider')
            ->pluck('provider');

        $methods = Payment::query()
            ->select('method')
            ->whereNotNull('method')
            ->distinct()
            ->orderBy('method')
            ->pluck('method');

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

        return view('admin.payments.index', [
            'payments' => $payments,
            'summary' => $summary,
            'providers' => $providers,
            'methods' => $methods,
            'movies' => $movies,
            'shows' => $shows,
            'statusOptions' => self::STATUS_OPTIONS,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'provider' => $filters['provider'] ?? '',
                'method' => $filters['method'] ?? '',
                'movie_id' => isset($filters['movie_id']) ? (int) $filters['movie_id'] : null,
                'show_id' => isset($filters['show_id']) ? (int) $filters['show_id'] : null,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
        ]);
    }

    public function show(Payment $payment): View
    {
        $payment->load([
            'booking.customer',
            'booking.show.movieVersion.movie',
            'booking.show.auditorium.cinema',
            'booking.tickets.seat',
            'refunds',
        ]);

        $metrics = [
            'refund_count' => $payment->refunds->count(),
            'refund_success_amount' => (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount'),
            'net_amount' => $this->netCapturedAmount($payment),
        ];

        return view('admin.payments.show', [
            'payment' => $payment,
            'statusOptions' => self::STATUS_OPTIONS,
            'metrics' => $metrics,
        ]);
    }

    public function update(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'external_txn_ref' => ['nullable', 'string', 'max:128'],
            'paid_at' => ['nullable', 'date'],
        ]);

        try {
            DB::transaction(function () use ($payment, $data) {
                /** @var Payment $lockedPayment */
                $lockedPayment = Payment::query()
                    ->with(['booking.payments.refunds', 'booking.tickets', 'refunds'])
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                $paidAt = ! empty($data['paid_at']) ? Carbon::parse($data['paid_at']) : null;

                if ($data['status'] === 'CAPTURED' && $paidAt === null) {
                    $paidAt = $lockedPayment->paid_at ?: now();
                }

                if (in_array($data['status'], ['FAILED', 'CANCELLED', 'INITIATED'], true) && empty($data['paid_at'])) {
                    $paidAt = null;
                }

                $lockedPayment->update([
                    'status' => $data['status'],
                    'external_txn_ref' => $data['external_txn_ref'] ?: null,
                    'paid_at' => $paidAt,
                ]);

                $this->syncBookingFromPayments($lockedPayment->booking->fresh(['payments.refunds', 'tickets']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật giao dịch thanh toán và đồng bộ booking liên quan.');
    }

    private function syncBookingFromPayments(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }

        $paidAmount = (int) $booking->payments->sum(fn (Payment $payment) => $this->netCapturedAmount($payment));
        $paidAmount = max(0, min((int) $booking->total_amount, $paidAmount));

        $successfulRefundAmount = (int) $booking->payments
            ->flatMap(fn (Payment $payment) => $payment->refunds)
            ->where('status', 'SUCCESS')
            ->sum('amount');

        $fullSuccessRefund = $successfulRefundAmount > 0 && $paidAmount <= 0;
        $currentStatus = (string) $booking->status;

        $payload = ['paid_amount' => $paidAmount];

        if (! in_array($currentStatus, self::BOOKING_TERMINAL_STATUSES, true)) {
            if ($fullSuccessRefund && $currentStatus !== 'COMPLETED') {
                $payload['status'] = 'CANCELLED';
            } else {
                $payload['status'] = $paidAmount > 0 ? 'PAID' : 'PENDING';
            }
        }

        $booking->update($payload);

        $nextBookingStatus = (string) ($payload['status'] ?? $currentStatus);

        if ($nextBookingStatus === 'CANCELLED') {
            $booking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => 'CANCELLED']);
        } elseif ($nextBookingStatus === 'EXPIRED') {
            $booking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => 'EXPIRED']);
        } else {
            $booking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => $paidAmount > 0 ? 'ISSUED' : 'RESERVED']);
        }

        $freshBooking = $booking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
        $this->ticketLifecycleService->syncForBooking($freshBooking);
        $this->loyaltyPointService->syncForBooking($freshBooking);
    }

    private function netCapturedAmount(Payment $payment): int
    {
        $refundSuccessAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');

        return match ((string) $payment->status) {
            'CAPTURED' => max(0, (int) $payment->amount - $refundSuccessAmount),
            'REFUNDED' => 0,
            default => 0,
        };
    }
}
