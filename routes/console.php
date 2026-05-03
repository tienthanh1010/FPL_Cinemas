<?php

use App\Models\Booking;
use App\Models\StockMovement;
use App\Models\StockLocation;
use App\Models\Show;
use App\Models\Seat;
use App\Models\InventoryBalance;
use App\Models\Coupon;
use App\Models\BookingProduct;
use App\Services\TicketLifecycleService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tickets:backfill
    {--booking_id=* : Chỉ xử lý các booking ID cụ thể}
    {--booking_code=* : Chỉ xử lý các mã booking cụ thể}
    {--show_id= : Chỉ xử lý booking của một suất chiếu}
    {--sync-all : Đồng bộ tất cả booking có vé ISSUED, kể cả đã có ticket điện tử}
    {--dry-run : Chỉ xem trước, không ghi dữ liệu}', function () {
    /** @var TicketLifecycleService $ticketLifecycleService */
    $ticketLifecycleService = app(TicketLifecycleService::class);

    $bookingIds = collect((array) $this->option('booking_id'))
        ->map(fn ($value) => (int) $value)
        ->filter()
        ->values();

    $bookingCodes = collect((array) $this->option('booking_code'))
        ->map(fn ($value) => trim((string) $value))
        ->filter()
        ->values();

    $showId = $this->option('show_id');
    $syncAll = (bool) $this->option('sync-all');
    $dryRun = (bool) $this->option('dry-run');

    $query = Booking::query()
        ->with(['tickets.ticket', 'payments.refunds'])
        ->whereHas('tickets', function ($ticketQuery) use ($syncAll) {
            $ticketQuery->where('status', 'ISSUED');

            if (! $syncAll) {
                $ticketQuery->whereDoesntHave('ticket');
            }
        })
        ->orderBy('id');

    if ($bookingIds->isNotEmpty()) {
        $query->whereIn('id', $bookingIds->all());
    }

    if ($bookingCodes->isNotEmpty()) {
        $query->whereIn('booking_code', $bookingCodes->all());
    }

    if (! empty($showId)) {
        $query->where('show_id', (int) $showId);
    }

    $bookings = $query->get();

    if ($bookings->isEmpty()) {
        $this->warn('Không tìm thấy booking nào cần backfill ticket điện tử.');
        return;
    }

    $this->info('Đã tìm thấy ' . $bookings->count() . ' booking cần xử lý.');
    $headers = ['Booking ID', 'Mã booking', 'Trạng thái', 'BookingTicket ISSUED', 'Đã có e-ticket'];
    $rows = $bookings->map(function (Booking $booking) {
        $issuedTickets = $booking->tickets->where('status', 'ISSUED');

        return [
            $booking->id,
            $booking->booking_code,
            $booking->status,
            $issuedTickets->count(),
            $issuedTickets->filter(fn ($item) => $item->ticket !== null)->count(),
        ];
    })->all();
    $this->table($headers, $rows);

    if ($dryRun) {
        $this->comment('Dry-run: chưa ghi dữ liệu. Bỏ --dry-run để thực hiện backfill thật.');
        return;
    }

    $totals = [
        'bookings' => 0,
        'created' => 0,
        'updated' => 0,
        'unchanged' => 0,
        'skipped' => 0,
    ];

    foreach ($bookings as $booking) {
        DB::transaction(function () use ($booking, $ticketLifecycleService, &$totals) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets.ticket', 'payments.refunds'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            $summary = $ticketLifecycleService->syncForBooking($lockedBooking);

            $totals['bookings']++;
            $totals['created'] += (int) ($summary['created'] ?? 0);
            $totals['updated'] += (int) ($summary['updated'] ?? 0);
            $totals['unchanged'] += (int) ($summary['unchanged'] ?? 0);
            $totals['skipped'] += (int) ($summary['skipped'] ?? 0);
        }, 3);
    }

    $this->newLine();
    $this->info('Backfill hoàn tất.');
    $this->table(['Booking đã xử lý', 'Ticket tạo mới', 'Ticket cập nhật', 'Không đổi', 'Bỏ qua'], [[
        $totals['bookings'],
        $totals['created'],
        $totals['updated'],
        $totals['unchanged'],
        $totals['skipped'],
    ]]);
})->purpose('Backfill vé điện tử cho booking cũ đã phát hành vé nghiệp vụ nhưng chưa có ticket điện tử');


Artisan::command('bookings:expire-stale {--dry-run : Chỉ xem trước, chưa cập nhật dữ liệu}', function () {
    /** @var TicketLifecycleService $ticketLifecycleService */
    $ticketLifecycleService = app(TicketLifecycleService::class);
    $dryRun = (bool) $this->option('dry-run');

    $bookings = Booking::query()
        ->with(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'show', 'payments.refunds'])
        ->where('status', 'PENDING')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->orderBy('expires_at')
        ->get();

    if ($bookings->isEmpty()) {
        $this->info('Không có booking quá hạn nào cần đóng.');
        return;
    }

    $this->table(
        ['Booking ID', 'Mã booking', 'Hết hạn lúc', 'Số ghế', 'Số combo'],
        $bookings->map(fn (Booking $booking) => [
            $booking->id,
            $booking->booking_code,
            optional($booking->expires_at)->format('d/m/Y H:i'),
            $booking->tickets->count(),
            (int) $booking->bookingProducts->sum('qty'),
        ])->all()
    );

    if ($dryRun) {
        $this->comment('Dry-run: chưa có dữ liệu nào được cập nhật.');
        return;
    }

    $totals = ['bookings' => 0, 'inventory_restored' => 0, 'coupons_reopened' => 0];

    foreach ($bookings as $booking) {
        DB::transaction(function () use ($booking, $ticketLifecycleService, &$totals) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets.ticket', 'bookingProducts.product', 'discounts.coupon', 'show', 'payments.refunds'])
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
                $product = $item->product;
                if (! $product || (int) $item->qty <= 0) {
                    continue;
                }

                $locationId = StockMovement::query()
                    ->where('reference_type', 'BOOKING')
                    ->where('reference_id', $lockedBooking->id)
                    ->where('product_id', $product->id)
                    ->value('stock_location_id');

                $location = $locationId
                    ? StockLocation::query()->find($locationId)
                    : StockLocation::query()->firstOrCreate(
                        ['cinema_id' => $lockedBooking->cinema_id, 'code' => 'KIOSK1'],
                        ['name' => 'Quầy F&B chính', 'location_type' => 'KIOSK', 'is_active' => 1]
                    );

                if (! $location) {
                    continue;
                }

                $alreadyRestored = StockMovement::query()
                    ->where('reference_type', 'BOOKING_CANCEL')
                    ->where('reference_id', $lockedBooking->id)
                    ->where('product_id', $product->id)
                    ->exists();

                if ($alreadyRestored) {
                    continue;
                }

                $balance = InventoryBalance::query()->lockForUpdate()->firstOrCreate(
                    ['stock_location_id' => $location->id, 'product_id' => $product->id],
                    ['qty_on_hand' => 0, 'reorder_level' => 5]
                );

                $balance->update([
                    'qty_on_hand' => (int) $balance->qty_on_hand + (int) $item->qty,
                ]);

                StockMovement::create([
                    'stock_location_id' => $location->id,
                    'product_id' => $product->id,
                    'movement_type' => 'IN',
                    'qty_delta' => (int) $item->qty,
                    'reference_type' => 'BOOKING_CANCEL',
                    'reference_id' => $lockedBooking->id,
                    'note' => 'Trả lại tồn kho do booking quá hạn ' . $lockedBooking->booking_code,
                    'created_at' => now(),
                ]);

                $totals['inventory_restored'] += (int) $item->qty;
            }

            foreach ($lockedBooking->discounts as $discount) {
                if ($discount->coupon instanceof Coupon && $discount->coupon->status === 'REDEEMED') {
                    $discount->coupon->update([
                        'status' => $discount->coupon->expires_at && $discount->coupon->expires_at->isPast() ? 'EXPIRED' : 'ACTIVE',
                        'redeemed_at' => null,
                    ]);
                    $totals['coupons_reopened']++;
                }
            }

            $ticketLifecycleService->syncForBooking($lockedBooking->fresh(['tickets.ticket', 'payments.refunds']));

            $show = $lockedBooking->show;
            if ($show && ! in_array((string) $show->status, ['CANCELLED', 'ENDED'], true)) {
                $totalSeats = Seat::query()->where('auditorium_id', $show->auditorium_id)->where('is_active', 1)->count();
                $busySeats = DB::table('booking_tickets')->where('show_id', $show->id)->whereIn('status', ['RESERVED', 'ISSUED'])->count();
                $nextStatus = ($totalSeats > 0 && $busySeats >= $totalSeats) ? 'SOLD_OUT' : 'ON_SALE';
                if (! $show->start_time || now()->lt($show->start_time)) {
                    $show->update(['status' => $nextStatus]);
                }
            }

            $totals['bookings']++;
        }, 3);
    }

    $this->table(['Booking hết hạn', 'SL F&B hoàn tồn', 'Coupon mở lại'], [[
        $totals['bookings'],
        $totals['inventory_restored'],
        $totals['coupons_reopened'],
    ]]);
})->purpose('Đóng các booking PENDING đã quá hạn, trả ghế, hoàn tồn F&B và mở lại coupon nếu cần');
