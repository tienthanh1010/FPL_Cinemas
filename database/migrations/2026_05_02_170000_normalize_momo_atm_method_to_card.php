<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('payments')) {
            return;
        }

        DB::table('payments')
            ->where('provider', 'MOMO')
            ->where('method', 'ATM_CARD')
            ->update(['method' => 'CARD']);
    }

    public function down(): void
    {
        // Không đổi ngược để tránh vi phạm CHECK constraint chk_payment_method.
    }
};
