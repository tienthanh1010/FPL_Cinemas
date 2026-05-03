<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->where('provider', 'MOMO')
            ->where('method', 'ATM_CARD')
            ->update(['method' => 'CARD']);
    }

    public function down(): void
    {
        // Không rollback về ATM_CARD vì database gốc chỉ cho phép EWALLET, BANK_TRANSFER, CARD, CASH.
    }
};
