<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getSchemaBuilder()->hasTable('payments')) {
            DB::table('payments')
                ->where('provider', 'MOMO')
                ->whereIn('method', ['ATM_CARD', 'EWALLET'])
                ->update(['method' => 'CARD']);
        }
    }

    public function down(): void
    {
        // Khong rollback du lieu thanh toan de tranh lam sai lich su giao dich.
    }
};
