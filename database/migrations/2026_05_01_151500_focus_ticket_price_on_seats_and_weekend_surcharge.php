<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ticket_types')) {
            return;
        }

        $defaultTicketTypeId = DB::table('ticket_types')->orderBy('id')->value('id');
        if (! $defaultTicketTypeId) {
            $defaultTicketTypeId = DB::table('ticket_types')->insertGetId([
                'code' => 'SEAT_BASED',
                'name' => 'Giá theo ghế',
                'description' => 'Giá vé cố định theo loại ghế',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('ticket_types')
                ->where('id', $defaultTicketTypeId)
                ->update([
                    'code' => 'SEAT_BASED',
                    'name' => 'Giá theo ghế',
                    'description' => 'Giá vé cố định theo loại ghế',
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('pricing_rules')) {
            DB::table('pricing_rules')
                ->where('ticket_type_id', '<>', $defaultTicketTypeId)
                ->delete();

            DB::table('pricing_rules')
                ->update(['ticket_type_id' => $defaultTicketTypeId, 'updated_at' => now()]);

            $seatPrices = $this->seatPrices();
            foreach ($seatPrices as $seatTypeId => $price) {
                DB::table('pricing_rules')
                    ->where('rule_type', 'BASE')
                    ->where('seat_type_id', $seatTypeId)
                    ->update([
                        'price_amount' => $price,
                        'price_mode' => 'FIXED',
                        'adjustment_value' => null,
                        'updated_at' => now(),
                    ]);
            }
        }

        if (Schema::hasTable('show_prices')) {
            foreach ($this->seatPrices() as $seatTypeId => $price) {
                DB::table('show_prices')
                    ->where('seat_type_id', $seatTypeId)
                    ->where('ticket_type_id', $defaultTicketTypeId)
                    ->update([
                        'price_amount' => $price,
                        'currency' => 'VND',
                        'is_active' => 1,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('show_prices')
                ->where('ticket_type_id', '<>', $defaultTicketTypeId)
                ->delete();
        }
    }

    public function down(): void
    {
        // Không khôi phục lại HSSV / Trẻ em / Người lớn để tránh làm sai giá vé đã chốt theo ghế.
    }

    private function seatPrices(): array
    {
        $prices = [];

        if (! Schema::hasTable('seat_types')) {
            return $prices;
        }

        $seatTypes = DB::table('seat_types')->get(['id', 'code', 'name']);
        foreach ($seatTypes as $seatType) {
            $code = strtoupper((string) ($seatType->code ?? ''));
            $name = mb_strtolower((string) ($seatType->name ?? ''));

            $prices[(int) $seatType->id] = match (true) {
                $code === 'VIP' || str_contains($name, 'vip') => 70000,
                in_array($code, ['COUPLE', 'SWEETBOX'], true) || str_contains($name, 'đôi') || str_contains($name, 'doi') => 90000,
                default => 50000,
            };
        }

        return $prices;
    }
};
