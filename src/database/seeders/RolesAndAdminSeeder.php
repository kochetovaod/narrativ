<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'super_admin',
            'admin',
        ];

        foreach ($roles as $role) {
            Role::findOrCreate($role, config('permission.default_guard'));
        }

        $adminEmail = env('ADMIN_EMAIL', 'admin@example.com');
        $adminPassword = env('ADMIN_PASSWORD', 'password');

        $admin = User::query()->firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Super Admin',
                'password' => Hash::make($adminPassword),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );

        $admin->assignRole('super_admin');
    }
}
