<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $seatTypes = DB::table('seat_types')->orderBy('id')->get();
        $ticketTypes = DB::table('ticket_types')->orderBy('id')->get();
        $profiles = DB::table('pricing_profiles')->orderBy('id')->get();

        if ($seatTypes->isEmpty() || $ticketTypes->isEmpty() || $profiles->isEmpty()) {
            return;
        }

        foreach ($profiles as $profile) {
            DB::table('pricing_profiles')
                ->where('id', $profile->id)
                ->update([
                    'name' => $profile->name ?: 'Giá Vé',
                    'updated_at' => now(),
                ]);

            DB::table('pricing_rules')->where('pricing_profile_id', $profile->id)->delete();

            $rows = [];
            foreach ($seatTypes as $seatType) {
                $basePrice = $this->basePriceForSeatType((string) $seatType->code, (int) $seatType->id);

                foreach ($ticketTypes as $ticketType) {
                    $rows[] = [
                        'pricing_profile_id' => $profile->id,
                        'rule_name' => 'Giá vé ' . $seatType->name,
                        'rule_type' => 'BASE',
                        'valid_from' => null,
                        'valid_to' => null,
                        'day_of_week' => null,
                        'start_time' => null,
                        'end_time' => null,
                        'seat_type_id' => $seatType->id,
                        'ticket_type_id' => $ticketType->id,
                        'price_amount' => $basePrice,
                        'price_mode' => 'FIXED',
                        'adjustment_value' => null,
                        'priority' => 100,
                        'is_active' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            DB::table('pricing_rules')->insert($rows);

            foreach ($seatTypes as $seatType) {
                DB::table('show_prices')
                    ->where('seat_type_id', $seatType->id)
                    ->update([
                        'price_amount' => $this->basePriceForSeatType((string) $seatType->code, (int) $seatType->id),
                        'updated_at' => now(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        // Không khôi phục rule giá cũ để tránh đưa hệ thống quay lại cấu hình sai.
    }

    private function basePriceForSeatType(string $code, int $id): int
    {
        return match (strtoupper($code)) {
            'REGULAR' => 50000,
            'VIP' => 70000,
            'COUPLE', 'SWEETBOX' => 90000,
            default => match ($id) {
                1 => 50000,
                2 => 70000,
                3 => 90000,
                default => 50000,
            },
        };
    }
};
