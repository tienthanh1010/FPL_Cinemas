<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SoftTicketIssuedMail;

use App\Mail\SoftTicketIssuedMail;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingLifecycleService;
use App\Services\LoyaltyPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PROVIDER_OPTIONS = [
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

    private const GATEWAY_PAYMENT_STATUSES = ['INITIATED', 'WAITING_TRANSFER'];
    private const FAILED_PAYMENT_STATUSES = ['FAILED', 'CANCELLED'];


    public function __construct(
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly BookingLifecycleService $bookingLifecycleService,
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
                'tickets.ticket',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

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
                'tickets.ticket',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ])
            ->firstOrFail();

        $currentCinemaId = current_cinema_id();
        if ($currentCinemaId && (int) $booking->cinema_id !== (int) $currentCinemaId) {
            abort(404);
        }

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

    }
}
