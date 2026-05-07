<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAccessController extends Controller
{
    public function dashboard(): View
    {
        $this->ensurePermission('admin.dashboard.view');

        return view('erp.admin.dashboard', $this->shared('Access Management'));
    }

    public function users(): View
    {
        $this->ensurePermission('admin.users.view');

        return view('erp.admin.users', [
            ...$this->shared('Users & Roles'),
            'users' => User::query()->with('roles')->orderBy('name')->get(),
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function updateUserRoles(Request $request, User $user): RedirectResponse
    {
        $this->ensurePermission('admin.users.edit');

        $roleIds = array_map('intval', $request->input('role_ids', []));
        $superAdminRoleId = Role::query()->where('slug', 'super-admin')->value('id');

        if ($request->user()?->is($user) && ! in_array($superAdminRoleId, $roleIds, true)) {
            return back()->with('error', 'You cannot remove your own super admin role.');
        }

        $user->roles()->sync($roleIds);

        return back()->with('status', 'User roles updated successfully.');
    }

    public function roles(): View
    {
        $this->ensurePermission('admin.roles.view');

        return view('erp.admin.roles', [
            ...$this->shared('Roles & Permissions'),
            'roles' => Role::query()->with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('name')->get(),
        ]);
    }

    public function storeRole(Request $request): RedirectResponse
    {
        $this->ensurePermission('admin.roles.create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name'],
            'slug' => ['required', 'string', 'max:120', 'alpha_dash', 'unique:roles,slug'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        Role::query()->create($data);

        return back()->with('status', 'Role created successfully.');
    }

    public function updateRole(Request $request, Role $role): RedirectResponse
    {
        $this->ensurePermission('admin.roles.edit');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:roles,name,' . $role->id],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($data);

        return back()->with('status', 'Role updated successfully.');
    }

    public function deleteRole(Request $request, Role $role): RedirectResponse
    {
        $this->ensurePermission('admin.roles.delete');

        if ($role->slug === 'super-admin') {
            return back()->with('error', 'Super Admin role cannot be deleted.');
        }

        if ($request->user()?->roles()->where('roles.id', $role->id)->exists()) {
            return back()->with('error', 'You cannot delete a role assigned to yourself.');
        }

        $role->delete();

        return back()->with('status', 'Role deleted successfully.');
    }

    public function updateRolePermissions(Request $request, Role $role): RedirectResponse
    {
        $this->ensurePermission('admin.roles.edit');

        $permissionIds = array_map('intval', $request->input('permission_ids', []));
        $role->permissions()->sync($permissionIds);

        return back()->with('status', 'Role permissions updated.');
    }

    /**
     * @return array<string, mixed>
     */
    private function shared(string $pageTitle): array
    {
        return [
            'activeModule' => 'admin',
            'pageTitle' => $pageTitle,
            'breadcrumbs' => [
                ['label' => 'Main menu', 'route' => 'erp.accounts.dashboard'],
                ['label' => 'Admin', 'route' => 'erp.admin.dashboard'],
                ['label' => $pageTitle],
            ],
        ];
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth()->user()?->hasPermission($permission), 403);
    }
}
