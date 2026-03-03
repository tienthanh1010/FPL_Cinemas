<?php

namespace Database\Seeders;

use App\Models\AdminUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        AdminUser::updateOrCreate(
            ['email' => 'admin@cinema.local'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('admin123'),
            ]
        );
    }
}
