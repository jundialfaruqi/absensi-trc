<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Role &amp; Permission</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola role dan permission</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('role-permission') }}">
                        <span class="text-base-content font-bold">Role &amp; Permission</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>


    {{-- Toast ditangani oleh global toast di layout (app-scripts.blade.php #global-toast) --}}

    {{-- ─── Stats Cards ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4 mb-6">
        {{-- Total --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="card-title text-sm text-base-content/60 font-medium">Total Role &amp; Permission
                        </h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-2xl font-bold">{{ $this->stats['total'] ?? 0 }}</span>
                            <span class="text-xs text-base-content/50">Role &amp; Permission</span>
                        </div>
                    </div>
                    <div class="p-2 bg-base-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- Top Role --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="card-title text-sm text-base-content/60 font-medium">Role Terbanyak Digunakan
                        </h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-2xl font-bold">{{ $this->stats['top_role_users'] ?? 0 }}</span>
                            <span class="text-xs text-success">Pengguna Role
                                {{ ucfirst($this->stats['top_role_name'] ?? '-') }}</span>
                        </div>
                    </div>
                    <div class="p-2 bg-base-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M3 3v18h18M9 13v5m4-9v9m4-13v13" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- Role User --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="card-title text-sm text-base-content/60 font-medium">Role Admin OPD</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-2xl font-bold">{{ $this->stats['opd_role_count'] ?? 0 }}</span>
                            <span class="text-xs text-warning">Pengguna</span>
                        </div>
                    </div>
                    <div class="p-2 bg-base-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        {{-- Super Admin --}}
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="card-title text-sm text-base-content/60 font-medium">Role Super Admin</h2>
                        <div class="flex items-center gap-2 mt-2">
                            <span class="text-2xl font-bold">{{ $this->stats['superadmin_role_count'] ?? 0 }}</span>
                            <span class="text-xs text-error">Pengguna</span>
                        </div>
                    </div>
                    <div class="p-2 bg-base-200 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 12l2 2 4-4M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                {{-- Per-page role --}}
                <div class="join">
                    <span class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show
                        Roles</span>
                    <select wire:model.live="perPageRole" class="select join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                {{-- Search --}}
                <div class="relative w-full sm:w-auto">
                    <input id="rp-search-input" type="text" placeholder="Search..."
                        wire:model.live.debounce.400ms="search"
                        class="input input-bordered placeholder:text-base-content/60 w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" id="btn-add-permission" wire:click="openAddPermission"
                wire:loading.attr="disabled" class="btn btn-base-300 gap-2">
                <span wire:loading wire:target="openAddPermission" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="openAddPermission" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span wire:loading.remove wire:target="openAddPermission">Add Permission</span>
            </button>
            <button type="button" id="btn-add-role" wire:click="openAddRole" wire:loading.attr="disabled"
                class="btn btn-neutral gap-2">
                <span wire:loading wire:target="openAddRole" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="openAddRole" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span wire:loading.remove wire:target="openAddRole">Add Role</span>
            </button>
        </div>
    </div>

    {{-- ─── Roles Table ─────────────────────────────────────────────────────── --}}
    <div class="pb-4 px-4">
        <div class="text-sm text-base-content/60 font-medium">Roles</div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Name</th>
                            <th>Guard</th>
                            <th>Created At</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->roles as $r)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $this->roles->firstItem() + $loop->index }}</td>
                                <td class="text-sm">
                                    <span class="badge badge-sm border-none text-white px-2 py-3 whitespace-nowrap"
                                        style="background-color: {{ $r->color ?? '#64748b' }}">
                                        {{ $r->name }}
                                    </span>
                                </td>
                                <td class="text-sm">{{ $r->guard_name }}</td>
                                <td class="text-sm font-mono text-base-content/60">
                                    {{ $r->created_at->format('d-m-Y H:i:s') }}
                                </td>
                                <td class="text-sm whitespace-nowrap"><b>{{ $r->permissions_count }}</b> permission
                                </td>
                                <td class="text-sm whitespace-nowrap"><b>{{ $r->users_count }}</b> pengguna</td>
                                <td class="text-center">
                                    <div class="dropdown dropdown-left dropdown-end">
                                        <button tabindex="0" class="btn btn-ghost btn-xs btn-square rounded-full">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                            </svg>
                                        </button>
                                        <ul tabindex="0"
                                            class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36">
                                            <li>
                                                <button type="button"
                                                    wire:click="openEditRole({{ $r->id }})">
                                                    Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="text-error"
                                                    wire:click="confirmDelete('role', {{ $r->id }}, '{{ addslashes($r->name) }}')">
                                                    Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-sm text-base-content/60">Tidak ada role
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->roles->links() }}</div>
            </div>
        </div>
    </div>

    {{-- ─── Permissions Grid ────────────────────────────────────────────────── --}}
    <div class="mt-4">
        <div class="pb-4 px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-base-content/60 font-medium">Permissions</div>
            <div class="join">
                <span class="btn btn-disabled btn-sm join-item text-base-content pointer-events-none rounded-left-md">
                    Per Group
                </span>
                <select wire:model.live="perPagePerm" class="select select-sm join-item w-16 rounded-end-md">
                    <option value="4">4</option>
                    <option value="8">8</option>
                    <option value="12">12</option>
                    <option value="20">20</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($this->permissionGroups as $grp)
                <div class="card bg-base-100 shadow-sm flex flex-col">
                    <div class="card-body p-0 flex flex-col flex-1">

                        {{-- ── Group Header ── --}}
                        <div class="px-4 pt-4 pb-3 border-b border-base-200 flex items-center justify-between">
                            <div class="text-xs font-semibold uppercase tracking-wider text-base-content/60">
                                {{ $grp['name'] }}
                            </div>
                            <span class="badge badge-ghost badge-sm">{{ $grp['total'] }}</span>
                        </div>

                        {{-- ── Table ── --}}
                        <div class="overflow-x-auto flex-1">
                            <table class="table w-full">
                                <thead>
                                    <tr class="bg-base-200/50">
                                        <th>Name</th>
                                        <th>Guard</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($grp['items'] as $p)
                                        <tr>
                                            <td class="text-sm">{{ $p->name }}</td>
                                            <td class="text-sm">{{ $p->guard_name }}</td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-left dropdown-end">
                                                    <button tabindex="0"
                                                        class="btn btn-ghost btn-xs btn-square rounded-full">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                            viewBox="0 0 24 24" stroke-width="1.5"
                                                            stroke="currentColor" class="w-5 h-5">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                        </svg>
                                                    </button>
                                                    <ul tabindex="0"
                                                        class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36">
                                                        <li>
                                                            <button type="button"
                                                                wire:click="openEditPermission({{ $p->id }})">
                                                                Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="text-error"
                                                                wire:click="confirmDelete('permission', {{ $p->id }}, '{{ addslashes($p->name) }}')">
                                                                Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-base-content/60 py-4">
                                                Tidak ada permission
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- ── Per-group Pagination Footer ── --}}
                        @if ($grp['lastPage'] > 1)
                            <div class="px-4 py-2 border-t border-base-200 flex items-center justify-between gap-2">
                                <span class="text-xs text-base-content/50">
                                    {{ $grp['currentPage'] }} / {{ $grp['lastPage'] }}
                                    <span class="hidden sm:inline">({{ $grp['total'] }} total)</span>
                                </span>
                                <div class="join">
                                    <button
                                        class="join-item btn btn-xs {{ $grp['currentPage'] <= 1 ? 'btn-disabled' : '' }}"
                                        @if ($grp['currentPage'] > 1) wire:click="setGroupPage('{{ $grp['name'] }}', {{ $grp['currentPage'] - 1 }})" @endif>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 19.5L8.25 12l7.5-7.5" />
                                        </svg>
                                    </button>
                                    <button
                                        class="join-item btn btn-xs {{ $grp['currentPage'] >= $grp['lastPage'] ? 'btn-disabled' : '' }}"
                                        @if ($grp['currentPage'] < $grp['lastPage']) wire:click="setGroupPage('{{ $grp['name'] }}', {{ $grp['currentPage'] + 1 }})" @endif>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @else
                            <div class="px-4 py-2 border-t border-base-200">
                                <span class="text-xs text-base-content/40">{{ $grp['total'] }} permission</span>
                            </div>
                        @endif

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ─── Modal: Permission ───────────────────────────────────────────────── --}}
    <dialog id="permission-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'permission-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'permission-modal' && $el.close()">
        <div class="modal-box shadow">
            <h3 class="font-bold text-lg mb-4">
                {{ $permissionId ? 'Edit Permission' : 'Tambah Permission' }}
            </h3>
            <form wire:submit="savePermission">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    {{-- Name --}}
                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text text-sm font-medium text-base-content">Nama Permission</span>
                        </label>
                        <input type="text" wire:model="permissionName" id="permission-name"
                            class="input input-bordered placeholder:text-base-content/60 w-full @error('permissionName') input-error @enderror"
                            placeholder="Masukkan nama permission">
                        @error('permissionName')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Group --}}
                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text text-sm font-medium text-base-content">Nama Group Permission</span>
                        </label>
                        <input type="text" wire:model="permissionGroup" id="permission-group"
                            class="input input-bordered placeholder:text-base-content/60 w-full @error('permissionGroup') input-error @enderror"
                            placeholder="Masukkan nama group permission">
                        @error('permissionGroup')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Guard --}}
                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text text-sm font-medium text-base-content">Guard Name</span>
                        </label>
                        <select wire:model="permissionGuard" id="permission-guard"
                            class="select select-bordered w-full @error('permissionGuard') select-error @enderror">
                            <option value="web">web</option>
                            <option value="api">api</option>
                        </select>
                        @error('permissionGuard')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="modal-action">
                    <button type="button" class="btn btn-ghost"
                        x-on:click="document.getElementById('permission-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="savePermission"
                            class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="savePermission">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Role ─────────────────────────────────────────────────────── --}}
    <dialog id="role-modal" class="modal backdrop-blur-xs modal-bottom sm:modal-middle" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'role-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'role-modal' && $el.close()">
        <div class="modal-box shadow p-0 max-h-[80vh] overflow-y-auto relative">
            <div class="p-6 border-b border-base-200 bg-base-200 flex justify-between items-center sticky top-0 z-50">
                <h3 class="font-bold text-lg">
                    {{ $roleId ? 'Edit Role' : 'Tambah Role' }}
                </h3>

                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('role-modal').close()">✕</button>

            </div>

            <form wire:submit="saveRole">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 p-6">
                    {{-- Name --}}
                    <div class="form-control mb-2">
                        <label class="label mb-2"><span class="label-text text-sm font-medium text-base-content">Nama
                                Role</span></label>
                        <input type="text" wire:model="roleName" id="role-name"
                            class="input input-bordered placeholder:text-base-content/60 @error('roleName') input-error @enderror"
                            placeholder="Masukkan nama role">
                        @error('roleName')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Guard --}}
                    <div class="form-control mb-2">
                        <label class="label mb-2"><span class="label-text text-sm font-medium text-base-content">Pilih
                                Guard</span></label>
                        <select wire:model="roleGuard" id="role-guard"
                            class="select select-bordered w-full @error('roleGuard') select-error @enderror">
                            <option value="web">web</option>
                            <option value="api">api</option>
                        </select>
                        @error('roleGuard')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Custom Swatch Color Picker: klik warna → langsung pilih & picker tutup otomatis --}}
                    <div class="form-control mb-2 md:col-span-2" x-data="{
                        color: $wire.entangle('roleColor'),
                        open: false,
                        swatches: [
                            '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16',
                            '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
                            '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef',
                            '#ec4899', '#f43f5e', '#64748b', '#475569', '#334155',
                            '#dc2626', '#ea580c', '#d97706', '#ca8a04', '#65a30d',
                            '#16a34a', '#059669', '#0d9488', '#0891b2', '#0284c7',
                            '#2563eb', '#4f46e5', '#7c3aed', '#9333ea', '#c026d3',
                            '#db2777', '#e11d48', '#0f172a', '#1e293b', '#ffffff',
                        ],
                        selectColor(c) {
                            this.color = c;
                            this.open = false;
                        }
                    }"
                        x-on:click.outside="open = false">
                        <label class="label mb-2"><span class="label-text text-sm font-medium text-base-content">Pilih
                                Warna Badge</span></label>
                        <div class="flex gap-2 items-center">
                            {{-- Preview button: klik buka/tutup swatch grid --}}
                            <button type="button" id="role-color-preview" x-on:click="open = !open"
                                class="w-10 h-10 rounded-lg border-2 border-base-content/20 shrink-0
                                       shadow-sm transition-all hover:scale-110 hover:shadow-md
                                       focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-primary"
                                :style="`background-color: ${color}`" title="Klik untuk pilih warna">
                            </button>
                            {{-- Hex input manual --}}
                            <input type="text" id="role-color-text" x-model="color" x-on:focus="open = false"
                                class="input input-bordered flex-1 font-mono text-sm" placeholder="#64748b"
                                maxlength="7">
                        </div>

                        {{-- Swatch Grid Popover --}}
                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="mt-2 p-3 bg-base-100 border border-base-content/10 rounded-xl shadow-xl">
                            <p class="text-[10px] text-base-content/50 mb-2 uppercase tracking-wider font-semibold">
                                Klik warna untuk memilih</p>
                            <div class="grid grid-cols-10 gap-1.5">
                                <template x-for="swatch in swatches" :key="swatch">
                                    <button type="button" x-on:click="selectColor(swatch)"
                                        class="w-6 h-6 rounded-md border-2 transition-all duration-100
                                               hover:scale-125 hover:shadow-md focus:outline-none"
                                        :class="color === swatch ?
                                            'border-base-content/60 scale-110 shadow' :
                                            'border-transparent'"
                                        :style="`background-color: ${swatch}`" :title="swatch">
                                    </button>
                                </template>
                            </div>
                            {{-- Preview & tombol tutup --}}
                            <div class="flex items-center gap-2 mt-3 pt-3 border-t border-base-content/10">
                                <div class="w-5 h-5 rounded shrink-0 border border-base-content/20"
                                    :style="`background-color: ${color}`"></div>
                                <span class="text-xs text-base-content/60 font-mono" x-text="color"></span>
                                <button type="button" x-on:click="open = false"
                                    class="ml-auto text-xs btn btn-xs btn-ghost">Tutup</button>
                            </div>
                        </div>

                        @error('roleColor')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    {{-- Permissions --}}
                    <div class="form-control md:col-span-2">
                        <label class="label mb-2"><span
                                class="label-text text-sm font-medium">Permissions</span></label>
                        <p class="text-xs text-base-content/60 pb-2">Pilih semua permission/izin yang diperlukan</p>
                        <div class="max-h-85 overflow-auto py-3 px-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach ($this->allPermissions->groupBy('group') as $groupName => $groupList)
                                    <div class="card p-3 border border-base-content/15">
                                        <div class="border-base-content/15 border-b border-dashed mb-2">
                                            <div class="text-xs font-semibold uppercase text-base-content/60 mb-2">
                                                {{ $groupName ?? 'Ungrouped' }}
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ($groupList as $p)
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" wire:model="selectedPermissions"
                                                        value="{{ $p->id }}" class="checkbox checkbox-xs">
                                                    <span class="text-xs">{{ $p->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-action px-6 pb-6">
                    <button type="button" class="btn btn-ghost"
                        x-on:click="document.getElementById('role-modal').close()">Batal</button>
                    <button type="submit" class="btn btn-secondary" wire:loading.attr="disabled">
                        <span wire:loading wire:target="saveRole" class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="saveRole">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="rp-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'rp-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'rp-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('rp-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>

    {{-- ─── FAB ─────────────────────────────────────────────────────────────── --}}
    <div class="fab fab-flower fab-bottom fab-end mb-12">
        <div tabindex="0" role="button" class="btn btn-circle btn-lg btn-primary">
            <svg aria-label="New" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor"
                class="size-6">
                <path
                    d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z" />
            </svg>
        </div>
        <div class="fab-close">
            <span class="btn btn-circle btn-lg btn-error">✕</span>
        </div>
        <button type="button" class="tooltip btn btn-circle btn-lg btn-primary" id="fab-add-role"
            wire:click="openAddRole" data-tip="Add Role">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd"
                    d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z"
                    clip-rule="evenodd" />
            </svg>
        </button>
        <button type="button" class="tooltip btn btn-circle btn-lg btn-primary" id="fab-add-permission"
            wire:click="openAddPermission" data-tip="Add Permission">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd"
                    d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>
