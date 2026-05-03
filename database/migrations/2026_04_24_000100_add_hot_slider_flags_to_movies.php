<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (! Schema::hasColumn('movies', 'is_hot')) {
                $table->boolean('is_hot')->default(false)->after('status');
            }
            if (! Schema::hasColumn('movies', 'is_on_slider')) {
                $table->boolean('is_on_slider')->default(false)->after('is_hot');
            }
        });

        $activeMovieIds = DB::table('movies')
            ->where('status', 'ACTIVE')
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->limit(3)
            ->pluck('id');

        if ($activeMovieIds->isNotEmpty()) {
            if (! DB::table('movies')->where('is_hot', true)->exists()) {
                DB::table('movies')->whereIn('id', $activeMovieIds)->update(['is_hot' => true]);
            }

            if (! DB::table('movies')->where('is_on_slider', true)->exists()) {
                DB::table('movies')->whereIn('id', $activeMovieIds)->update(['is_on_slider' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('movies', function (Blueprint $table) {
            if (Schema::hasColumn('movies', 'is_on_slider')) {
                $table->dropColumn('is_on_slider');
            }
            if (Schema::hasColumn('movies', 'is_hot')) {
                $table->dropColumn('is_hot');
            }
        });
    }
};
