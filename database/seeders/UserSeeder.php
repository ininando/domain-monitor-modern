<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Akun default. Ganti email/password setelah instalasi.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'superadmin@tomohonkota.go.id'],
            ['name' => 'Super Admin Diskominfo', 'password' => 'password', 'role' => UserRole::SuperAdmin],
        );

        User::updateOrCreate(
            ['email' => 'admin@tomohonkota.go.id'],
            ['name' => 'Admin Diskominfo', 'password' => 'password', 'role' => UserRole::Admin],
        );
    }
}
