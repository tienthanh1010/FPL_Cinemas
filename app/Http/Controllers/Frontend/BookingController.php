<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingTicket;
use App\Models\Seat;
use App\Models\Show;
use App\Models\ShowPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'show_id' => ['required', 'integer'],
            'qty' => ['required', 'integer', 'min:1', 'max:10'],
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:32'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        $bookingCode = null;

        try {
            DB::transaction(function () use ($data, &$bookingCode) {
                $show = Show::query()
                    ->with(['auditorium.cinema', 'movieVersion.movie'])
                    ->lockForUpdate()
                    ->findOrFail($data['show_id']);

                if (! in_array($show->status, ['SCHEDULED', 'ON_SALE'], true)) {
                    abort(422, 'Suất chiếu không khả dụng.');
                }

                $auditoriumId = $show->auditorium_id;
                $cinemaId = $show->auditorium->cinema_id;

                // Find available seats
                $reservedSeatIds = BookingTicket::query()
                    ->where('show_id', $show->id)
                    ->whereIn('status', ['RESERVED', 'ISSUED'])
                    ->select('seat_id');

                $heldSeatIds = DB::table('seat_holds')
                    ->where('show_id', $show->id)
                    ->whereIn('status', ['HELD', 'CONFIRMED'])
                    ->where('expires_at', '>', now())
                    ->select('seat_id');

                $seats = Seat::query()
                    ->where('auditorium_id', $auditoriumId)
                    ->where('is_active', 1)
                    ->whereNotIn('id', $reservedSeatIds)
                    ->whereNotIn('id', $heldSeatIds)
                    ->orderBy('row_label')
                    ->orderBy('col_number')
                    ->limit((int) $data['qty'])
                    ->lockForUpdate()
                    ->get();

                if ($seats->count() < (int) $data['qty']) {
                    abort(422, 'Không đủ ghế trống cho suất chiếu này.');
                }

                // Pricing (Adult + Regular by default)
                $defaultPrice = 120000;
                $adultTicketTypeId = 1;

                $priceBySeatType = ShowPrice::query()
                    ->where('show_id', $show->id)
                    ->where('ticket_type_id', $adultTicketTypeId)
                    ->where('is_active', 1)
                    ->get()
                    ->keyBy('seat_type_id');

                $subtotal = 0;
                foreach ($seats as $seat) {
                    $subtotal += (int) (($priceBySeatType[$seat->seat_type_id]->price_amount ?? null) ?? $defaultPrice);
                }

                $bookingCode = 'BK' . now()->format('Ymd') . strtoupper(Str::random(6));

                $booking = Booking::create([
                    'public_id' => (string) Str::ulid(),
                    'booking_code' => $bookingCode,
                    'show_id' => $show->id,
                    'cinema_id' => $cinemaId,
                    'sales_channel_id' => 1,
                    'status' => 'PENDING',
                    'contact_name' => $data['contact_name'],
                    'contact_phone' => $data['contact_phone'],
                    'contact_email' => $data['contact_email'] ?? null,
                    'subtotal_amount' => $subtotal,
                    'discount_amount' => 0,
                    'total_amount' => $subtotal,
                    'paid_amount' => 0,
                    'currency' => 'VND',
                    'expires_at' => now()->addMinutes(15),
                ]);

                foreach ($seats as $seat) {
                    $unitPrice = (int) (($priceBySeatType[$seat->seat_type_id]->price_amount ?? null) ?? $defaultPrice);

                    BookingTicket::create([
                        'booking_id' => $booking->id,
                        'show_id' => $show->id,
                        'seat_id' => $seat->id,
                        'ticket_type_id' => $adultTicketTypeId,
                        'seat_type_id' => $seat->seat_type_id,
                        'unit_price_amount' => $unitPrice,
                        'discount_amount' => 0,
                        'final_price_amount' => $unitPrice,
                        'status' => 'RESERVED',
                    ]);
                }
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('booking.success', ['booking_code' => $bookingCode]);
    }

    public function success(string $booking_code)
    {
        $booking = Booking::query()
            ->where('booking_code', $booking_code)
            ->with(['tickets'])
            ->firstOrFail();

        return view('frontend.booking_success', compact('booking'));
    }
}
