<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $regularId = DB::table('seat_types')->where('code', 'REGULAR')->value('id');
        $vipId = DB::table('seat_types')->where('code', 'VIP')->value('id');
        $coupleId = DB::table('seat_types')->where('code', 'COUPLE')->value('id');

        if (! $regularId || ! $vipId || ! $coupleId) {
            return;
        }

        DB::table('seats')
            ->whereIn('row_label', ['A', 'B', 'C', 'D', 'E'])
            ->update(['seat_type_id' => $regularId]);

        DB::table('seats')
            ->whereIn('row_label', ['F', 'G', 'H'])
            ->update(['seat_type_id' => $vipId]);

        DB::table('seats')
            ->whereIn('row_label', ['I', 'J'])
            ->update(['seat_type_id' => $coupleId]);
    }

    public function down(): void
    {
        $regularId = DB::table('seat_types')->where('code', 'REGULAR')->value('id');
        $vipId = DB::table('seat_types')->where('code', 'VIP')->value('id');
        $coupleId = DB::table('seat_types')->where('code', 'COUPLE')->value('id');

        if (! $regularId || ! $vipId || ! $coupleId) {
            return;
        }

        DB::table('seats')
            ->whereIn('row_label', ['A', 'B', 'C', 'D', 'G', 'H', 'I'])
            ->update(['seat_type_id' => $regularId]);

        DB::table('seats')
            ->whereIn('row_label', ['E', 'F'])
            ->update(['seat_type_id' => $vipId]);

        DB::table('seats')
            ->where('row_label', 'J')
            ->where('col_number', '<=', 4)
            ->update(['seat_type_id' => $coupleId]);

        DB::table('seats')
            ->where('row_label', 'J')
            ->where('col_number', '>', 4)
            ->update(['seat_type_id' => $regularId]);
    }
};
