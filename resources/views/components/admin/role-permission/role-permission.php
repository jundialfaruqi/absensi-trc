<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Validation\Rule;

new #[Title('Role & Permission')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithPagination;

    // Pagination
    public int $perPageRole   = 10;
    public int $perPagePerm   = 4;

    // Search
    public string $search = '';

    // Role form
    public ?int   $roleId         = null;
    public string $roleName       = '';
    public string $roleGuard      = 'web';
    public string $roleColor      = '#64748b';
    public array  $selectedPermissions = [];

    // Permission form
    public ?int   $permissionId   = null;
    public string $permissionName  = '';
    public string $permissionGroup = '';
    public string $permissionGuard = 'web';

    // Delete confirmation
    public string $deleteType = '';
    public ?int   $deleteId   = null;
    public string $deleteName = '';

    // Modal state (untuk trigger dari PHP)
    public bool $showRoleModal       = false;
    public bool $showPermissionModal = false;
    public bool $showDeleteModal     = false;

    public function mount(): void
    {
        //
    }

    // ─── Computed: Stats ────────────────────────────────────────────────────────

    #[Computed]
    public function stats(): array
    {
        $roles       = Role::withCount(['permissions', 'users'])->get();
        $permissions = Permission::all();
        $topRole     = $roles->sortByDesc('users_count')->first();

        $newPermsThisMonth = Permission::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'roles'               => $roles->count(),
            'permissions'         => $permissions->count(),
            'new_permissions'     => $newPermsThisMonth,
            'total'               => $roles->count() + $permissions->count(),
            'top_role_users'      => $topRole?->users_count ?? 0,
            'top_role_name'       => $topRole?->name ?? '-',
            'user_role_count'     => Role::where('name', 'user')->withCount('users')->first()?->users_count ?? 0,
            'superadmin_role_count' => Role::where('name', 'super-admin')->withCount('users')->first()?->users_count ?? 0,
        ];
    }

    // ─── Computed: Roles (paginated) ────────────────────────────────────────────

    #[Computed]
    public function roles()
    {
        return Role::withCount(['permissions', 'users'])
            ->when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('name')
            ->paginate($this->perPageRole, pageName: 'page_role');
    }

    // ─── Computed: Permission groups (paginated per group) ──────────────────────

    #[Computed]
    public function permissionGroups(): array
    {
        $groups = Permission::when($this->search, fn($q) => $q->where('name', 'like', '%' . $this->search . '%'))
            ->orderBy('group')
            ->orderBy('name')
            ->paginate($this->perPagePerm, pageName: 'page_perm');

        $grouped = [];
        foreach ($groups->groupBy('group') as $groupName => $items) {
            $grouped[] = [
                'name'  => $groupName ?: 'Ungrouped',
                'items' => $items,
            ];
        }

        return $grouped;
    }

    // ─── Computed: All permissions (for role modal checkbox) ────────────────────

    #[Computed]
    public function allPermissions()
    {
        return Permission::orderBy('group')->orderBy('name')->get();
    }

    // ─── Role Actions ────────────────────────────────────────────────────────────

    public function openAddRole(): void
    {
        $this->resetRoleForm();
        $this->showRoleModal = true;
        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function openEditRole(int $id): void
    {
        $role = Role::with('permissions')->findOrFail($id);
        $this->roleId              = $role->id;
        $this->roleName            = $role->name;
        $this->roleGuard           = $role->guard_name;
        $this->roleColor           = $role->color ?? '#64748b';
        $this->selectedPermissions = $role->permissions->pluck('id')->toArray();
        $this->showRoleModal       = true;
        $this->dispatch('open-modal', id: 'role-modal');
    }

    public function saveRole(): void
    {
        $this->validate([
            'roleName'  => ['required', 'string', 'max:255',
                $this->roleId
                    ? Rule::unique('roles', 'name')->ignore($this->roleId)
                    : Rule::unique('roles', 'name')
            ],
            'roleGuard' => 'required|string|in:web,api',
            'roleColor' => 'nullable|string|max:20',
        ], [], [
            'roleName'  => 'Nama',
            'roleGuard' => 'Guard Name',
            'roleColor' => 'Warna',
        ]);

        if ($this->roleId) {
            $role = Role::findOrFail($this->roleId);
            $role->update([
                'name'       => $this->roleName,
                'guard_name' => $this->roleGuard,
                'color'      => $this->roleColor,
            ]);
        } else {
            $role = Role::create([
                'name'       => $this->roleName,
                'guard_name' => $this->roleGuard,
                'color'      => $this->roleColor,
            ]);
        }

        $role->syncPermissions($this->selectedPermissions);

        $this->resetRoleForm();
        $this->dispatch('close-modal', id: 'role-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Role berhasil disimpan.');
    }

    public function deleteRole(int $id): void
    {
        $role = Role::findOrFail($id);
        $role->delete();
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Role berhasil dihapus.');
    }

    private function resetRoleForm(): void
    {
        $this->roleId              = null;
        $this->roleName            = '';
        $this->roleGuard           = 'web';
        $this->roleColor           = '#64748b';
        $this->selectedPermissions = [];
        $this->resetErrorBag();
    }

    // ─── Permission Actions ──────────────────────────────────────────────────────

    public function openAddPermission(): void
    {
        $this->resetPermissionForm();
        $this->showPermissionModal = true;
        $this->dispatch('open-modal', id: 'permission-modal');
    }

    public function openEditPermission(int $id): void
    {
        $permission = Permission::findOrFail($id);
        $this->permissionId    = $permission->id;
        $this->permissionName  = $permission->name;
        $this->permissionGroup = $permission->group ?? '';
        $this->permissionGuard = $permission->guard_name;
        $this->showPermissionModal = true;
        $this->dispatch('open-modal', id: 'permission-modal');
    }

    public function savePermission(): void
    {
        $this->validate([
            'permissionName'  => ['required', 'string', 'max:255',
                $this->permissionId
                    ? Rule::unique('permissions', 'name')->ignore($this->permissionId)
                    : Rule::unique('permissions', 'name')
            ],
            'permissionGroup' => 'nullable|string|max:100',
            'permissionGuard' => 'required|string|in:web,api',
        ], [], [
            'permissionName'  => 'Nama',
            'permissionGroup' => 'Group',
            'permissionGuard' => 'Guard Name',
        ]);

        if ($this->permissionId) {
            Permission::findOrFail($this->permissionId)->update([
                'name'       => $this->permissionName,
                'group'      => $this->permissionGroup ?: null,
                'guard_name' => $this->permissionGuard,
            ]);
        } else {
            Permission::create([
                'name'       => $this->permissionName,
                'group'      => $this->permissionGroup ?: null,
                'guard_name' => $this->permissionGuard,
            ]);
        }

        $this->resetPermissionForm();
        $this->dispatch('close-modal', id: 'permission-modal');
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Permission berhasil disimpan.');
    }

    public function deletePermission(int $id): void
    {
        Permission::findOrFail($id)->delete();
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Permission berhasil dihapus.');
    }

    private function resetPermissionForm(): void
    {
        $this->permissionId    = null;
        $this->permissionName  = '';
        $this->permissionGroup = '';
        $this->permissionGuard = 'web';
        $this->resetErrorBag();
    }

    // ─── Delete Confirmation ─────────────────────────────────────────────────────

    public function confirmDelete(string $type, int $id, string $name): void
    {
        $this->deleteType = $type;
        $this->deleteId   = $id;
        $this->deleteName = $name;
        $this->dispatch('open-modal', id: 'rp-delete-modal');
    }

    public function executeDelete(): void
    {
        if ($this->deleteType === 'role') {
            $this->deleteRole($this->deleteId);
        } elseif ($this->deleteType === 'permission') {
            $this->deletePermission($this->deleteId);
        }

        $this->deleteType = '';
        $this->deleteId   = null;
        $this->deleteName = '';
        $this->dispatch('close-modal', id: 'rp-delete-modal');
    }

    // ─── Search & Pagination Watchers ────────────────────────────────────────────

    public function updatedSearch(): void
    {
        $this->resetPage('page_role');
        $this->resetPage('page_perm');
    }

    public function updatedPerPageRole(): void
    {
        $this->resetPage('page_role');
    }

    public function updatedPerPagePerm(): void
    {
        $this->resetPage('page_perm');
    }
};
