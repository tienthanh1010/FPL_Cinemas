<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now(config('app.timezone'))->format('Y-m-d H:i:s');

        DB::table('shows')
            ->where('status', 'ON_SALE')
            ->where('start_time', '>', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('on_sale_from')
                    ->orWhere('on_sale_from', '>', $now);
            })
            ->update([
                'on_sale_from' => $now,
            ]);
    }

    public function down(): void
    {
        // Không rollback dữ liệu để tránh ghi đè lại thời điểm mở bán đã được người dùng chỉnh sửa.
    }
};
