<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cinemas')) {
            DB::table('cinemas')
                ->where('name', 'FPL Cinema')
                ->orWhere('id', 1)
                ->update([
                    'phone' => '0393312307',
                    'email' => 'kientr2307@gmail.com',
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('cinema_chains')) {
            DB::table('cinema_chains')
                ->where('name', 'FPL Cinema')
                ->orWhere('id', 1)
                ->update([
                    'hotline' => '0393312307',
                    'email' => 'kientr2307@gmail.com',
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Không tự khôi phục về dữ liệu liên hệ cũ để tránh ghi đè thông tin thật của rạp.
    }
};
