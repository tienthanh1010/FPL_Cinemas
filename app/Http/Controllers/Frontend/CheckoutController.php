<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingLifecycleService;
use App\Services\LoyaltyPointService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const PROVIDER_OPTIONS = [
        'MOMO' => ['label' => 'Ví MoMo', 'method' => 'EWALLET', 'description' => 'Thanh toán mô phỏng bằng ví MoMo.'],
        'ZALOPAY' => ['label' => 'ZaloPay', 'method' => 'EWALLET', 'description' => 'Thanh toán mô phỏng qua ví ZaloPay.'],
        'VNPAY' => ['label' => 'VNPay', 'method' => 'BANK_TRANSFER', 'description' => 'Thanh toán mô phỏng qua VNPay / ngân hàng nội địa.'],
        'CARD' => ['label' => 'Thẻ ngân hàng', 'method' => 'CARD', 'description' => 'Thanh toán mô phỏng bằng thẻ nội địa / quốc tế.'],
    ];

    private const TERMINAL_BOOKING_STATUSES = ['CANCELLED', 'EXPIRED'];
    private const PAID_LIKE_BOOKING_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    public function __construct(
        private readonly LoyaltyPointService $loyaltyPointService,
        private readonly BookingLifecycleService $bookingLifecycleService,
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

            return redirect()->route('booking.success', ['booking_code' => $booking->booking_code])
                ->with('success', 'Booking đã được thanh toán trước đó.');
        }

        session(['editable_booking_code' => $booking->booking_code]);

        $amountDue = max(0, (int) $booking->total_amount - (int) $booking->paid_amount);
        $estimatedPoints = $this->loyaltyPointService->previewPoints($amountDue);

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
                $booking->loadMissing(['tickets.ticket', 'discounts.coupon', 'payments.refunds']);

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
                        'message' => 'Thanh toán online mô phỏng thành công.',
                        'external_txn_ref' => $externalRef,
                    ],
                    'paid_at' => now(),
                ]);

                $this->bookingLifecycleService->syncBookingFromPayments($booking->fresh(['payments.refunds', 'tickets.ticket']));
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        session()->forget('editable_booking_code');

        return redirect()->route('booking.success', ['booking_code' => $booking_code])
            ->with('success', 'Thanh toán thành công. Vé của bạn đã được phát hành.');
    }
}
