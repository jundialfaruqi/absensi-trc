<x-layout title="Role & Permission">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Role & Permission</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola role dan permission</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard.index') }}">{{ $appSetting->app_name ?? config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('role_permission.index') }}">
                        <span class="text-base-content">Role &
                            Permission
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- Toast Success --}}
    @if (session('success'))
        <div id="success-toast" class="toast toast-top toast-end z-50 shadow-2xl rounded-xl">
            <div class="alert glass backdrop-blur-lg border border-primary text-secondary font-bold">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M9 12l2 2 4-4M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <div class="card bg-linear-to-r from-secondary to-neutral text-base-100 p-5">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <div class="text-lg text-white font-bold">Manajemen Akses</div>
                    <div class="text-sm text-white opacity-80">Data role dan permission</div>
                </div>
                <div class="flex flex-wrap gap-4 md:gap-0 mt-1 md:mt-0">
                    <div class="text-center">
                        <div class="text-2xl text-white font-bold">{{ $stats['roles'] ?? 0 }}</div>
                        <div class="text-xs text-white opacity-80">Roles</div>
                    </div>
                    <div class="text-center md:pl-6 md:ml-6 md:border-l md:border-dotted md:border-white/40">
                        <div class="text-2xl text-white font-bold">{{ $stats['permissions'] ?? 0 }}</div>
                        <div class="text-xs text-white opacity-80">Permissions</div>
                    </div>
                    <div class="text-center md:pl-6 md:ml-6 md:border-l md:border-dotted md:border-white/40">
                        <div class="text-2xl text-white font-bold">{{ $stats['new_permissions'] ?? 0 }}</div>
                        <div class="text-xs text-white opacity-80">Permission Baru</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-4">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="card-title text-sm text-base-content/60 font-medium">Total Role & Permission</h2>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-2xl font-bold">{{ $stats['total'] ?? 0 }}</span>
                                <span class="text-xs text-base-content/50">Role & Permission</span>
                            </div>
                        </div>
                        <div class="p-2 bg-base-200 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="card-title text-sm text-base-content/60 font-medium">Role Terbanyak Digunakan
                            </h2>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-2xl font-bold">{{ $stats['top_role_users'] ?? 0 }}</span>
                                <span class="text-xs text-success">Pengguna Role
                                    {{ ucfirst($stats['top_role_name'] ?? '-') }}</span>
                            </div>
                        </div>
                        <div class="p-2 bg-base-200 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M3 3v18h18M9 13v5m4-9v9m4-13v13" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="card-title text-sm text-base-content/60 font-medium">Role User</h2>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-2xl font-bold">{{ $stats['user_role_count'] ?? 0 }}</span>
                                <span class="text-xs text-warning">Pengguna</span>
                            </div>
                        </div>
                        <div class="p-2 bg-base-200 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 14c3.866 0 7 1.343 7 3v1H5v-1c0-1.657 3.134-3 7-3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 12a4 4 0 100-8 4 4 0 000 8z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body p-5">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="card-title text-sm text-base-content/60 font-medium">Role Super Admin</h2>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="text-2xl font-bold">{{ $stats['superadmin_role_count'] ?? 0 }}</span>
                                <span class="text-xs text-error">Pengguna</span>
                            </div>
                        </div>
                        <div class="p-2 bg-base-200 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12l2 2 4-4M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <form method="GET" action="{{ route('role_permission.index') }}" class="flex items-center gap-2">
                    <div class="join">
                        <span
                            class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show
                            Roles</span>
                        <select name="per_page_role" class="select join-item w-20 rounded-end-md"
                            onchange="this.form.submit()">
                            @php $ppr = (int) request('per_page_role', 10); @endphp
                            <option value="10" @selected($ppr === 10)>10</option>
                            <option value="20" @selected($ppr === 20)>20</option>
                            <option value="50" @selected($ppr === 50)>50</option>
                        </select>
                    </div>
                    <input type="hidden" name="q" value="{{ request('q') }}">
                    <input type="hidden" name="per_page_perm" value="{{ request('per_page_perm', 4) }}">
                    <input type="hidden" name="page_role" value="{{ request('page_role') }}">
                    <input type="hidden" name="page_perm" value="{{ request('page_perm') }}">
                </form>
                <div class="relative w-full sm:w-auto">
                    <input id="rp-search-input" type="text" placeholder="Search..." value="{{ request('q') }}"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    <button type="button" id="rp-search-clear"
                        class="absolute inset-y-0 right-0 pr-3 text-base-content/50 hidden">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <div id="rp-search-suggestions"
                        class="absolute mt-1 w-full bg-base-100 rounded-md shadow z-10 hidden"></div>
                </div>
            </div>
        </div>
        <div class="flex gap-2">
            <button type="button" id="btn-add-permission" class="btn btn-base-300 gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Permission
            </button>
            <button type="button" id="btn-add-role" class="btn btn-neutral gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Role
            </button>
        </div>
    </div>

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
                        @forelse($roles as $r)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $roles->firstItem() + $loop->index }}
                                </td>
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
                                <td class="text-sm whitespace-nowrap">
                                    <b>{{ $r->permissions_count }}</b> permission
                                </td>
                                <td class="text-sm whitespace-nowrap"><b>{{ $r->users_count }}</b> pengguna</td>
                                <td class="text-center">
                                    <div class="dropdown dropdown-left dropdown-end">
                                        <button class="btn btn-ghost btn-xs btn-square rounded-full">
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
                                                <button type="button" data-edit-role="{{ $r->id }}"
                                                    data-name="{{ $r->name }}"
                                                    data-guard="{{ $r->guard_name }}"
                                                    data-color="{{ $r->color ?? '#64748b' }}"
                                                    data-permission-ids="{{ $r->permissions->pluck('id')->implode(',') }}">
                                                    Edit
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="text-error rp-delete-btn"
                                                    data-type="role" data-id="{{ $r->id }}"
                                                    data-name="{{ $r->name }}">
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
                <div class="w-full">{!! $roles->links() !!}</div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <div class="pb-4 px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="text-sm text-base-content/60 font-medium">Permissions</div>
            <form method="GET" action="{{ route('role_permission.index') }}" class="flex items-center gap-2">
                <div class="join">
                    <span
                        class="btn btn-disabled btn-sm join-item text-base-content pointer-events-none rounded-left-md">Show
                        Permissions</span>
                    <select name="per_page_perm" class="select select-sm join-item w-16 rounded-end-md"
                        onchange="this.form.submit()">
                        @php $ppp = (int) request('per_page_perm', 4); @endphp
                        <option value="4" @selected($ppp === 4)>4</option>
                        <option value="8" @selected($ppp === 8)>8</option>
                        <option value="12" @selected($ppp === 12)>12</option>
                    </select>
                </div>
                <input type="hidden" name="q" value="{{ request('q') }}">
                <input type="hidden" name="per_page_role" value="{{ request('per_page_role', 10) }}">
                <input type="hidden" name="page_role" value="{{ request('page_role') }}">
                <input type="hidden" name="page_perm" value="{{ request('page_perm') }}">
            </form>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($permissionGroups ?? [] as $grp)
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body p-0">
                        <div class="p-4">
                            <div class="text-xs font-semibold uppercase text-base-content/60">
                                {{ $grp['name'] ?? 'Ungrouped' }}</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="table w-full">
                                <thead>
                                    <tr class="bg-base-200/50">
                                        <th>Name</th>
                                        <th>Guard</th>
                                        <th class="text-end"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($grp['items'] as $p)
                                        <tr>
                                            <td class="text-sm">{{ $p->name }}</td>
                                            <td class="text-sm">{{ $p->guard_name }}</td>
                                            <td class="text-right">
                                                <div class="dropdown dropdown-left dropdown-end">
                                                    <button class="btn btn-ghost btn-xs btn-square rounded-full">
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
                                                                data-edit-permission="{{ $p->id }}"
                                                                data-name="{{ $p->name }}"
                                                                data-group="{{ $p->group }}"
                                                                data-guard="{{ $p->guard_name }}">
                                                                Edit
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="text-error rp-delete-btn"
                                                                data-type="permission" data-id="{{ $p->id }}"
                                                                data-name="{{ $p->name }}">
                                                                Delete
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-base-content/60">Tidak
                                                ada permission</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-actions justify-between items-center p-4 border-t border-base-200 mt-auto">
                            <div class="w-full">{!! $grp['items']->links() !!}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <dialog id="permission-modal" class="modal backdrop-blur-xs">
        <div class="modal-box shadow">
            <h3 class="font-bold text-lg mb-4">Permission</h3>
            <form id="permission-form" method="POST" action="{{ route('permissions.store') }}" data-loading>
                @csrf
                <input type="hidden" name="modal_type" value="permission">
                <input type="hidden" name="_method" id="permission-method" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">

                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" id="permission-name"
                            class="input input-bordered w-full">
                        @if ($errors->has('name') && old('modal_type') === 'permission')
                            <span class="text-red-500 text-xs">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text">Group</span>
                        </label>
                        <input type="text" name="group" id="permission-group"
                            class="input input-bordered w-full">
                        @if ($errors->has('group') && old('modal_type') === 'permission')
                            <span class="text-red-500 text-xs">{{ $errors->first('group') }}</span>
                        @endif
                    </div>

                    <div class="form-control md:col-span-2 mb-2">
                        <label class="label mb-2">
                            <span class="label-text">Guard Name</span>
                        </label>
                        <select name="guard_name" id="permission-guard" class="select select-bordered w-full">
                            <option value="web" @selected(old('guard_name') === 'web')>web</option>
                            <option value="api" @selected(old('guard_name') === 'api')>api</option>
                        </select>
                        @if ($errors->has('guard_name') && old('modal_type') === 'permission')
                            <span class="text-red-500 text-xs">{{ $errors->first('guard_name') }}</span>
                        @endif
                    </div>

                </div>

                <div class="modal-action">
                    <button type="button" class="btn" data-close="permission-modal">Batal</button>
                    <button type="submit" class="btn btn-secondary">
                        <span class="loading loading-spinner loading-xs hidden"></span>
                        <span class="btn-text">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="role-modal" class="modal backdrop-blur-xs">
        <div class="modal-box shadow">
            <h3 class="font-bold text-lg mb-4">Role</h3>
            <form id="role-form" method="POST" action="{{ route('roles.store') }}" data-loading>
                @csrf
                <input type="hidden" name="modal_type" value="role">
                <input type="hidden" name="_method" id="role-method" value="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="form-control mb-2">
                        <label class="label mb-2"><span class="label-text">Nama</span></label>
                        <input type="text" name="name" id="role-name" class="input input-bordered">
                        @if ($errors->has('name') && old('modal_type') === 'role')
                            <span class="text-red-500 text-xs">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="form-control mb-2">
                        <label class="label mb-2"><span class="label-text">Pilih Guard</span></label>
                        <select name="guard_name" id="role-guard" class="select select-bordered w-full">
                            <option value="web" @selected(old('guard_name') === 'web')>web</option>
                            <option value="api" @selected(old('guard_name') === 'api')>api</option>
                        </select>
                        @if ($errors->has('guard_name') && old('modal_type') === 'role')
                            <span class="text-red-500 text-xs">{{ $errors->first('guard_name') }}</span>
                        @endif
                    </div>
                    <div class="form-control mb-2 md:col-span-2">
                        <label class="label mb-2"><span class="label-text">Pilih Warna</span></label>
                        <div class="flex gap-2 items-center">
                            <input type="color" name="color" id="role-color"
                                class="input-color-circle w-10 h-10" value="#64748b">
                            <input type="text" id="role-color-text" class="input input-bordered flex-1"
                                value="#64748b" placeholder="#64748b">
                        </div>
                        @if ($errors->has('color') && old('modal_type') === 'role')
                            <span class="text-red-500 text-xs">{{ $errors->first('color') }}</span>
                        @endif
                    </div>
                    <div class="form-control md:col-span-2">
                        <label class="label mb-2"><span class="label-text">Permissions</span></label>
                        <p class="text-xs text-base-content/60">Pilih semua permission/izin yang diperlukan</p>
                        <div class="max-h-85 overflow-auto py-3 px-1">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach (($allPermissions ?? collect())->groupBy('group') as $groupName => $groupList)
                                    <div class="card p-3 border border-base-content/15">
                                        <div class="border-base-content/15 border-b border-dashed mb-2">
                                            <div class="text-xs font-semibold uppercase text-base-content/60 mb-2">
                                                {{ $groupName ?? 'Ungrouped' }}</div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2">
                                            @foreach ($groupList as $p)
                                                <label class="flex items-center gap-2">
                                                    <input type="checkbox" name="permission_ids[]"
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
                <div class="modal-action">
                    <button type="button" class="btn" data-close="role-modal">Batal</button>
                    <button type="submit" class="btn btn-secondary">
                        <span class="loading loading-spinner loading-xs hidden"></span>
                        <span class="btn-text">Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </dialog>

    <dialog id="rp-delete-modal" class="modal">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">Apakah Anda yakin ingin menghapus <span id="rp-delete-name"
                    class="font-semibold"></span>?</p>
            <div class="modal-action">
                <button type="button" class="btn" data-close="rp-delete-modal">Batal</button>
                <form id="rp-delete-form" method="POST" action="#" data-loading
                    data-loading-text="Menghapus...">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">
                        <span class="loading loading-spinner loading-xs hidden"></span>
                        <span class="btn-text">Hapus</span>
                    </button>
                </form>
            </div>
        </div>
    </dialog>

    <div class="fab fab-flower fab-bottom fab-end mb-12">
        <!-- a focusable div with tabindex is necessary to work on all browsers. role="button" is necessary for accessibility -->
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

        <!-- buttons that show up when FAB is open -->
        <button type="button" class="tooltip btn btn-circle btn-lg btn-primary" id="fab-add-role"
            data-tip="Add Role">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd"
                    d="M12 1.5a5.25 5.25 0 0 0-5.25 5.25v3a3 3 0 0 0-3 3v6.75a3 3 0 0 0 3 3h10.5a3 3 0 0 0 3-3v-6.75a3 3 0 0 0-3-3v-3c0-2.9-2.35-5.25-5.25-5.25Zm3.75 8.25v-3a3.75 3.75 0 1 0-7.5 0v3h7.5Z"
                    clip-rule="evenodd" />
            </svg>
        </button>
        <button type="button" class="tooltip btn btn-circle btn-lg btn-primary" id="fab-add-permission"
            data-tip="Add Permission">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                <path fill-rule="evenodd"
                    d="M12.516 2.17a.75.75 0 0 0-1.032 0 11.209 11.209 0 0 1-7.877 3.08.75.75 0 0 0-.722.515A12.74 12.74 0 0 0 2.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 0 0 .374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 0 0-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08Zm3.094 8.016a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z"
                    clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <script>
        window.RolePermissionConfig = {
            routes: {
                index: "{{ route('role_permission.index') }}",
                permissionsStore: "{{ route('permissions.store') }}",
                permissionsBaseUrl: "{{ url('/permissions') }}",
                permissionsSuggest: "{{ route('permissions.suggest') }}",
                rolesStore: "{{ route('roles.store') }}",
                rolesBaseUrl: "{{ url('/roles') }}"
            },
            old: {
                modalType: "{{ old('modal_type') }}"
            }
        };
    </script>
    <script src="{{ asset('js/role-permission.js') }}"></script>
</x-layout>
