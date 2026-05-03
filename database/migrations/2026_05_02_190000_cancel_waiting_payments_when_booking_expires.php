<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('bookings') || ! DB::getSchemaBuilder()->hasTable('payments') || ! DB::getSchemaBuilder()->hasTable('booking_tickets')) {
            return;
        }

        DB::table('booking_tickets')
            ->join('bookings', 'bookings.id', '=', 'booking_tickets.booking_id')
            ->whereIn('bookings.status', ['EXPIRED', 'CANCELLED'])
            ->whereIn('booking_tickets.status', ['RESERVED', 'ISSUED'])
            ->update(['booking_tickets.status' => 'CANCELLED']);

        DB::table('payments')
            ->join('bookings', 'bookings.id', '=', 'payments.booking_id')
            ->whereIn('bookings.status', ['EXPIRED', 'CANCELLED'])
            ->whereIn('payments.status', ['INITIATED', 'AUTHORIZED'])
            ->update([
                'payments.status' => 'CANCELLED',
                'payments.response_payload' => DB::raw("JSON_MERGE_PATCH(COALESCE(payments.response_payload, JSON_OBJECT()), JSON_OBJECT('status', 'CANCELLED', 'message', 'Booking đã hết hạn hoặc đã bị huỷ, giao dịch chờ thanh toán đã được đóng để nhả ghế.'))"),
            ]);
    }

    public function down(): void
    {
        // Không đảo ngược trạng thái để tránh mở lại giao dịch/ghế đã hết hạn.
    }
};
