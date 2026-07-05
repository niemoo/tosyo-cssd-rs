<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $sortBy  = $request->get('sort', 'name');
        $sortDir = $request->get('direction', 'asc');

        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'name';
        }

        $roles = Role::withCount(['permissions', 'users'])
                     ->orderBy($sortBy, $sortDir)
                     ->paginate(10)
                     ->withQueryString();

        $stats = [
            'total'       => Role::count(),
            'permissions' => Permission::count(),
            'users'       => \App\Models\User::count(),
        ];

        return view('roles.index', compact('roles', 'stats', 'sortBy', 'sortDir'));
    }

    public function create(): View
    {
        [$permissions, $actions] = $this->getPermissionsAndActions();
        return view('roles.create', compact('permissions', 'actions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create(['name' => $request->name, 'guard_name' => 'web']);

        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        }

        return redirect()->route('roles.index')
                         ->with('success', "Role '{$role->name}' berhasil ditambahkan.");
    }

    public function show(Role $role): View
    {
        $role->load('permissions');
        [$permissions, $actions] = $this->getPermissionsAndActions();
        $users = $role->users()->take(5)->get();

        return view('roles.show', compact('role', 'permissions', 'actions', 'users'));
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');
        [$permissions, $actions] = $this->getPermissionsAndActions();
        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'permissions', 'actions', 'rolePermissionIds'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update(['name' => $request->name]);

        if ($request->filled('permissions')) {
            $permissions = Permission::whereIn('id', $request->permissions)->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')
                         ->with('success', "Role '{$role->name}' berhasil diperbarui.");
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')
                             ->with('error', "Role '{$role->name}' tidak dapat dihapus karena masih digunakan oleh {$role->users()->count()} pengguna.");
        }

        $role->delete();

        return redirect()->route('roles.index')
                         ->with('success', "Role '{$role->name}' berhasil dihapus.");
    }

    // Helper method — DRY, tidak perlu repeat di setiap method
    private function getPermissionsAndActions(): array
    {
        $allPermissions = Permission::orderBy('name')->get();

        $permissions = $allPermissions->groupBy(function ($permission) {
            return explode('.', $permission->name)[0];
        })->map(function ($modulePermissions) {
            return $modulePermissions->keyBy(function ($permission) {
                $parts = explode('.', $permission->name);
                return $parts[1] ?? $permission->name;
            });
        });

        $actionOrder = ['view', 'create', 'edit', 'delete', 'export', 'import', 'toggle-active', 'restore'];

        $actions = $allPermissions->map(function ($permission) {
            $parts = explode('.', $permission->name);
            return $parts[1] ?? $permission->name;
        })->unique()->sortBy(function ($action) use ($actionOrder) {
            $index = array_search($action, $actionOrder);
            return $index !== false ? $index : 99;
        })->values();

        return [$permissions, $actions];
    }
}