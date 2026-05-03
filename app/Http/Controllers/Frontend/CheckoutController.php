<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Mail\SoftTicketIssuedMail;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingLifecycleService;
use App\Services\LoyaltyPointService;
use App\Services\MomoPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'description' => 'Quét mã QR MoMo để thanh toán vé online.',
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
    private const GATEWAY_PAYMENT_STATUSES = ['INITIATED', 'AUTHORIZED'];

    public function __construct(
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly MomoPaymentService $momoPaymentService,
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

        try {
            [$paymentId, $bookingCode] = $this->createOrReuseGatewayPayment($booking_code, 'MOMO');
        } catch (\Throwable $e) {
            return view('frontend.payment', [
                'booking' => $booking,
                'amountDue' => max(0, (int) $booking->total_amount - (int) $booking->paid_amount),
                'estimatedPoints' => $this->loyaltyPointService->previewPoints(max(0, (int) $booking->total_amount - (int) $booking->paid_amount)),
                'providerOptions' => ['MOMO' => self::PROVIDER_OPTIONS['MOMO']],
                'pendingPayments' => collect(),
                'emailRecipient' => $this->resolveRecipient($booking),
            ])->with('error', $e->getMessage());
        }

        return redirect()->route('booking.payment.gateway', ['booking_code' => $bookingCode, 'payment' => $paymentId]);
    }

    public function pay(Request $request, string $booking_code): RedirectResponse
    {
        $data = $request->validate([
            'provider' => ['nullable', Rule::in(array_keys(self::PROVIDER_OPTIONS))],
        ]);

        try {
            [$paymentId, $bookingCode] = $this->createOrReuseGatewayPayment($booking_code, (string) ($data['provider'] ?? 'MOMO'));
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

        if ((string) $payment->provider === 'MOMO') {
            try {
                if ((string) $payment->status === 'INITIATED' || blank(data_get($payment->response_payload, 'momo.pay_url'))) {
                    $momo = $this->momoPaymentService->createPayment($payment, $booking);
                    $payment->update([
                        'status' => 'AUTHORIZED',
                        'external_txn_ref' => $momo['order_id'],
                        'request_payload' => array_merge((array) $payment->request_payload, [
                            'flow' => 'MOMO_QR_ONLINE',
                            'provider_label' => 'MoMo',
                            'order_id' => $momo['order_id'],
                            'request_id' => $momo['request_id'],
                            'order_info' => $momo['order_info'],
                            'amount' => (int) $payment->amount,
                            'transfer_content' => $momo['order_info'],
                            'momo_request' => $momo['request'],
                        ]),
                        'response_payload' => array_merge((array) $payment->response_payload, [
                            'status' => 'AUTHORIZED',
                            'message' => 'Đã tạo mã QR MoMo, đang chờ khách thanh toán.',
                            'momo' => [
                                'pay_url' => $momo['pay_url'],
                                'deeplink' => $momo['deeplink'],
                                'qr_code_url' => $momo['qr_code_url'],
                                'raw' => $momo['response'],
                            ],
                        ]),
                    ]);
                    $payment->refresh();
                }
            } catch (\Throwable $e) {
                return redirect()->route('booking.payment', $booking_code)->with('error', $e->getMessage());
            }
        } elseif ((string) $payment->status === 'INITIATED') {
            $payment->update([
                'status' => 'AUTHORIZED',
                'request_payload' => array_merge((array) $payment->request_payload, ['gateway_viewed_at' => now()->toIso8601String()]),
                'response_payload' => array_merge((array) $payment->response_payload, [
                    'status' => 'AUTHORIZED',
                    'message' => 'Giao dịch VNPay đang chờ xác nhận.',
                ]),
            ]);
            $payment->refresh();
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
        if ((string) $payment->provider === 'MOMO') {
            return redirect()->route('booking.payment.gateway', ['booking_code' => $booking_code, 'payment' => $payment]);
        }

        $data = $request->validate([
            'result' => ['required', Rule::in(['success', 'failed', 'cancel'])],
        ]);

        try {
            $result = DB::transaction(function () use ($booking_code, $payment, $data) {
                $booking = Booking::query()
                    ->where('booking_code', $booking_code)
                    ->with(['customer', 'show.movieVersion.movie.contentRating', 'show.auditorium.cinema', 'tickets.seat', 'tickets.ticketType', 'tickets.seatType', 'tickets.ticket', 'discounts.promotion', 'discounts.coupon', 'payments.refunds'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $payment->booking_id !== (int) $booking->id) {
                    abort(404);
                }

                $this->assertBookingPayable($booking, true);
                $payment->refresh();

                if (! in_array((string) $payment->status, self::GATEWAY_PAYMENT_STATUSES, true)) {
                    return ['redirect' => redirect()->route('booking.success', ['booking_code' => $booking->booking_code])->with('success', 'Giao dịch đã được xử lý trước đó.'), 'send_mail' => false, 'booking_code' => $booking->booking_code];
                }

                if ((string) $data['result'] === 'success') {
                    $this->capturePayment($booking, $payment, ['source' => 'VNPAY_SIMULATION']);
                    session()->forget('editable_booking_code');

                    return ['redirect' => redirect()->route('booking.success', ['booking_code' => $booking->booking_code]), 'send_mail' => true, 'booking_code' => $booking->booking_code, 'payment_id' => $payment->id, 'provider_label' => 'VNPay'];
                }

                $status = (string) $data['result'] === 'cancel' ? 'CANCELLED' : 'FAILED';
                $this->failPaymentAndReleaseSeats($booking, $payment, $status, ['source' => 'VNPAY_SIMULATION']);

                return ['redirect' => redirect()->route('booking.lookup', ['booking_code' => $booking->booking_code])->with('error', 'Thanh toán không thành công. Booking đã được huỷ và ghế đã được nhả.'), 'send_mail' => false, 'booking_code' => $booking->booking_code];
            }, 3);
        } catch (\Throwable $e) {
            return redirect()->route('booking.payment', ['booking_code' => $booking_code])->with('error', $e->getMessage());
        }

        if (($result['send_mail'] ?? false) === true) {
            $booking = $this->findBooking((string) $result['booking_code']);
            $sendState = $this->sendSoftTicketMail($booking, (int) $result['payment_id']);
            $message = 'Thanh toán thành công qua ' . ($result['provider_label'] ?? 'cổng thanh toán') . '.';
            $message .= $sendState['sent'] ? ' Vé bản mềm đã được gửi tới email của bạn.' : ' Không gửi được email lúc này, bạn có thể bấm gửi lại vé trong trang chi tiết booking.';

            return $result['redirect']->with('success', $message);
        }

        return $result['redirect'];
    }

    public function momoReturn(Request $request): RedirectResponse
    {
        $result = $this->processMomoResult($request->all(), false);

        if (($result['send_mail'] ?? false) === true) {
            $booking = $this->findBooking((string) $result['booking_code']);
            $sendState = $this->sendSoftTicketMail($booking, (int) $result['payment_id']);
            $message = 'Thanh toán MoMo thành công.';
            $message .= $sendState['sent'] ? ' Vé bản mềm đã được gửi tới email của bạn.' : ' Không gửi được email lúc này, bạn có thể gửi lại trong trang chi tiết booking.';

            return redirect()->route('booking.success', ['booking_code' => $result['booking_code']])->with('success', $message);
        }

        if (($result['status'] ?? '') === 'already_paid') {
            return redirect()->route('booking.success', ['booking_code' => $result['booking_code']])->with('success', 'Booking đã được thanh toán trước đó.');
        }

        return redirect()->route('booking.lookup', ['booking_code' => $result['booking_code'] ?? ''])
            ->with('error', $result['message'] ?? 'Thanh toán MoMo không thành công. Booking đã được huỷ và ghế đã được nhả.');
    }

    public function momoIpn(Request $request): JsonResponse
    {
        $result = $this->processMomoResult($request->all(), true);

        return response()->json([
            'resultCode' => ($result['ok'] ?? false) ? 0 : 1,
            'message' => $result['message'] ?? (($result['ok'] ?? false) ? 'Success' : 'Failed'),
        ]);
    }

    public function paymentStatus(string $booking_code, Payment $payment): JsonResponse
    {
        $booking = $this->findBooking($booking_code);
        if ((int) $payment->booking_id !== (int) $booking->id) {
            abort(404);
        }

        $payment->refresh();
        $booking->refresh();

        return response()->json([
            'booking_status' => $booking->status,
            'payment_status' => $payment->status,
            'paid' => in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true) && (int) $booking->paid_amount >= (int) $booking->total_amount,
            'failed' => in_array((string) $payment->status, ['FAILED', 'CANCELLED'], true) || in_array((string) $booking->status, self::TERMINAL_BOOKING_STATUSES, true),
            'success_url' => route('booking.success', ['booking_code' => $booking->booking_code]),
            'lookup_url' => route('booking.lookup', ['booking_code' => $booking->booking_code]),
        ]);
    }

    public function resendTicketMail(Request $request, string $booking_code): RedirectResponse
    {
        $booking = $this->findBooking($booking_code)->fresh(['payments.refunds', 'tickets.ticket', 'customer', 'show.movieVersion.movie.contentRating', 'show.auditorium.cinema', 'tickets.seat', 'tickets.ticketType', 'tickets.seatType']);

        if (! in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return back()->with('error', 'Chỉ booking đã thanh toán mới có thể gửi lại vé qua email.');
        }

        $payment = $booking->payments->where('status', 'CAPTURED')->sortByDesc('paid_at')->first();
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

    private function createOrReuseGatewayPayment(string $bookingCode, string $provider): array
    {
        return DB::transaction(function () use ($bookingCode, $provider) {
            $booking = Booking::query()
                ->where('booking_code', $bookingCode)
                ->with(['show.auditorium.cinema', 'tickets.ticket', 'discounts.coupon', 'payments.refunds', 'payments'])
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertBookingPayable($booking);
            $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
            if ($amountDue <= 0) {
                abort(422, 'Booking này không còn số tiền nào cần thanh toán.');
            }

            $provider = $provider !== '' ? $provider : 'MOMO';
            $option = self::PROVIDER_OPTIONS[$provider] ?? self::PROVIDER_OPTIONS['MOMO'];

            $existingPayment = $booking->payments
                ->first(fn (Payment $payment) => (string) $payment->provider === $provider && (int) $payment->amount === $amountDue && in_array((string) $payment->status, self::GATEWAY_PAYMENT_STATUSES, true));

            if ($existingPayment) {
                $existingPayment->update([
                    'status' => 'INITIATED',
                    'request_payload' => array_merge((array) $existingPayment->request_payload, ['gateway_opened_at' => now()->toIso8601String()]),
                ]);

                return [$existingPayment->id, $booking->booking_code];
            }

            $externalRef = $provider . '-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
            $payment = Payment::create([
                'booking_id' => $booking->id,
                'provider' => $provider,
                'method' => $option['method'],
                'status' => 'INITIATED',
                'amount' => $amountDue,
                'currency' => $booking->currency ?: 'VND',
                'external_txn_ref' => $externalRef,
                'request_payload' => [
                    'flow' => $provider === 'MOMO' ? 'MOMO_QR_ONLINE' : 'HOSTED_GATEWAY_SIMULATION',
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
                    'status' => 'INITIATED',
                    'message' => 'Giao dịch đã khởi tạo và đang chờ thanh toán.',
                ],
            ]);

            return [$payment->id, $booking->booking_code];
        }, 3);
    }

    private function processMomoResult(array $data, bool $fromIpn): array
    {
        try {
            if (! $this->momoPaymentService->verifyResultSignature($data)) {
                return ['ok' => false, 'message' => 'Chữ ký MoMo không hợp lệ.'];
            }

            $orderId = $this->momoPaymentService->resultOrderId($data);
            if (! $orderId) {
                return ['ok' => false, 'message' => 'Thiếu orderId MoMo.'];
            }

            $result = DB::transaction(function () use ($data, $orderId) {
                $payment = Payment::query()
                    ->where('provider', 'MOMO')
                    ->where('external_txn_ref', $orderId)
                    ->with(['booking.show', 'booking.payments.refunds', 'booking.tickets.ticket'])
                    ->lockForUpdate()
                    ->first();

                if (! $payment || ! $payment->booking) {
                    return ['ok' => false, 'message' => 'Không tìm thấy giao dịch MoMo trong hệ thống.'];
                }

                $booking = $payment->booking;
                if ((int) $payment->amount !== (int) ($data['amount'] ?? 0)) {
                    return ['ok' => false, 'message' => 'Số tiền MoMo trả về không khớp booking.'];
                }

                if ((string) $payment->status === 'CAPTURED') {
                    return ['ok' => true, 'status' => 'already_paid', 'booking_code' => $booking->booking_code, 'message' => 'Giao dịch đã được ghi nhận trước đó.'];
                }

                if ($this->momoPaymentService->isSuccessfulResult($data)) {
                    $this->capturePayment($booking, $payment, ['momo_result' => $data]);
                    session()->forget('editable_booking_code');

                    return ['ok' => true, 'status' => 'paid', 'send_mail' => true, 'booking_code' => $booking->booking_code, 'payment_id' => $payment->id, 'message' => 'Thanh toán MoMo thành công.'];
                }

                $status = $this->momoPaymentService->isCancelledResult($data) ? 'CANCELLED' : 'FAILED';
                $this->failPaymentAndReleaseSeats($booking, $payment, $status, ['momo_result' => $data]);

                return ['ok' => true, 'status' => strtolower($status), 'booking_code' => $booking->booking_code, 'message' => 'Thanh toán MoMo không thành công. Booking đã được huỷ và ghế đã được nhả.'];
            }, 3);

            if (($fromIpn && ($result['send_mail'] ?? false)) === true) {
                $booking = $this->findBooking((string) $result['booking_code']);
                $this->sendSoftTicketMail($booking, (int) $result['payment_id']);
            }

            return $result;
        } catch (\Throwable $e) {
            report($e);
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function capturePayment(Booking $booking, Payment $payment, array $extraPayload = []): void
    {
        $payment->update([
            'status' => 'CAPTURED',
            'paid_at' => now(),
            'response_payload' => array_merge((array) $payment->response_payload, [
                'status' => 'SUCCESS',
                'message' => 'Thanh toán thành công.',
                'paid_at' => now()->toIso8601String(),
            ], $extraPayload),
        ]);

        $this->bookingLifecycleService->syncBookingFromPayments($booking->fresh(['payments.refunds', 'tickets.ticket']));
    }

    private function failPaymentAndReleaseSeats(Booking $booking, Payment $payment, string $status, array $extraPayload = []): void
    {
        $payment->update([
            'status' => $status,
            'response_payload' => array_merge((array) $payment->response_payload, [
                'status' => $status,
                'message' => 'Thanh toán không thành công. Booking đã được huỷ và ghế đã được nhả.',
            ], $extraPayload),
        ]);

        if ((string) $booking->status === 'PENDING') {
            $booking->update([
                'status' => 'CANCELLED',
                'paid_amount' => 0,
                'notes' => trim((string) ($booking->notes ? ($booking->notes . PHP_EOL) : '') . 'Thanh toán online không thành công, tự động huỷ booking và nhả ghế.'),
            ]);
            $booking->tickets()->whereIn('status', ['RESERVED', 'ISSUED'])->update(['status' => 'CANCELLED']);
            $this->bookingLifecycleService->refreshShowSaleStatus($booking->show);
        }
    }

    private function findBooking(string $bookingCode): Booking
    {
        $booking = Booking::query()
            ->where('booking_code', $bookingCode)
            ->with(['customer', 'show.movieVersion.movie', 'show.auditorium.cinema', 'tickets.seat', 'tickets.ticketType', 'tickets.seatType', 'tickets.ticket', 'discounts.promotion', 'discounts.coupon', 'payments.refunds'])
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

        $payment = $booking->payments->where('status', 'CAPTURED')->sortByDesc('paid_at')->first();
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
        $booking->loadMissing(['tickets.ticket', 'tickets.seat', 'tickets.ticketType', 'tickets.seatType', 'show.movieVersion.movie', 'show.auditorium.cinema', 'customer']);
        $recipient = $this->resolveRecipient($booking);
        if (! $recipient) {
            return ['sent' => false, 'recipient' => null, 'reason' => 'missing_recipient'];
        }

        $issuedTickets = $booking->tickets->filter(fn ($ticket) => filled($ticket->ticket?->ticket_code));
        if ($issuedTickets->isEmpty()) {
            return ['sent' => false, 'recipient' => $recipient, 'reason' => 'missing_issued_tickets'];
        }

        $payment = Payment::query()->find($paymentId);
        if (! $payment) {
            return ['sent' => false, 'recipient' => $recipient, 'reason' => 'payment_not_found'];
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

            return ['sent' => false, 'recipient' => $recipient, 'reason' => 'mailer_exception'];
        }
    }

    private function resolveRecipient(Booking $booking): ?string
    {
        $candidate = trim((string) ($booking->contact_email ?: $booking->customer?->email ?: ''));

        return filter_var($candidate, FILTER_VALIDATE_EMAIL) ? $candidate : null;
    }
}
