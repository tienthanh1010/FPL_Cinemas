<?php

use App\Models\Booking;
use App\Services\BookingLifecycleService;
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
    /** @var BookingLifecycleService $bookingLifecycleService */
    $bookingLifecycleService = app(BookingLifecycleService::class);
    $dryRun = (bool) $this->option('dry-run');

    $bookings = Booking::query()
        ->with(['tickets', 'bookingProducts', 'discounts.coupon', 'show', 'payments.refunds'])
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
        DB::transaction(function () use ($booking, $bookingLifecycleService, &$totals) {
            /** @var Booking $lockedBooking */
            $lockedBooking = Booking::query()
                ->with(['tickets', 'bookingProducts', 'discounts.coupon', 'show', 'payments.refunds'])
                ->lockForUpdate()
                ->findOrFail($booking->id);

            if ((string) $lockedBooking->status !== 'PENDING') {
                return;
            }

            $productQty = (int) $lockedBooking->bookingProducts->sum('qty');
            $couponCount = $lockedBooking->discounts
                ->filter(fn ($discount) => $discount->coupon && $discount->coupon->status === 'REDEEMED')
                ->count();

            $bookingLifecycleService->expirePendingBooking($lockedBooking, 'AUTO_EXPIRED_CONSOLE');

            $totals['bookings']++;
            $totals['inventory_restored'] += $productQty;
            $totals['coupons_reopened'] += $couponCount;
        }, 3);
    }

    $this->table(['Booking hết hạn', 'SL F&B hoàn tồn', 'Coupon mở lại'], [[
        $totals['bookings'],
        $totals['inventory_restored'],
        $totals['coupons_reopened'],
    ]]);
})->purpose('Đóng các booking PENDING đã quá hạn, trả ghế, hoàn tồn F&B và mở lại coupon nếu cần');
