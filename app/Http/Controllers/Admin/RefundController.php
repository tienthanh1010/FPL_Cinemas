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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RefundController extends Controller
{
    private const STATUS_OPTIONS = [
        'PENDING' => 'Chờ xử lý',
        'SUCCESS' => 'Thành công',
        'FAILED' => 'Thất bại',
        'REJECTED' => 'Từ chối',
        'CANCELLED' => 'Đã huỷ',
    ];

    private const PAYMENT_TERMINAL_STATUSES = ['CANCELLED', 'FAILED', 'INITIATED'];
    private const BOOKING_TERMINAL_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const COMMITTED_REFUND_STATUSES = ['PENDING', 'SUCCESS'];

    public function __construct(private readonly TicketLifecycleService $ticketLifecycleService,
        private readonly LoyaltyPointService $loyaltyPointService)
    {
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'payment_status' => ['nullable', Rule::in(['INITIATED', 'AUTHORIZED', 'CAPTURED', 'FAILED', 'CANCELLED', 'REFUNDED'])],
            'provider' => ['nullable', 'string', 'max:32'],
            'method' => ['nullable', 'string', 'max:32'],
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'show_id' => ['nullable', 'integer', 'exists:shows,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Refund::query()
            ->with([
                'payment.booking.customer',
                'payment.booking.show.movieVersion.movie',
                'payment.booking.show.auditorium',
            ])
            ->when(! empty($filters['q']), function ($refundQuery) use ($filters) {
                $q = trim((string) $filters['q']);

                $refundQuery->where(function ($subQuery) use ($q) {
                    $subQuery->where('external_ref', 'like', "%{$q}%")
                        ->orWhere('reason', 'like', "%{$q}%")
                        ->orWhereHas('payment', function ($paymentQuery) use ($q) {
                            $paymentQuery->where('external_txn_ref', 'like', "%{$q}%")
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
                });
            })
            ->when(! empty($filters['status']), fn ($refundQuery) => $refundQuery->where('status', $filters['status']))
            ->when(! empty($filters['payment_status']), function ($refundQuery) use ($filters) {
                $refundQuery->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', $filters['payment_status']));
            })
            ->when(! empty($filters['provider']), function ($refundQuery) use ($filters) {
                $refundQuery->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('provider', $filters['provider']));
            })
            ->when(! empty($filters['method']), function ($refundQuery) use ($filters) {
                $refundQuery->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('method', $filters['method']));
            })
            ->when(! empty($filters['movie_id']), function ($refundQuery) use ($filters) {
                $refundQuery->whereHas('payment.booking.show.movieVersion', fn ($movieVersionQuery) => $movieVersionQuery->where('movie_id', $filters['movie_id']));
            })
            ->when(! empty($filters['show_id']), function ($refundQuery) use ($filters) {
                $refundQuery->whereHas('payment.booking', fn ($bookingQuery) => $bookingQuery->where('show_id', $filters['show_id']));
            })
            ->when(! empty($filters['date_from']), fn ($refundQuery) => $refundQuery->whereDate('created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn ($refundQuery) => $refundQuery->whereDate('created_at', '<=', $filters['date_to']));

        $summaryQuery = clone $query;

        $summary = [
            'refunds' => (clone $summaryQuery)->count(),
            'success_amount' => (clone $summaryQuery)->where('status', 'SUCCESS')->sum('amount'),
            'pending_amount' => (clone $summaryQuery)->where('status', 'PENDING')->sum('amount'),
            'rejected_count' => (clone $summaryQuery)->whereIn('status', ['FAILED', 'REJECTED', 'CANCELLED'])->count(),
        ];

        $refunds = $query
            ->orderByDesc('created_at')
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

        $movies = Movie::query()->orderBy('title')->get(['id', 'title']);

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

        return view('admin.refunds.index', [
            'refunds' => $refunds,
            'summary' => $summary,
            'providers' => $providers,
            'methods' => $methods,
            'movies' => $movies,
            'shows' => $shows,
            'statusOptions' => self::STATUS_OPTIONS,
            'paymentStatusOptions' => [
                'INITIATED' => 'Khởi tạo',
                'AUTHORIZED' => 'Đã uỷ quyền',
                'CAPTURED' => 'Đã thu tiền',
                'FAILED' => 'Thất bại',
                'CANCELLED' => 'Đã huỷ',
                'REFUNDED' => 'Đã hoàn tiền',
            ],
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'payment_status' => $filters['payment_status'] ?? '',
                'provider' => $filters['provider'] ?? '',
                'method' => $filters['method'] ?? '',
                'movie_id' => isset($filters['movie_id']) ? (int) $filters['movie_id'] : null,
                'show_id' => isset($filters['show_id']) ? (int) $filters['show_id'] : null,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
        ]);
    }

    public function show(Refund $refund): View
    {
        $refund->load([
            'payment.booking.customer',
            'payment.booking.show.movieVersion.movie',
            'payment.booking.show.auditorium.cinema',
            'payment.booking.tickets.seat',
            'payment.refunds',
        ]);

        $payment = $refund->payment;
        $successAmount = $payment
            ? (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount')
            : 0;

        $metrics = [
            'payment_amount' => (int) ($payment?->amount ?? 0),
            'success_amount' => $successAmount,
            'remaining_amount' => max(0, (int) ($payment?->amount ?? 0) - $successAmount),
            'refund_count' => (int) ($payment?->refunds->count() ?? 0),
        ];

        return view('admin.refunds.show', [
            'refund' => $refund,
            'statusOptions' => self::STATUS_OPTIONS,
            'metrics' => $metrics,
        ]);
    }

    public function store(Request $request, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'reason' => ['nullable', 'string', 'max:255'],
            'external_ref' => ['nullable', 'string', 'max:128'],
        ]);

        try {
            DB::transaction(function () use ($payment, $data) {
                /** @var Payment $lockedPayment */
                $lockedPayment = Payment::query()
                    ->with(['booking.payments.refunds', 'booking.tickets', 'refunds'])
                    ->lockForUpdate()
                    ->findOrFail($payment->id);

                $this->ensureRefundablePayment($lockedPayment);
                $this->ensureRefundAmountWithinLimit($lockedPayment, (int) $data['amount']);

                Refund::create([
                    'payment_id' => $lockedPayment->id,
                    'amount' => (int) $data['amount'],
                    'status' => $data['status'],
                    'reason' => $data['reason'] ?: null,
                    'external_ref' => $data['external_ref'] ?: null,
                ]);

                $lockedPayment->refresh();
                $lockedPayment->load(['booking.payments.refunds', 'booking.tickets', 'refunds']);

                $this->syncPaymentStatusFromRefunds($lockedPayment);
                $this->syncBookingFromPayments($lockedPayment->booking?->fresh(['payments.refunds', 'tickets']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', 'Đã tạo yêu cầu hoàn tiền cho giao dịch.');
    }

    public function update(Request $request, Refund $refund): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'reason' => ['nullable', 'string', 'max:255'],
            'external_ref' => ['nullable', 'string', 'max:128'],
        ]);

        try {
            DB::transaction(function () use ($refund, $data) {
                /** @var Refund $lockedRefund */
                $lockedRefund = Refund::query()
                    ->with(['payment.booking.payments.refunds', 'payment.booking.tickets', 'payment.refunds'])
                    ->lockForUpdate()
                    ->findOrFail($refund->id);

                $lockedPayment = $lockedRefund->payment;
                if (! $lockedPayment) {
                    abort(422, 'Không tìm thấy giao dịch thanh toán liên quan để xử lý refund.');
                }

                $this->ensureRefundablePayment($lockedPayment, $lockedRefund);
                $this->ensureRefundAmountWithinLimit($lockedPayment, (int) $data['amount'], $lockedRefund);

                $lockedRefund->update([
                    'amount' => (int) $data['amount'],
                    'status' => $data['status'],
                    'reason' => $data['reason'] ?: null,
                    'external_ref' => $data['external_ref'] ?: null,
                ]);

                $lockedPayment->refresh();
                $lockedPayment->load(['booking.payments.refunds', 'booking.tickets', 'refunds']);

                $this->syncPaymentStatusFromRefunds($lockedPayment);
                $this->syncBookingFromPayments($lockedPayment->booking?->fresh(['payments.refunds', 'tickets']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return back()->with('success', 'Đã cập nhật yêu cầu hoàn tiền và đồng bộ giao dịch liên quan.');
    }

    private function ensureRefundablePayment(Payment $payment, ?Refund $refund = null): void
    {
        if (in_array((string) $payment->status, self::PAYMENT_TERMINAL_STATUSES, true)) {
            abort(422, 'Chỉ có thể hoàn tiền cho giao dịch đã thu tiền.');
        }

        if (! in_array((string) $payment->status, ['CAPTURED', 'REFUNDED'], true)) {
            abort(422, 'Giao dịch này chưa ở trạng thái có thể hoàn tiền.');
        }

        $remainingAmount = $this->availableRefundableAmount($payment, $refund);
        if ($remainingAmount <= 0) {
            abort(422, 'Giao dịch này không còn số dư nào để hoàn tiền tiếp.');
        }
    }

    private function ensureRefundAmountWithinLimit(Payment $payment, int $amount, ?Refund $currentRefund = null): void
    {
        $remainingAmount = $this->availableRefundableAmount($payment, $currentRefund);

        if ($amount > (int) $payment->amount) {
            abort(422, 'Số tiền hoàn không được vượt quá giá trị giao dịch gốc.');
        }

        if ($amount > $remainingAmount) {
            abort(422, 'Số tiền hoàn vượt quá phần còn có thể hoàn của giao dịch này.');
        }
    }

    private function availableRefundableAmount(Payment $payment, ?Refund $currentRefund = null): int
    {
        $committedAmount = (int) $payment->refunds
            ->whereIn('status', self::COMMITTED_REFUND_STATUSES)
            ->reject(fn (Refund $refund) => $currentRefund && $refund->id === $currentRefund->id)
            ->sum('amount');

        return max(0, (int) $payment->amount - $committedAmount);
    }

    private function syncPaymentStatusFromRefunds(Payment $payment): void
    {
        $successAmount = (int) $payment->refunds->where('status', 'SUCCESS')->sum('amount');

        $targetStatus = $successAmount >= (int) $payment->amount ? 'REFUNDED' : 'CAPTURED';

        if ($payment->status !== $targetStatus) {
            $payment->update(['status' => $targetStatus]);
        }
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
<<<<<<< HEAD
                ->update(['status' => 'CANCELLED']);
=======
                ->update(['status' => 'EXPIRED']);
>>>>>>> 19e5bc83fca8bd5ee3fc2623868f2c32ac80f112
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
