<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            if (! Schema::hasColumn('shows', 'pricing_profile_id')) {
                $table->unsignedBigInteger('pricing_profile_id')->nullable()->after('movie_version_id');
            }
        });

        Schema::table('pricing_rules', function (Blueprint $table) {
            if (! Schema::hasColumn('pricing_rules', 'rule_name')) {
                $table->string('rule_name')->nullable()->after('pricing_profile_id');
            }
            if (! Schema::hasColumn('pricing_rules', 'rule_type')) {
                $table->string('rule_type', 16)->default('BASE')->after('rule_name');
            }
            if (! Schema::hasColumn('pricing_rules', 'valid_from')) {
                $table->date('valid_from')->nullable()->after('rule_type');
            }
            if (! Schema::hasColumn('pricing_rules', 'valid_to')) {
                $table->date('valid_to')->nullable()->after('valid_from');
            }
            if (! Schema::hasColumn('pricing_rules', 'price_mode')) {
                $table->string('price_mode', 20)->default('FIXED')->after('price_amount');
            }
            if (! Schema::hasColumn('pricing_rules', 'adjustment_value')) {
                $table->bigInteger('adjustment_value')->nullable()->after('price_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('shows', function (Blueprint $table) {
            if (Schema::hasColumn('shows', 'pricing_profile_id')) {
                $table->dropColumn('pricing_profile_id');
            }
        });

        Schema::table('pricing_rules', function (Blueprint $table) {
            foreach (['rule_name', 'rule_type', 'valid_from', 'valid_to', 'price_mode', 'adjustment_value'] as $column) {
                if (Schema::hasColumn('pricing_rules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
