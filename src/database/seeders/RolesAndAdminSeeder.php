<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('permission.default_guard');

        foreach (Permission::cases() as $permission) {
            PermissionModel::findOrCreate($permission->value, $guard);
        }

        $roles = [
            'super_admin' => Permission::values(),
            'admin' => [
                Permission::AccessAdminPanel->value,
                Permission::PublishContent->value,
                Permission::UnpublishContent->value,
                Permission::PreviewContent->value,
            ],
        ];

        foreach ($roles as $role => $permissions) {
            $createdRole = Role::findOrCreate($role, $guard);
            $createdRole->syncPermissions($permissions);
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
