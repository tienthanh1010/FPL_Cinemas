<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingProduct;
use App\Models\Coupon;
use App\Models\InventoryBalance;
use App\Models\Movie;
use App\Models\Seat;
use App\Models\Show;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BookingController extends Controller
{
    private const STATUS_OPTIONS = [
        'PENDING' => 'Chờ thanh toán',
        'PAID' => 'Đã thanh toán',
        'CONFIRMED' => 'Đã xác nhận',
        'COMPLETED' => 'Hoàn tất',
        'EXPIRED' => 'Hết hạn',
        'CANCELLED' => 'Đã huỷ',
    ];

    private const PAID_LIKE_STATUSES = ['PAID', 'CONFIRMED', 'COMPLETED'];

    private const TERMINAL_STATUSES = ['CANCELLED', 'EXPIRED'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_OPTIONS))],
            'movie_id' => ['nullable', 'integer', 'exists:movies,id'],
            'show_id' => ['nullable', 'integer', 'exists:shows,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $query = Booking::query()
            ->with([
                'customer',
                'show.movieVersion.movie',
                'show.auditorium',
                'tickets.seat',
                'bookingProducts.product',
            ])
            ->when(! empty($filters['q']), function ($bookingQuery) use ($filters) {
                $q = trim((string) $filters['q']);

                $bookingQuery->where(function ($subQuery) use ($q) {
                    $subQuery->where('booking_code', 'like', "%{$q}%")
                        ->orWhere('contact_phone', 'like', "%{$q}%")
                        ->orWhere('contact_email', 'like', "%{$q}%")
                        ->orWhere('contact_name', 'like', "%{$q}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($q) {
                            $customerQuery->where('full_name', 'like', "%{$q}%")
                                ->orWhere('phone', 'like', "%{$q}%")
                                ->orWhere('email', 'like', "%{$q}%");
                        });
                });
            })
            ->when(! empty($filters['status']), fn ($bookingQuery) => $bookingQuery->where('status', $filters['status']))
            ->when(! empty($filters['movie_id']), function ($bookingQuery) use ($filters) {
                $bookingQuery->whereHas('show.movieVersion', fn ($movieVersionQuery) => $movieVersionQuery->where('movie_id', $filters['movie_id']));
            })
            ->when(! empty($filters['show_id']), fn ($bookingQuery) => $bookingQuery->where('show_id', $filters['show_id']))
            ->when(! empty($filters['date_from']), function ($bookingQuery) use ($filters) {
                $bookingQuery->whereHas('show', fn ($showQuery) => $showQuery->whereDate('start_time', '>=', $filters['date_from']));
            })
            ->when(! empty($filters['date_to']), function ($bookingQuery) use ($filters) {
                $bookingQuery->whereHas('show', fn ($showQuery) => $showQuery->whereDate('start_time', '<=', $filters['date_to']));
            });

        $summaryQuery = clone $query;

        $summary = [
            'bookings' => (clone $summaryQuery)->count(),
            'tickets' => DB::table('booking_tickets')
                ->whereIn('booking_id', (clone $summaryQuery)->select('bookings.id'))
                ->count(),
            'revenue' => (clone $summaryQuery)
                ->whereIn('status', self::PAID_LIKE_STATUSES)
                ->sum(DB::raw('CASE WHEN paid_amount > 0 THEN paid_amount ELSE total_amount END')),
            'cancelled' => (clone $summaryQuery)
                ->whereIn('status', self::TERMINAL_STATUSES)
                ->count(),
        ];

        $bookings = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
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

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'filters' => [
                'q' => $filters['q'] ?? '',
                'status' => $filters['status'] ?? '',
                'movie_id' => isset($filters['movie_id']) ? (int) $filters['movie_id'] : null,
                'show_id' => isset($filters['show_id']) ? (int) $filters['show_id'] : null,
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
            ],
            'summary' => $summary,
            'movies' => $movies,
            'shows' => $shows,
            'statusOptions' => self::STATUS_OPTIONS,
        ]);
    }

    public function show(Booking $booking): View
    {
        $booking->load([
            'customer',
            'cinema',
            'show.movieVersion.movie',
            'show.auditorium.cinema',
            'tickets.seat',
            'tickets.ticketType',
            'tickets.seatType',
            'bookingProducts.product',
            'discounts.promotion',
            'discounts.coupon',
            'payments.refunds',
        ]);

        $totals = [
            'ticket_count' => $booking->tickets->count(),
            'product_qty' => (int) $booking->bookingProducts->sum('qty'),
            'discount_count' => $booking->discounts->count(),
            'refund_amount' => (int) $booking->payments->flatMap->refunds->where('status', 'SUCCESS')->sum('amount'),
            'refund_pending_amount' => (int) $booking->payments->flatMap->refunds->where('status', 'PENDING')->sum('amount'),
        ];

        return view('admin.bookings.show', [
            'booking' => $booking,
            'statusOptions' => self::STATUS_OPTIONS,
            'totals' => $totals,
        ]);
    }

    public function update(Request $request, Booking $booking): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_OPTIONS))],
        ]);

        if ($booking->status === $data['status']) {
            return back()->with('success', 'Trạng thái booking không thay đổi.');
        }

        try {
            DB::transaction(function () use ($booking, $data) {
                /** @var Booking $lockedBooking */
                $lockedBooking = Booking::query()
                    ->with(['tickets', 'bookingProducts.product', 'discounts.coupon', 'show.auditorium'])
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                $currentStatus = (string) $lockedBooking->status;
                $newStatus = (string) $data['status'];

                if (in_array($currentStatus, self::TERMINAL_STATUSES, true) && ! in_array($newStatus, self::TERMINAL_STATUSES, true)) {
                    abort(422, 'Booking đã ở trạng thái kết thúc, không thể mở lại từ màn quản trị này.');
                }

                if (in_array($newStatus, self::TERMINAL_STATUSES, true)) {
                    $this->closeBooking($lockedBooking, $newStatus);
                } else {
                    $updatePayload = ['status' => $newStatus];
                    if (in_array($newStatus, self::PAID_LIKE_STATUSES, true) && (int) $lockedBooking->paid_amount <= 0) {
                        $updatePayload['paid_amount'] = (int) $lockedBooking->total_amount;
                    }

                    $lockedBooking->update($updatePayload);

                    $ticketStatus = in_array($newStatus, self::PAID_LIKE_STATUSES, true) ? 'ISSUED' : 'RESERVED';
                    $lockedBooking->tickets()
                        ->whereIn('status', ['RESERVED', 'ISSUED'])
                        ->update(['status' => $ticketStatus]);
                }

                $this->refreshShowSaleStatus($lockedBooking->show);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã cập nhật trạng thái booking.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if (in_array((string) $booking->status, self::TERMINAL_STATUSES, true)) {
            return back()->with('error', 'Booking này đã ở trạng thái kết thúc.');
        }

        try {
            DB::transaction(function () use ($booking) {
                /** @var Booking $lockedBooking */
                $lockedBooking = Booking::query()
                    ->with(['tickets', 'bookingProducts.product', 'discounts.coupon', 'show.auditorium'])
                    ->lockForUpdate()
                    ->findOrFail($booking->id);

                $this->closeBooking($lockedBooking, 'CANCELLED');
                $this->refreshShowSaleStatus($lockedBooking->show);
            }, 3);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã huỷ booking và trả lại ghế/tồn kho liên quan.');
    }

    private function closeBooking(Booking $booking, string $status): void
    {
        if (! in_array($status, self::TERMINAL_STATUSES, true)) {
            return;
        }

        $booking->update([
            'status' => $status,
            'paid_amount' => $status === 'CANCELLED' ? 0 : (int) $booking->paid_amount,
        ]);

        $booking->tickets()
            ->whereIn('status', ['RESERVED', 'ISSUED'])
            ->update(['status' => $status]);

        foreach ($booking->bookingProducts as $item) {
            $this->restoreInventory($booking, $item);
        }

        foreach ($booking->discounts as $discount) {
            if ($discount->coupon instanceof Coupon && $discount->coupon->status === 'REDEEMED') {
                $discount->coupon->update([
                    'status' => $discount->coupon->expires_at && $discount->coupon->expires_at->isPast() ? 'EXPIRED' : 'ACTIVE',
                    'redeemed_at' => null,
                ]);
            }
        }
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
            'note' => 'Trả lại tồn kho do huỷ/hết hạn booking ' . $booking->booking_code,
            'created_at' => now(),
        ]);
    }

    private function refreshShowSaleStatus(?Show $show): void
    {
        if (! $show || in_array((string) $show->status, ['CANCELLED', 'ENDED'], true)) {
            return;
        }

        $show = Show::query()->with('auditorium')->find($show->id);
        if (! $show || ! $show->auditorium) {
            return;
        }

        $totalSeats = Seat::query()
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
}
