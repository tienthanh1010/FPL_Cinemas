<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loyalty_tiers')) {
            return;
        }

        $tiers = [
            ['code' => 'MEMBER', 'name' => 'Member', 'min_points' => 0, 'benefits' => json_encode(['earn_rate' => '1 point / 10,000đ'])],
            ['code' => 'SILVER', 'name' => 'Silver', 'min_points' => 100, 'benefits' => json_encode(['priority' => 'Ưu tiên nhận tin khuyến mãi'])],
            ['code' => 'GOLD', 'name' => 'Gold', 'min_points' => 300, 'benefits' => json_encode(['voucher' => 'Nhận ưu đãi theo chiến dịch'])],
            ['code' => 'PLATINUM', 'name' => 'Platinum', 'min_points' => 700, 'benefits' => json_encode(['special' => 'Ưu đãi thành viên thân thiết'])],
        ];

        foreach ($tiers as $tier) {
            DB::table('loyalty_tiers')->updateOrInsert(
                ['code' => $tier['code']],
                $tier + ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('loyalty_tiers')) {
            return;
        }

        DB::table('loyalty_tiers')->whereIn('code', ['MEMBER', 'SILVER', 'GOLD', 'PLATINUM'])->delete();
    }
};
