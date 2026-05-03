<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            if (! Schema::hasColumn('seat_holds', 'owner_token')) {
                $table->char('owner_token', 64)->nullable()->after('hold_token');
                $table->index(['show_id', 'owner_token'], 'idx_seat_holds_owner_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('seat_holds', function (Blueprint $table) {
            if (Schema::hasColumn('seat_holds', 'owner_token')) {
                $table->dropIndex('idx_seat_holds_owner_token');
                $table->dropColumn('owner_token');
            }
        });
    }
};
