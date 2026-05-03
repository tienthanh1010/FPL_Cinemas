<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('customers')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            if (! Schema::hasColumn('customers', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('public_id')->constrained('users')->nullOnDelete();
                $table->unique('user_id');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('customers') || ! Schema::hasColumn('customers', 'user_id')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
