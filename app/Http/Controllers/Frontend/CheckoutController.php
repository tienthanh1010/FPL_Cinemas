<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SoftTicketIssuedMail;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Coupon;
use App\Models\InventoryBalance;
use App\Models\Payment;
use App\Models\StockLocation;
use App\Models\StockMovement;
use App\Models\Show;
use App\Services\LoyaltyPointService;
use App\Services\TicketLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PROVIDER_OPTIONS = [
        'MOMO' => ['label' => 'Ví MoMo', 'method' => 'EWALLET', 'description' => 'Thanh toán nhanh bằng ví điện tử MoMo.'],
        'ZALOPAY' => ['label' => 'ZaloPay', 'method' => 'EWALLET', 'description' => 'Thanh toán ngay qua ví ZaloPay.'],
        'VNPAY' => ['label' => 'VNPay', 'method' => 'BANK_TRANSFER', 'description' => 'Thanh toán qua VNPay / ngân hàng nội địa.'],
        'CARD' => ['label' => 'Thẻ ngân hàng', 'method' => 'CARD', 'description' => 'Mô phỏng thanh toán thẻ nội địa / quốc tế.'],
        'MOMO' => [
            'label' => 'Ví MoMo',
            'method' => 'EWALLET',
            'description' => 'Thanh toán online qua cổng MoMo.',
            'accent' => '#ae2070',
            'short' => 'MoMo',
        ],
        'VNPAY' => [
            'label' => 'VNPay',
            'method' => 'BANK_TRANSFER',
            'description' => 'Thanh toán online qua cổng VNPay.',
            'accent' => '#0066b3',
            'short' => 'VNP',
        ],

    ];

    private const TERMINAL_BOOKING_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const PAID_LIKE_BOOKING_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];
    private const GATEWAY_PAYMENT_STATUSES = ['INITIATED', 'WAITING_TRANSFER'];
    private const FAILED_PAYMENT_STATUSES = ['FAILED', 'CANCELLED'];

    public function __construct(
        private readonly TicketLifecycleService $ticketLifecycleService,
        private readonly LoyaltyPointService $loyaltyPointService,
    ) {
    }

    public function show(string $booking_code): View|RedirectResponse
    {
        $booking = $this->findBooking($booking_code);
        $booking = $this->bookingLifecycleService->expirePendingBooking($booking);

        if (in_array((string) $booking->status, self::TERMINAL_BOOKING_STATUSES, true)) {
            return redirect()->route('booking.lookup', ['booking_code' => $booking->booking_code])
                ->with('error', 'Booking đã hết hạn hoặc đã bị huỷ, không thể thanh toán trực tuyến.');
        }

        if ($booking->show && $booking->show->start_time && now()->gte($booking->show->start_time)) {
            return redirect()->route('booking.lookup', ['booking_code' => $booking->booking_code])
                ->with('error', 'Suất chiếu đã bắt đầu hoặc đã kết thúc, không thể thanh toán tiếp.');
        }

        if ((int) $booking->paid_amount >= (int) $booking->total_amount && in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            session()->forget('editable_booking_code');
            $this->ensureSoftTicketMail($booking);

            return redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                ->with('success', 'Booking đã được thanh toán trước đó.');
        }

        session(['editable_booking_code' => $booking->booking_code]);

        $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
        $estimatedPoints = $this->loyaltyPointService->previewPoints($amountDue);
        $pendingPayments = $booking->payments
            ->whereIn('status', array_merge(self::GATEWAY_PAYMENT_STATUSES, self::FAILED_PAYMENT_STATUSES))
            ->sortByDesc('created_at')
            ->values();

        return view('frontend.payment', [
            'booking' => $booking->fresh([
                'customer',
                'show.movieVersion.movie.contentRating',
                'show.auditorium.cinema',
                'tickets.seat',
                'tickets.ticketType',
                'tickets.seatType',
                'tickets.ticket',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ]),
            'amountDue' => $amountDue,
            'estimatedPoints' => $estimatedPoints,
            'providerOptions' => self::PROVIDER_OPTIONS,
            'pendingPayments' => $pendingPayments,
            'emailRecipient' => $this->resolveRecipient($booking),
        ]);
    }

    public function pay(Request $request, string $booking_code): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(self::PROVIDER_OPTIONS))],
        ]);

        try {
            [$paymentId, $bookingCode] = DB::transaction(function () use ($booking_code, $data) {
                $booking = Booking::query()
                    ->where('booking_code', $booking_code)
                    ->with([
                        'show.auditorium.cinema',
                        'tickets.ticket',
                        'discounts.coupon',
                        'payments.refunds',
                        'payments',
                    ])
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertBookingPayable($booking);

                $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
                if ($amountDue <= 0) {
                    abort(422, 'Booking này không còn số tiền nào cần thanh toán.');
                }

                $provider = (string) $data['provider'];
                $option = self::PROVIDER_OPTIONS[$provider];

                $existingPayment = $booking->payments
                    ->first(fn (Payment $payment) =>
                        (string) $payment->provider === $provider
                        && (int) $payment->amount === $amountDue
                        && in_array((string) $payment->status, self::GATEWAY_PAYMENT_STATUSES, true)
                    );

                if ($existingPayment) {
                    $requestPayload = array_merge((array) $existingPayment->request_payload, [
                        'gateway_opened_at' => now()->toIso8601String(),
                    ]);

                    $existingPayment->update([
                        'status' => 'WAITING_TRANSFER',
                        'request_payload' => $requestPayload,
                        'response_payload' => array_merge((array) $existingPayment->response_payload, [
                            'status' => 'WAITING_TRANSFER',
                            'message' => 'Giao dịch đang chờ xác nhận trên cổng ' . $option['label'] . '.',
                        ]),
                    ]);

                    return [$existingPayment->id, $booking->booking_code];
                }

                $externalRef = $provider . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
                $payment = Payment::create([
                    'booking_id' => $booking->id,
                    'provider' => $provider,
                    'method' => $option['method'],
                    'status' => 'WAITING_TRANSFER',
                    'amount' => $amountDue,
                    'currency' => $booking->currency ?: 'VND',
                    'external_txn_ref' => $externalRef,
                    'request_payload' => [
                        'flow' => 'HOSTED_GATEWAY_SIMULATION',
                        'provider' => $provider,
                        'method' => $option['method'],
                        'booking_code' => $booking->booking_code,
                        'customer_name' => $booking->contact_name,
                        'customer_phone' => $booking->contact_phone,
                        'customer_email' => $booking->contact_email,
                        'amount_due' => $amountDue,
                        'gateway_opened_at' => now()->toIso8601String(),
                        'expires_at' => optional($booking->expires_at)?->toIso8601String(),
                    ],
                    'response_payload' => [
                        'status' => 'WAITING_TRANSFER',
                        'message' => 'Đang chờ người dùng xác nhận thanh toán trên cổng ' . $option['label'] . '.',
                    ],
                ]);

                return [$payment->id, $booking->booking_code];
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.payment.gateway', ['booking_code' => $bookingCode, 'payment' => $paymentId]);
    }

    public function gateway(string $booking_code, Payment $payment): View|RedirectResponse
    {
        $booking = $this->findBooking($booking_code);
        if ((int) $payment->booking_id !== (int) $booking->id) {
            abort(404);
        }

        $booking = $this->bookingLifecycleService->expirePendingBooking($booking);

        if (! in_array((string) $payment->status, self::GATEWAY_PAYMENT_STATUSES, true)) {
            return redirect()->route('booking.payment', $booking_code)
                ->with('error', 'Giao dịch này không còn ở trạng thái chờ thanh toán.');
        }

        $provider = self::PROVIDER_OPTIONS[(string) $payment->provider] ?? null;
        if (! $provider) {
            abort(404);
        }

        return view('frontend.payment_gateway', [
            'booking' => $booking,
            'payment' => $payment,
            'provider' => $provider,
            'recipientEmail' => $this->resolveRecipient($booking),
        ]);
    }

    public function callback(Request $request, string $booking_code, Payment $payment): RedirectResponse
    {
        $data = $request->validate([
            'result' => ['required', Rule::in(['success', 'failed', 'cancel'])],
        ]);

        try {
            $result = DB::transaction(function () use ($booking_code, $payment, $data) {
                $booking = Booking::query()
                    ->where('booking_code', $booking_code)
                    ->with([
                        'customer',
                        'show.movieVersion.movie.contentRating',
                        'show.auditorium.cinema',
                        'tickets.seat',
                        'tickets.ticketType',
                        'tickets.seatType',
                        'tickets.ticket',
                        'discounts.promotion',
                        'discounts.coupon',
                        'payments.refunds',
                    ])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $payment->booking_id !== (int) $booking->id) {
                    abort(404);
                }

                $this->assertBookingPayable($booking, true);

                $payment->refresh();
                if (! in_array((string) $payment->status, self::GATEWAY_PAYMENT_STATUSES, true)) {
                    return [
                        'redirect' => redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                            ->with('success', 'Giao dịch đã được xử lý trước đó.'),
                        'send_mail' => false,
                        'booking_code' => $booking->booking_code,
                    ];
                }

                $providerLabel = $payment->provider === 'MOMO' ? 'MoMo' : 'VNPay';
                $result = (string) $data['result'];

                if ($result === 'success') {
                    $payment->update([
                        'status' => 'CAPTURED',
                        'paid_at' => now(),
                        'response_payload' => array_merge((array) $payment->response_payload, [
                            'status' => 'SUCCESS',
                            'message' => 'Thanh toán thành công qua cổng ' . $providerLabel . '.',
                            'paid_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    $this->bookingLifecycleService->syncBookingFromPayments($booking->fresh(['payments.refunds', 'tickets.ticket']));
                    session()->forget('editable_booking_code');

                    return [
                        'redirect' => redirect()->route('booking.success', ['booking_code' => $booking->booking_code]),
                        'send_mail' => true,
                        'booking_code' => $booking->booking_code,
                        'payment_id' => $payment->id,
                        'provider_label' => $providerLabel,
                    ];
                }

                $status = $result === 'cancel' ? 'CANCELLED' : 'FAILED';
                $message = $result === 'cancel'
                    ? 'Bạn đã huỷ giao dịch trên cổng thanh toán.'
                    : 'Thanh toán không thành công. Vui lòng thử lại.';

                $payment->update([
                    'status' => $status,
                    'response_payload' => array_merge((array) $payment->response_payload, [
                        'status' => $status,
                        'message' => $message,
                    ]),
                ]);

                return [
                    'redirect' => redirect()->route('booking.payment', ['booking_code' => $booking->booking_code])
                        ->with('error', $message),
                    'send_mail' => false,
                    'booking_code' => $booking->booking_code,
                ];
            }, 3);
        } catch (\Throwable $e) {
            return redirect()->route('booking.payment', ['booking_code' => $booking_code])
                ->with('error', $e->getMessage());
        }

        if (($result['send_mail'] ?? false) === true) {
            $booking = $this->findBooking((string) $result['booking_code']);
            $sendState = $this->sendSoftTicketMail($booking, (int) $result['payment_id']);
            $message = 'Thanh toán thành công qua ' . ($result['provider_label'] ?? 'cổng thanh toán') . '.';
            if ($sendState['sent']) {
                $message .= ' Vé bản mềm đã được gửi tới email của bạn.';
            } elseif ($sendState['recipient']) {
                $message .= ' Không gửi được email lúc này, bạn có thể bấm gửi lại vé trong trang chi tiết booking.';
            } else {
                $message .= ' Booking chưa có email nhận vé.';
            }

            return $result['redirect']->with('success', $message);
        }

        return $result['redirect'];
    }

    public function resendTicketMail(Request $request, string $booking_code): RedirectResponse
    {
        $booking = $this->findBooking($booking_code);
        $booking = $booking->fresh([
            'payments.refunds',
            'tickets.ticket',
            'customer',
            'show.movieVersion.movie.contentRating',
            'show.auditorium.cinema',
            'tickets.seat',
            'tickets.ticketType',
            'tickets.seatType',
        ]);

        if (! in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return back()->with('error', 'Chỉ booking đã thanh toán mới có thể gửi lại vé qua email.');
        }

        $payment = $booking->payments
            ->where('status', 'CAPTURED')
            ->sortByDesc('paid_at')
            ->first();

        if (! $payment) {
            return back()->with('error', 'Không tìm thấy giao dịch thanh toán hợp lệ để gửi vé.');
        }

        $sendState = $this->sendSoftTicketMail($booking, $payment->id, true);

        if (! $sendState['recipient']) {
            return back()->with('error', 'Booking chưa có email người nhận để gửi vé.');
        }

        if (! $sendState['sent']) {
            return back()->with('error', 'Không thể gửi email lúc này. Hãy kiểm tra cấu hình SMTP trong file .env rồi thử lại.');
        }

        return back()->with('success', 'Vé bản mềm đã được gửi lại tới ' . $sendState['recipient'] . '.');
    }

    private function findBooking(string $bookingCode): Booking
    {
        $booking = Booking::query()
            ->where('booking_code', $bookingCode)
            ->with([
                'customer',
                'show.movieVersion.movie',
                'show.auditorium.cinema',
                'tickets.seat',
                'tickets.ticketType',
                'tickets.seatType',
                'bookingProducts.product.category',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        $booking = $this->expireIfNeeded($booking);
        return $booking;
    }

    private function assertBookingPayable(Booking $booking, bool $allowGatewayPending = false): void
    {
        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

        $booking = $this->bookingLifecycleService->expirePendingBooking($booking);

        if (in_array((string) $booking->status, self::TERMINAL_BOOKING_STATUSES, true)) {
            abort(422, 'Booking đã hết hạn hoặc đã bị huỷ, không thể thanh toán tiếp.');
        }

        if ($booking->show && $booking->show->start_time && now()->gte($booking->show->start_time)) {
            abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc, không thể thanh toán.');
        }

        if (! $allowGatewayPending && (int) $booking->paid_amount >= (int) $booking->total_amount) {
            abort(422, 'Booking này không còn số tiền nào cần thanh toán.');
        }
    }

    private function ensureSoftTicketMail(Booking $booking): void
    {
        if (! in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return;
        }

        $payment = $booking->payments
            ->where('status', 'CAPTURED')
            ->sortByDesc('paid_at')
            ->first();

        if (! $payment) {
            return;
        }

        $payload = (array) $payment->response_payload;
        if (! empty($payload['ticket_email_sent_at'])) {
            return;
        }

        $this->sendSoftTicketMail($booking, $payment->id, false);
    }

    private function sendSoftTicketMail(Booking $booking, int $paymentId, bool $force = false): array
    {
        $recipient = $this->resolveRecipient($booking);
        if (! $recipient) {
            return ['sent' => false, 'recipient' => null];
        }

        $payment = Payment::query()->find($paymentId);
        if (! $payment) {
            return ['sent' => false, 'recipient' => $recipient];
        }

        $payload = (array) $payment->response_payload;
        if (! $force && ! empty($payload['ticket_email_sent_at'])) {
            return ['sent' => true, 'recipient' => $recipient];
        }

        try {
            Mail::to($recipient)->send(new SoftTicketIssuedMail($booking));
            $payload['ticket_email_sent_at'] = now()->toIso8601String();
            $payload['ticket_email_status'] = 'SENT';
            unset($payload['ticket_email_error']);
            $payment->update(['response_payload' => $payload]);

            return ['sent' => true, 'recipient' => $recipient];
        } catch (\Throwable $e) {
            report($e);
            $payload['ticket_email_status'] = 'FAILED';
            $payload['ticket_email_error'] = Str::limit($e->getMessage(), 180, '');
            $payment->update(['response_payload' => $payload]);

            return ['sent' => false, 'recipient' => $recipient];
        }
    }

    private function resolveRecipient(Booking $booking): ?string
    {
        return $booking->contact_email ?: $booking->customer?->email;

        if ((int) $booking->paid_amount >= (int) $booking->total_amount && in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                ->with('success', 'Booking đã được thanh toán trước đó.');
        }

        $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
        $estimatedPoints = $this->loyaltyPointService->previewPoints($amountDue);

        return view('frontend.payment', [
            'booking' => $booking,
            'amountDue' => $amountDue,
            'estimatedPoints' => $estimatedPoints,
            'providerOptions' => self::PROVIDER_OPTIONS,
        ]);
    }

    public function pay(Request $request, string $booking_code): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['required', Rule::in(array_keys(self::PROVIDER_OPTIONS))],
        ]);

        try {
            DB::transaction(function () use ($booking_code, $data) {
                /** @var Booking $booking */
                $booking = Booking::query()
                    ->where('booking_code', $booking_code)
                    ->with([
                        'show.auditorium.cinema',
                        'tickets.ticket',
                        'bookingProducts.product',
                        'discounts.coupon',
                        'payments.refunds',
                    ])
                    ->lockForUpdate()
                    ->firstOrFail();

                $currentCinemaId = current_cinema_id();
                if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
                    abort(404);
                }

                $booking = $this->expireIfNeeded($booking);
                $booking->refresh();
                $booking->loadMissing(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'payments.refunds']);

                if (in_array((string) $booking->status, self::TERMINAL_BOOKING_STATUSES, true)) {
                    abort(422, 'Booking đã hết hạn hoặc đã bị huỷ, không thể thanh toán tiếp.');
                }

                if ($booking->show && $booking->show->start_time && now()->gte($booking->show->start_time)) {
                    abort(422, 'Suất chiếu đã bắt đầu hoặc đã kết thúc, không thể thanh toán.');
                }

                $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
                if ($amountDue <= 0) {
                    abort(422, 'Booking này không còn số tiền nào cần thanh toán.');
                }

                $provider = (string) $data['provider'];
                $option = self::PROVIDER_OPTIONS[$provider];
                $externalRef = $provider . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

                Payment::create([
                    'booking_id' => $booking->id,
                    'provider' => $provider,
                    'method' => $option['method'],
                    'status' => 'CAPTURED',
                    'amount' => $amountDue,
                    'currency' => $booking->currency ?: 'VND',
                    'external_txn_ref' => $externalRef,
                    'request_payload' => [
                        'mode' => 'FRONTEND_SIMULATED',
                        'provider' => $provider,
                        'method' => $option['method'],
                        'booking_code' => $booking->booking_code,
                        'customer_name' => $booking->contact_name,
                        'customer_phone' => $booking->contact_phone,
                        'amount_due' => $amountDue,
                    ],
                    'response_payload' => [
                        'status' => 'SUCCESS',
                        'message' => 'Thanh toán mô phỏng thành công từ giao diện khách hàng.',
                        'external_txn_ref' => $externalRef,
                    ],
                    'paid_at' => now(),
                ]);

                $this->syncBookingFromPayments($booking->fresh(['payments.refunds', 'tickets.ticket']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.success', ['booking_code' => $booking_code])
            ->with('success', 'Thanh toán thành công. Vé của bạn đã được phát hành.');

    }

    private function syncBookingFromPayments(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }

        $paidAmount = (int) $booking->payments->sum(fn (Payment $payment) => $this->netCapturedAmount($payment));
        $paidAmount = max(0, min((int) $booking->total_amount, $paidAmount));

        $payload = ['paid_amount' => $paidAmount];
        $currentStatus = (string) $booking->status;

        if (! in_array($currentStatus, self::TERMINAL_BOOKING_STATUSES, true)) {
            $payload['status'] = $paidAmount > 0 ? 'PAID' : 'PENDING';
        }

        $booking->update($payload);

        $nextStatus = (string) ($payload['status'] ?? $currentStatus);
        if (in_array($nextStatus, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'ISSUED']);
        } else {
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'RESERVED']);
        }

        $freshBooking = $booking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
        $this->ticketLifecycleService->syncForBooking($freshBooking);
        $this->loyaltyPointService->syncForBooking($freshBooking);
    }

    private function expireIfNeeded(Booking $booking): Booking
    {
        if ((string) $booking->status !== 'PENDING') {
            return $booking;
        }

        if (! $booking->expires_at || now()->lt($booking->expires_at)) {
            return $booking;
        }

        DB::transaction(function () use ($booking) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'payments.refunds'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((string) $lockedBooking->status !== 'PENDING') {
                return;
            }

            $lockedBooking->update([
                'status' => 'EXPIRED',
                'paid_amount' => 0,
            ]);

            $lockedBooking->tickets()
                ->whereIn('status', ['RESERVED', 'ISSUED'])
                ->update(['status' => 'EXPIRED']);

            foreach ($lockedBooking->bookingProducts as $item) {
                $this->restoreInventory($lockedBooking, $item);
            }

            foreach ($lockedBooking->discounts as $discount) {
                if ($discount->coupon instanceof Coupon && $discount->coupon->status === 'REDEEMED') {
                    $discount->coupon->update([
                        'status' => $discount->coupon->expires_at && $discount->coupon->expires_at->isPast() ? 'EXPIRED' : 'ACTIVE',
                        'redeemed_at' => null,
                    ]);
                }
            }

            $expiredBooking = $lockedBooking->fresh(['customer.loyaltyAccount', 'tickets.ticket', 'payments.refunds']);
            $this->ticketLifecycleService->syncForBooking($expiredBooking);
            $this->loyaltyPointService->syncForBooking($expiredBooking);
            $this->refreshShowSaleStatus($lockedBooking->show);
        }, 3);

        return $booking->fresh([
            'customer',
            'show.movieVersion.movie',
            'show.auditorium.cinema',
            'tickets.seat',
            'tickets.ticketType',
            'tickets.seatType',
            'bookingProducts.product.category',
            'discounts.promotion',
            'discounts.coupon',
            'payments.refunds',
        ]);
    }

    private function restoreInventory(Booking $booking, BookingProduct $bookingProduct): void
    {
        $product = $bookingProduct->product;
        if (! $product || (int) $bookingProduct->qty <= 0) {
            return;
        }

        $locationId = StockMovement::query()
            ->where('reference_type', 'BOOKING')
            ->where('reference_id', $booking->id)
            ->where('product_id', $product->id)
            ->value('stock_location_id');

        $location = $locationId
            ? StockLocation::query()->find($locationId)
            : StockLocation::query()->firstOrCreate(
                ['cinema_id' => $booking->cinema_id, 'code' => 'KIOSK1'],
                ['name' => 'Quầy F&B chính', 'location_type' => 'KIOSK', 'is_active' => 1]
            );

        if (! $location) {
            return;
        }

        $alreadyRestored = StockMovement::query()
            ->where('reference_type', 'BOOKING_CANCEL')
            ->where('reference_id', $booking->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($alreadyRestored) {
            return;
        }

        $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
            ['stock_location_id' => $location->id, 'product_id' => $product->id],
            ['qty_on_hand' => 0, 'reorder_level' => 5]
        );

        $balance->update([
            'qty_on_hand' => (int) $balance->qty_on_hand + (int) $bookingProduct->qty,
        ]);

        StockMovement::create([
            'stock_location_id' => $location->id,
            'product_id' => $product->id,
            'movement_type' => 'IN',
            'qty_delta' => (int) $bookingProduct->qty,
            'reference_type' => 'BOOKING_CANCEL',
            'reference_id' => $booking->id,
            'note' => 'Hoàn tồn do booking hết hạn từ giao diện khách hàng',
            'created_at' => now(),
        ]);
    }


    private function refreshShowSaleStatus(?Show $show): void
    {
        if (! $show || in_array((string) $show->status, ['CANCELLED', 'ENDED'], true)) {
            return;
        }

        $show = Show::query()->find($show->id);
        if (! $show) {
            return;
        }

        $totalSeats = DB::table('seats')
            ->where('auditorium_id', $show->auditorium_id)
            ->where('is_active', 1)
            ->count();

        $busySeats = DB::table('booking_tickets')
            ->where('show_id', $show->id)
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->count();

        $nextStatus = ($totalSeats > 0 && $busySeats >= $totalSeats) ? 'SOLD_OUT' : 'ON_SALE';

        if ($show->start_time && now()->gte($show->start_time)) {
            return;
        }

        if ($show->status !== $nextStatus) {
            $show->update(['status' => $nextStatus]);
        }
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
