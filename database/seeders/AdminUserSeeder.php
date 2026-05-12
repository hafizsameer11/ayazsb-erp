<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ERP_ADMIN_EMAIL');
        $password = env('ERP_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            return;
        }

        $admin = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ERP_ADMIN_NAME', 'System Administrator'),
                'username' => env('ERP_ADMIN_USERNAME'),
                'password' => Hash::make($password),
            ]
        );

        $superAdminRole = Role::query()->where('slug', 'super-admin')->firstOrFail();
        $admin->roles()->syncWithoutDetaching([$superAdminRole->id]);
    }
}
