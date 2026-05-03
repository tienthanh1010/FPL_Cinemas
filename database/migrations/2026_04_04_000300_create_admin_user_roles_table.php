<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('admin_user_roles')) {
            Schema::create('admin_user_roles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();

                $table->unique(['admin_user_id', 'role_id'], 'admin_user_roles_unique');
                $table->foreign('admin_user_id')->references('id')->on('admin_users')->cascadeOnDelete();
                $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            });
        }

        $adminRoleId = DB::table('roles')->where('code', 'ADMIN')->value('id');

        if ($adminRoleId) {
            $adminIds = DB::table('admin_users')->pluck('id');
            foreach ($adminIds as $adminId) {
                DB::table('admin_user_roles')->updateOrInsert(
                    ['admin_user_id' => $adminId, 'role_id' => $adminRoleId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_user_roles');
    }
};
