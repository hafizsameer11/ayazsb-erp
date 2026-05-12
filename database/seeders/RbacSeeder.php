<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\PermissionRegistry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RbacSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'Super Admin' => [
                'slug' => 'super-admin',
                'description' => 'Full system access',
            ],
            'Operator' => [
                'slug' => 'operator',
                'description' => 'Operational user with restricted actions',
            ],
            'Viewer' => [
                'slug' => 'viewer',
                'description' => 'Read-only user',
            ],
        ];

        foreach ($roles as $name => $meta) {
            Role::query()->updateOrCreate(
                ['slug' => $meta['slug']],
                ['name' => $name, 'description' => $meta['description']]
            );
        }

        $permissionIds = [];

        foreach (PermissionRegistry::allPermissions() as $permissionName) {
            $permission = Permission::query()->updateOrCreate(
                ['name' => $permissionName],
                ['description' => str_replace('.', ' ', $permissionName)]
            );

            $permissionIds[] = $permission->id;
        }

        $superAdminRole = Role::query()->where('slug', 'super-admin')->firstOrFail();
        $operatorRole = Role::query()->where('slug', 'operator')->firstOrFail();
        $viewerRole = Role::query()->where('slug', 'viewer')->firstOrFail();

        $superAdminRole->permissions()->sync($permissionIds);

        $operatorPermissions = Permission::query()
            ->where('name', 'like', '%.view')
            ->orWhere('name', 'like', '%.create')
            ->orWhere('name', 'like', '%.edit')
            ->orWhere('name', 'like', '%.post')
            ->orWhere('name', 'like', '%.print')
            ->where('name', 'not like', 'admin.%')
            ->pluck('id')
            ->all();
        $operatorRole->permissions()->sync($operatorPermissions);

        $viewerPermissions = Permission::query()
            ->where('name', 'like', '%.view')
            ->where('name', 'not like', 'admin.%')
            ->pluck('id')
            ->all();
        $viewerRole->permissions()->sync($viewerPermissions);

        $adminEmail = env('ERP_ADMIN_EMAIL');
        $adminPassword = env('ERP_ADMIN_PASSWORD');

        if ($adminEmail && $adminPassword) {
            $adminUser = User::query()->updateOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => env('ERP_ADMIN_NAME', 'System Administrator'),
                    'username' => env('ERP_ADMIN_USERNAME'),
                    'password' => Hash::make($adminPassword),
                ]
            );
            $adminUser->roles()->syncWithoutDetaching([$superAdminRole->id]);
        }
    }
}
