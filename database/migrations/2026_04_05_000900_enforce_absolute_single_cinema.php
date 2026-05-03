<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cinemas')) {
            return;
        }

        DB::transaction(function (): void {
            $primaryChainId = $this->ensurePrimaryChain();
            $primaryCinemaId = $this->ensurePrimaryCinema($primaryChainId);

            $secondaryCinemaIds = DB::table('cinemas')
                ->where('id', '!=', $primaryCinemaId)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            if (! empty($secondaryCinemaIds)) {
                $this->reassignCinemaReferences($primaryCinemaId, $secondaryCinemaIds);
                $this->mergePromotionCinemaPivot($primaryCinemaId, $secondaryCinemaIds);
                $this->mergeStockLocations($primaryCinemaId, $secondaryCinemaIds);
            } else {
                $this->mergeStockLocations($primaryCinemaId, []);
            }

            $this->renumberAuditoriums($primaryCinemaId);
            $this->normalizePrimaryCinema($primaryCinemaId, $primaryChainId);

            if (! empty($secondaryCinemaIds)) {
                DB::table('cinemas')->whereIn('id', $secondaryCinemaIds)->delete();
            }

            if (Schema::hasTable('cinema_chains')) {
                DB::table('cinema_chains')
                    ->where('id', '!=', $primaryChainId)
                    ->delete();
            }
        }, 5);
    }

    public function down(): void
    {
        // Không khôi phục lại dữ liệu multi-cinema sau khi đã gộp về 1 rạp.
    }

    private function ensurePrimaryChain(): int
    {
        if (! Schema::hasTable('cinema_chains')) {
            return 1;
        }

        $chainId = DB::table('cinema_chains')
            ->where('name', 'FPL Cinema')
            ->orderBy('id')
            ->value('id');

        if (! $chainId) {
            $chainId = DB::table('cinema_chains')->orderBy('id')->value('id');
        }

        if (! $chainId) {
            $chainId = DB::table('cinema_chains')->insertGetId([
                'public_id' => (string) Str::ulid(),
                'chain_code' => 'fpl',
                'name' => 'FPL Cinema',
                'legal_name' => 'CÔNG TY FPL CINEMA',
                'tax_code' => null,
                'hotline' => '1900 1234',
                'email' => 'support@fplcinema.local',
                'website' => null,
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        DB::table('cinema_chains')
            ->where('id', $chainId)
            ->update([
                'chain_code' => 'fpl',
                'name' => 'FPL Cinema',
                'legal_name' => DB::raw("COALESCE(NULLIF(legal_name, ''), 'CÔNG TY FPL CINEMA')"),
                'hotline' => DB::raw("COALESCE(NULLIF(hotline, ''), '1900 1234')"),
                'email' => DB::raw("COALESCE(NULLIF(email, ''), 'support@fplcinema.local')"),
                'status' => 'ACTIVE',
                'updated_at' => now(),
            ]);

        return (int) $chainId;
    }

    private function ensurePrimaryCinema(int $primaryChainId): int
    {
        $primaryCinemaId = DB::table('cinemas')
            ->where('name', 'FPL Cinema')
            ->orderBy('id')
            ->value('id');

        if (! $primaryCinemaId) {
            $primaryCinemaId = DB::table('cinemas')->orderBy('id')->value('id');
        }

        if (! $primaryCinemaId) {
            $primaryCinemaId = DB::table('cinemas')->insertGetId([
                'public_id' => (string) Str::ulid(),
                'chain_id' => $primaryChainId,
                'cinema_code' => 'FPL',
                'name' => 'FPL Cinema',
                'phone' => '1900 1234',
                'email' => 'support@fplcinema.local',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'address_line' => 'FPL Cinema',
                'ward' => null,
                'district' => null,
                'province' => null,
                'country_code' => 'VN',
                'latitude' => null,
                'longitude' => null,
                'opening_hours' => json_encode([
                    'mon' => '09:00-23:00',
                    'tue' => '09:00-23:00',
                    'wed' => '09:00-23:00',
                    'thu' => '09:00-23:00',
                    'fri' => '09:00-24:00',
                    'sat' => '09:00-24:00',
                    'sun' => '09:00-23:00',
                ], JSON_UNESCAPED_UNICODE),
                'status' => 'ACTIVE',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $primaryCinemaId;
    }

    private function normalizePrimaryCinema(int $primaryCinemaId, int $primaryChainId): void
    {
        $current = DB::table('cinemas')->where('id', $primaryCinemaId)->first();

        DB::table('cinemas')
            ->where('id', $primaryCinemaId)
            ->update([
                'chain_id' => $primaryChainId,
                'cinema_code' => ! empty($current?->cinema_code) ? $current->cinema_code : 'FPL',
                'name' => 'FPL Cinema',
                'phone' => ! empty($current?->phone) ? $current->phone : '1900 1234',
                'email' => ! empty($current?->email) ? $current->email : 'support@fplcinema.local',
                'timezone' => 'Asia/Ho_Chi_Minh',
                'country_code' => ! empty($current?->country_code) ? $current->country_code : 'VN',
                'status' => 'ACTIVE',
                'updated_at' => now(),
            ]);
    }

    /**
     * @param  array<int>  $secondaryCinemaIds
     */
    private function reassignCinemaReferences(int $primaryCinemaId, array $secondaryCinemaIds): void
    {
        $simpleTables = [
            'auditoriums' => 'cinema_id',
            'bookings' => 'cinema_id',
            'equipment' => 'cinema_id',
            'maintenance_requests' => 'cinema_id',
            'pricing_profiles' => 'cinema_id',
            'product_prices' => 'cinema_id',
            'purchase_orders' => 'cinema_id',
            'staff' => 'cinema_id',
            'staff_shifts' => 'cinema_id',
        ];

        foreach ($simpleTables as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            DB::table($table)
                ->whereIn($column, $secondaryCinemaIds)
                ->update([$column => $primaryCinemaId]);
        }

        if (Schema::hasTable('cinemas') && Schema::hasColumn('cinemas', 'chain_id')) {
            DB::table('cinemas')
                ->whereIn('id', $secondaryCinemaIds)
                ->update(['chain_id' => DB::table('cinemas')->where('id', $primaryCinemaId)->value('chain_id')]);
        }
    }

    /**
     * @param  array<int>  $secondaryCinemaIds
     */
    private function mergePromotionCinemaPivot(int $primaryCinemaId, array $secondaryCinemaIds): void
    {
        if (! Schema::hasTable('promotion_cinemas') || empty($secondaryCinemaIds)) {
            return;
        }

        $promotionIds = DB::table('promotion_cinemas')
            ->whereIn('cinema_id', $secondaryCinemaIds)
            ->pluck('promotion_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        foreach ($promotionIds as $promotionId) {
            $exists = DB::table('promotion_cinemas')
                ->where('promotion_id', $promotionId)
                ->where('cinema_id', $primaryCinemaId)
                ->exists();

            if (! $exists) {
                DB::table('promotion_cinemas')->insert([
                    'promotion_id' => $promotionId,
                    'cinema_id' => $primaryCinemaId,
                ]);
            }
        }

        DB::table('promotion_cinemas')
            ->whereIn('cinema_id', $secondaryCinemaIds)
            ->delete();
    }

    /**
     * @param  array<int>  $secondaryCinemaIds
     */
    private function mergeStockLocations(int $primaryCinemaId, array $secondaryCinemaIds): void
    {
        if (! Schema::hasTable('stock_locations')) {
            return;
        }

        $primaryLocationId = DB::table('stock_locations')
            ->where('cinema_id', $primaryCinemaId)
            ->orderBy('id')
            ->value('id');

        if (! $primaryLocationId) {
            $primaryLocationId = DB::table('stock_locations')->insertGetId([
                'cinema_id' => $primaryCinemaId,
                'code' => 'KIOSK1',
                'name' => 'Quầy F&B chính',
                'location_type' => 'KIOSK',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $otherLocationQuery = DB::table('stock_locations')
            ->where('cinema_id', $primaryCinemaId)
            ->where('id', '!=', $primaryLocationId);

        if (! empty($secondaryCinemaIds)) {
            $otherLocationQuery->orWhereIn('cinema_id', $secondaryCinemaIds);
        }

        $otherLocationIds = $otherLocationQuery
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if (empty($otherLocationIds)) {
            DB::table('stock_locations')->where('id', $primaryLocationId)->update([
                'cinema_id' => $primaryCinemaId,
                'code' => 'KIOSK1',
                'name' => 'Quầy F&B chính',
                'location_type' => 'KIOSK',
                'is_active' => 1,
                'updated_at' => now(),
            ]);

            return;
        }

        if (Schema::hasTable('inventory_balances')) {
            $balances = DB::table('inventory_balances')
                ->whereIn('stock_location_id', $otherLocationIds)
                ->orderBy('id')
                ->get();

            foreach ($balances as $balance) {
                $primaryBalance = DB::table('inventory_balances')
                    ->where('stock_location_id', $primaryLocationId)
                    ->where('product_id', $balance->product_id)
                    ->first();

                if ($primaryBalance) {
                    DB::table('inventory_balances')
                        ->where('id', $primaryBalance->id)
                        ->update([
                            'qty_on_hand' => (int) $primaryBalance->qty_on_hand + (int) $balance->qty_on_hand,
                            'reorder_level' => max((int) $primaryBalance->reorder_level, (int) $balance->reorder_level),
                            'updated_at' => now(),
                        ]);

                    DB::table('inventory_balances')->where('id', $balance->id)->delete();
                } else {
                    DB::table('inventory_balances')
                        ->where('id', $balance->id)
                        ->update([
                            'stock_location_id' => $primaryLocationId,
                            'updated_at' => now(),
                        ]);
                }
            }
        }

        if (Schema::hasTable('stock_movements')) {
            DB::table('stock_movements')
                ->whereIn('stock_location_id', $otherLocationIds)
                ->update(['stock_location_id' => $primaryLocationId]);
        }

        DB::table('stock_locations')
            ->where('id', $primaryLocationId)
            ->update([
                'cinema_id' => $primaryCinemaId,
                'code' => 'KIOSK1',
                'name' => 'Quầy F&B chính',
                'location_type' => 'KIOSK',
                'is_active' => 1,
                'updated_at' => now(),
            ]);

        DB::table('stock_locations')->whereIn('id', $otherLocationIds)->delete();
    }

    private function renumberAuditoriums(int $primaryCinemaId): void
    {
        if (! Schema::hasTable('auditoriums')) {
            return;
        }

        $auditoriums = DB::table('auditoriums')
            ->where('cinema_id', $primaryCinemaId)
            ->orderBy('id')
            ->get(['id']);

        foreach ($auditoriums as $index => $auditorium) {
            $number = $index + 1;

            DB::table('auditoriums')
                ->where('id', $auditorium->id)
                ->update([
                    'auditorium_code' => 'AUD' . $number,
                    'name' => 'Phòng ' . $number,
                    'updated_at' => now(),
                ]);
        }
    }
};
