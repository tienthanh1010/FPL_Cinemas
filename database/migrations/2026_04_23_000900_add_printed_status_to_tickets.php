<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('tickets', 'printed_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dateTime('printed_at')->nullable()->after('used_at');
            });
        }

        try {
            DB::statement("ALTER TABLE tickets DROP CHECK chk_ticket_status");
        } catch (Throwable $e) {
            // ignore if constraint does not exist or is already updated
        }

        DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_ticket_status CHECK (status IN ('ISSUED','USED','PRINTED','VOID','REFUNDED'))");
    }

    public function down(): void
    {
        DB::table('tickets')->where('status', 'PRINTED')->update([
            'status' => 'USED',
            'printed_at' => null,
        ]);

        try {
            DB::statement("ALTER TABLE tickets DROP CHECK chk_ticket_status");
        } catch (Throwable $e) {
            // ignore if constraint does not exist
        }

        DB::statement("ALTER TABLE tickets ADD CONSTRAINT chk_ticket_status CHECK (status IN ('ISSUED','USED','VOID','REFUNDED'))");

        if (Schema::hasColumn('tickets', 'printed_at')) {
            Schema::table('tickets', function (Blueprint $table) {
                $table->dropColumn('printed_at');
            });
        }
    }
};
