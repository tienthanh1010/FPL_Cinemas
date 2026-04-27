<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BankTransferQrService;
use App\Services\BookingLifecycleService;
use App\Services\LoyaltyPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const TERMINAL_BOOKING_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const PAID_LIKE_BOOKING_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function __construct(
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly BookingLifecycleService $bookingLifecycleService,
        private readonly BankTransferQrService $bankTransferQrService,
    ) {
    }

    public function show(string $booking_code): View|RedirectResponse
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with([
                'customer',
                'show.movieVersion.movie',
                'show.auditorium.cinema',
                'tickets.seat',
                'tickets.ticketType',
                'tickets.seatType',
                'tickets.ticket',
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

        $booking = $this->bookingLifecycleService->expirePendingBooking($booking);

        if ((int) $booking->paid_amount >= (int) $booking->total_amount && in_array((string) $booking->status, self::PAID_LIKE_BOOKING_STATUSES, true)) {
            return redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                ->with('success', 'Booking đã được thanh toán trước đó.');
        }

        $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
        $estimatedPoints = $this->loyaltyPointService->previewPoints($amountDue);
        $payment = $amountDue > 0 ? $this->prepareTransferPayment($booking, $amountDue) : null;
        $transferData = $amountDue > 0 ? $this->bankTransferQrService->payloadForBooking($booking, $amountDue) : null;

        return view('frontend.payment', [
            'booking' => $booking->fresh([
                'customer',
                'show.movieVersion.movie.contentRating',
                'show.auditorium.cinema',
                'tickets.seat',
                'tickets.ticketType',
                'tickets.seatType',
                'bookingProducts.product.category',
                'discounts.promotion',
                'discounts.coupon',
                'payments.refunds',
            ]),
            'amountDue' => $amountDue,
            'estimatedPoints' => $estimatedPoints,
            'payment' => $payment,
            'transferData' => $transferData,
            'bankProviderLabel' => $this->bankTransferQrService->providerLabel(),
        ]);
    }

    public function pay(Request $request, string $booking_code): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:mark_transferred'],
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

                $booking = $this->bookingLifecycleService->expirePendingBooking($booking);
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

                if ($data['action'] !== 'mark_transferred') {
                    abort(422, 'Hành động thanh toán không hợp lệ.');
                }

                $payment = $this->findOrCreateTransferPayment($booking, $amountDue);
                $requestPayload = array_merge((array) ($payment->request_payload ?? []), $this->bankTransferQrService->payloadForBooking($booking, $amountDue), [
                    'booking_code' => $booking->booking_code,
                    'mode' => 'MB_TRANSFER_QR',
                    'customer_name' => $booking->contact_name,
                    'customer_phone' => $booking->contact_phone,
                    'customer_email' => $booking->contact_email,
                    'marked_transferred_at' => now()->toIso8601String(),
                ]);

                $payment->update([
                    'provider' => 'MBBANK',
                    'method' => 'BANK_TRANSFER',
                    'status' => 'AUTHORIZED',
                    'amount' => $amountDue,
                    'currency' => $booking->currency ?: 'VND',
                    'request_payload' => $requestPayload,
                    'response_payload' => [
                        'status' => 'CUSTOMER_MARKED_TRANSFERRED',
                        'message' => 'Khách hàng đã bấm xác nhận chuyển khoản trên giao diện thanh toán.',
                        'marked_at' => now()->toIso8601String(),
                    ],
                ]);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.payment', ['booking_code' => $booking_code])
            ->with('success', 'Hệ thống đã ghi nhận yêu cầu đối soát chuyển khoản. Sau khi giao dịch được admin xác nhận, vé sẽ tự phát hành và bạn có thể check-in bằng QR.');
    }

    private function prepareTransferPayment(Booking $booking, int $amountDue): Payment
    {
        return DB::transaction(function () use ($booking, $amountDue) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['payments'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            return $this->findOrCreateTransferPayment($lockedBooking, $amountDue);
        }, 3);
    }

    private function findOrCreateTransferPayment(Booking $booking, int $amountDue): Payment
    {
        $payment = $booking->payments()
            ->whereIn('status', ['INITIATED', 'AUTHORIZED'])
            ->orderByDesc('id')
            ->first();

        $payload = $this->bankTransferQrService->payloadForBooking($booking, $amountDue) + [
            'booking_code' => $booking->booking_code,
            'mode' => 'MB_TRANSFER_QR',
        ];

        if ($payment) {
            $payment->update([
                'provider' => 'MBBANK',
                'method' => 'BANK_TRANSFER',
                'amount' => $amountDue,
                'currency' => $booking->currency ?: 'VND',
                'request_payload' => $payload,
                'external_txn_ref' => $payment->external_txn_ref ?: ('MBQR-' . $booking->booking_code),
            ]);

            return $payment->fresh();
        }

        return Payment::create([
            'booking_id' => $booking->id,
            'provider' => 'MBBANK',
            'method' => 'BANK_TRANSFER',
            'status' => 'INITIATED',
            'amount' => $amountDue,
            'currency' => $booking->currency ?: 'VND',
            'external_txn_ref' => 'MBQR-' . $booking->booking_code,
            'request_payload' => $payload,
            'response_payload' => [
                'status' => 'WAITING_TRANSFER',
                'message' => 'Đã tạo QR chuyển khoản cho booking.',
            ],
        ]);
    }
}
