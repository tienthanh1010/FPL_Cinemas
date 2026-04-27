<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasColumn('customers', 'account_status')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->string('account_status', 16)->default('ACTIVE')->after('city');
            });
        }

        foreach ([
            ['code' => 'ADMIN', 'name' => 'Admin'],
            ['code' => 'MANAGER', 'name' => 'Quản lý rạp'],
            ['code' => 'TICKET_COUNTER', 'name' => 'Nhân viên quầy vé'],
            ['code' => 'TICKET_CHECKIN', 'name' => 'Nhân viên soát vé'],
            ['code' => 'FNB', 'name' => 'Nhân viên bắp nước'],
            ['code' => 'TECHNICIAN', 'name' => 'Kỹ thuật / bảo trì'],
        ] as $role) {
            DB::table('roles')->updateOrInsert(['code' => $role['code']], $role + ['created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'account_status')) {
            Schema::table('customers', fn (Blueprint $table) => $table->dropColumn('account_status'));
        }
    }
};
