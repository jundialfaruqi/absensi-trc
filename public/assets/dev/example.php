<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePermissionController extends Controller
{
    public function index(Request $request)
    {
        $perPagePerm = (int) $request->input('per_page_perm', 4);
        $perPageRole = (int) $request->input('per_page_role', 10);
        $q = trim((string) $request->input('q', ''));
        $permQuery = Permission::query();
        $roleQuery = Role::query();
        if ($q !== '') {
            $permQuery->where('name', 'like', "%{$q}%");
            $roleQuery->where('name', 'like', "%{$q}%");
        }
        $permissions = $permQuery
            ->orderBy('group')
            ->orderBy('name')
            ->paginate($perPagePerm, ['*'], 'page_perm')
            ->appends($request->query());

        $roles = $roleQuery
            ->withCount(['permissions', 'users'])
            ->with(['permissions:id'])
            ->orderBy('name')
            ->paginate($perPageRole, ['*'], 'page_role')
            ->appends($request->query());

        $totalRoles = Role::count();
        $totalPermissions = Permission::count();
        $newPermissions = Permission::whereDate('created_at', Carbon::today())->count();

        // Count users with specific roles, ensuring we check the correct guard if specified
        // or just use the default guard if it's a common role.
        // For stats, we usually care about the web guard users.
        $userRoleCount = User::role('user', 'web')->count();
        $superAdminRoleCount = User::role('super-admin', 'web')->count();

        $topRoleName = null;
        $topRoleUsers = 0;
        foreach (Role::all() as $r) {
            $cnt = User::role($r->name, $r->guard_name)->count();
            if ($cnt > $topRoleUsers) {
                $topRoleUsers = $cnt;
                $topRoleName = $r->name;
            }
        }
        $stats = [
            'total' => $totalRoles + $totalPermissions,
            'roles' => $totalRoles,
            'permissions' => $totalPermissions,
            'new_permissions' => $newPermissions,
            'user_role_count' => $userRoleCount,
            'superadmin_role_count' => $superAdminRoleCount,
            'top_role_name' => $topRoleName,
            'top_role_users' => $topRoleUsers,
        ];

        $groupNames = Permission::query()
            ->select('group')
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->distinct()
            ->orderBy('group')
            ->pluck('group');

        $permissionGroups = [];
        foreach ($groupNames as $g) {
            $pageName = 'perm_' . Str::slug($g ?? 'ungrouped', '_');
            $query = Permission::query()
                ->when($q !== '', function ($query) use ($q) {
                    $query->where('name', 'like', "%{$q}%");
                })
                ->when($g === null, function ($query) {
                    $query->whereNull('group');
                }, function ($query) use ($g) {
                    $query->where('group', $g);
                })
                ->orderBy('name');
            $paginator = $query->paginate($perPagePerm, ['*'], $pageName)->appends($request->query());
            $permissionGroups[] = [
                'name' => $g,
                'pageName' => $pageName,
                'items' => $paginator,
            ];
        }

        $allPermissions = Permission::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderBy('group')
            ->orderBy('name')
            ->get();

        return view('role_permission.index', compact('permissions', 'roles', 'stats', 'permissionGroups', 'allPermissions'));
    }

    public function storePermission(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'name')],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
        ]);
        $data['guard_name'] = $data['guard_name'] ?? 'web';
        Permission::create($data);
        return redirect()->route('role_permission.index')->with('success', 'Permission baru berhasil dibuat');
    }

    public function updatePermission(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Permission::class, 'name')->ignore($permission->id)],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'group' => ['nullable', 'string', 'max:255'],
        ]);
        $data['guard_name'] = $data['guard_name'] ?? $permission->guard_name;
        $permission->update($data);
        return redirect()->route('role_permission.index')->with('success', 'Permission berhasil diperbarui');
    }

    public function destroyPermission(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('role_permission.index')->with('success', 'Permission berhasil dihapus');
    }

    public function storeRole(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'name')],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer'],
        ]);
        $data['guard_name'] = $data['guard_name'] ?? 'web';
        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'],
            'color' => $data['color'] ?? '#64748b',
        ]);
        if (!empty($data['permission_ids'])) {
            $perms = Permission::whereIn('id', $data['permission_ids'])->get();
            $role->syncPermissions($perms);
        }
        return redirect()->route('role_permission.index')->with('success', 'Role baru berhasil dibuat');
    }

    public function updateRole(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique(Role::class, 'name')->ignore($role->id)],
            'guard_name' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer'],
        ]);
        $role->name = $data['name'];
        $role->color = $data['color'] ?? $role->color ?? '#64748b';
        if (!empty($data['guard_name'])) {
            $role->guard_name = $data['guard_name'];
        }
        $role->save();
        $perms = Permission::whereIn('id', $data['permission_ids'] ?? [])->get();
        $role->syncPermissions($perms);
        return redirect()->route('role_permission.index')->with('success', 'Role berhasil diperbarui');
    }

    public function destroyRole(Role $role)
    {
        $role->delete();
        return redirect()->route('role_permission.index')->with('success', 'Role berhasil dihapus');
    }

    public function suggestPermission(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if ($q === '') {
            return response()->json([
                'roles' => [],
                'permissions' => []
            ]);
        }

        $roles = Role::query()
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(5)
            ->get(['id', 'name', 'guard_name', 'color'])
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'guard' => $r->guard_name,
                    'color' => $r->color ?? '#64748b',
                    'type' => 'role'
                ];
            });

        $permissions = Permission::query()
            ->where('name', 'like', "%{$q}%")
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'group', 'guard_name'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'group' => $p->group,
                    'guard' => $p->guard_name,
                    'type' => 'permission'
                ];
            });

        return response()->json([
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }
}
