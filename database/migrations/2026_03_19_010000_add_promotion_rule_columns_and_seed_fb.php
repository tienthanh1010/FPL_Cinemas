<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'day_of_week')) {
                $table->unsignedTinyInteger('day_of_week')->nullable()->after('status');
            }
            if (! Schema::hasColumn('promotions', 'show_start_from')) {
                $table->time('show_start_from')->nullable()->after('day_of_week');
            }
            if (! Schema::hasColumn('promotions', 'show_start_to')) {
                $table->time('show_start_to')->nullable()->after('show_start_from');
            }
            if (! Schema::hasColumn('promotions', 'customer_scope')) {
                $table->string('customer_scope', 16)->nullable()->after('show_start_to');
            }
            if (! Schema::hasColumn('promotions', 'auto_apply')) {
                $table->boolean('auto_apply')->default(false)->after('customer_scope');
            }
            if (! Schema::hasColumn('promotions', 'coupon_required')) {
                $table->boolean('coupon_required')->default(false)->after('auto_apply');
            }
        });

        if (Schema::hasTable('product_categories')) {
            DB::table('product_categories')->insertOrIgnore([
                ['code' => 'POPCORN', 'name' => 'Bắp nước'],
                ['code' => 'COMBO', 'name' => 'Combo'],
                ['code' => 'SNACK', 'name' => 'Snack'],
            ]);
        }

        if (Schema::hasTable('stock_locations') && Schema::hasTable('cinemas')) {
            $cinemaIds = DB::table('cinemas')->pluck('id');
            foreach ($cinemaIds as $cinemaId) {
                DB::table('stock_locations')->insertOrIgnore([
                    'cinema_id' => $cinemaId,
                    'code' => 'KIOSK1',
                    'name' => 'Quầy F&B chính',
                    'location_type' => 'KIOSK',
                    'is_active' => 1,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            foreach (['coupon_required', 'auto_apply', 'customer_scope', 'show_start_to', 'show_start_from', 'day_of_week'] as $column) {
                if (Schema::hasColumn('promotions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
