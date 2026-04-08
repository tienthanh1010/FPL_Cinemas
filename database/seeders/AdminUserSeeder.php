<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = AdminUser::updateOrCreate(
            ['email' => 'admin@cinema.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );

        $adminRoleId = Role::query()->where('code', 'ADMIN')->value('id');
        if ($adminRoleId) {
            $adminUser->roles()->syncWithoutDetaching([$adminRoleId]);
        }
    }
}
