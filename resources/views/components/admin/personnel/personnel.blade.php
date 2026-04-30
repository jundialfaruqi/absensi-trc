<div wire:init="load">
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Personnel</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola data dan akun personnel</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li>
                    <a href="{{ route('personnel') }}">
                        <span class="text-base-content font-bold">Personnel</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>


    {{-- ─── Toolbar: Search + Buttons ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="join">
                    <span
                        class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                    <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-auto">
                    <input type="text" placeholder="Cari nama/email..." wire:model.live.debounce.400ms="search"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
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

                @if (auth()->user()->hasRole('super-admin'))
                    <div class="w-full sm:w-auto">
                        <select wire:model.live="selectedOpd" class="select select-bordered w-full sm:w-64 bg-base-100">
                            <option value="">Semua OPD (Filter)</option>
                            @foreach ($this->opds as $opd)
                                <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
        </div>
        <div class="flex gap-2">
            <button wire:click="goToAdd" class="btn btn-neutral gap-2" wire:loading.attr="disabled">
                <span wire:loading wire:target="goToAdd" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="goToAdd" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Tambah Personnel
            </button>
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            {{-- ─── Skeleton Loading (While Not Ready) ────────────────────────── --}}
            @if (!$readyToLoad)
                <div class="overflow-x-auto">
                    <table class="table table-zebra w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-16">#</th>
                                <th>Personnel</th>
                                <th>Kontak</th>
                                <th>Penugasan</th>
                                <th>Kantor / Lokasi</th>
                                <th>OPD Induk</th>
                                <th class="text-center">Face</th>
                                <th class="text-center w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @for ($i = 0; $i < $perPage; $i++)
                                <tr>
                                    <td class="text-center">
                                        <div class="skeleton h-4 w-4 mx-auto"></div>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="skeleton h-10 w-10 rounded-full shrink-0"></div>
                                            <div class="flex flex-col gap-2">
                                                <div class="skeleton h-4 w-32"></div>
                                                <div class="skeleton h-3 w-24"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-24"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-5 w-20 rounded-full"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-32 mb-1"></div>
                                        <div class="skeleton h-3 w-16"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton h-4 w-28"></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="skeleton h-4 w-12 mx-auto rounded-full"></div>
                                    </td>
                                    <td class="text-center">
                                        <div class="skeleton h-6 w-6 mx-auto rounded-full"></div>
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ─── Real Table Data ────────────────────────────────────────── --}}
            @if ($readyToLoad)
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="text-center w-16">#</th>
                                <th>Personnel</th>
                                <th>Kontak</th>
                                <th>Penugasan</th>
                                <th>Kantor / Lokasi</th>
                                <th>OPD Induk</th>
                                <th class="text-center">Face</th>
                                <th class="text-center w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $currentOpd = null;
                                $isSuperAdmin = auth()->user()->hasRole('super-admin');
                            @endphp
                            @forelse ($this->personnels as $r)
                                @if ($isSuperAdmin && $currentOpd !== $r->opd_id)
                                    <tr class="bg-base-200">
                                        <td colspan="8"
                                            class="sticky left-0 top-12 z-50 p-0 border-b border-base-200 bg-base-200">
                                            <div class="sticky left-0 w-fit px-4 py-2 flex items-center gap-2">
                                                <div class="w-1.5 h-4 bg-primary rounded-full"></div>
                                                <span
                                                    class="text-[11px] font-black uppercase tracking-[0.2em] text-primary whitespace-nowrap">
                                                    {{ $r->opd?->singkatan ?? ($r->opd?->name ?? 'TANPA OPD') }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @php $currentOpd = $r->opd_id; @endphp
                                @endif
                                <tr class="hover:bg-base-200/50">
                                    <td class="text-center font-bold">
                                        {{ $this->personnels->firstItem() + $loop->index }}
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-3">
                                            <div class="avatar placeholder">
                                                @if ($r->foto)
                                                    <div class="w-10 rounded-full">
                                                        <img src="{{ asset('storage/' . $r->foto) }}"
                                                            alt="{{ $r->name }}" />
                                                    </div>
                                                @else
                                                    <div class="bg-neutral text-neutral-content w-10 rounded-full">
                                                        <span
                                                            class="text-lg">{{ strtoupper(substr($r->name, 0, 1)) }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-bold">{{ $r->name }}</div>
                                                <div class="text-xs opacity-70">{{ $r->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-sm">{{ $r->nomor_hp ?: '-' }}</div>
                                    </td>
                                    <td>
                                        @if ($r->penugasan)
                                            <div class="badge badge-secondary badge-sm font-medium text-nowrap">
                                                {{ $r->penugasan->name }}</div>
                                        @else
                                            <div class="text-xs italic text-base-content/50">-</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->kantor)
                                            <div class="text-sm font-bold">{{ $r->kantor->name }}</div>
                                            @if ($r->wajib_absen_di_lokasi)
                                                <div class="badge badge-error badge-xs font-bold text-[8px] uppercase">
                                                    Wajib
                                                    Lokasi</div>
                                            @else
                                                <div
                                                    class="badge badge-ghost badge-xs font-bold text-[8px] uppercase opacity-50">
                                                    Luar Lokasi OK</div>
                                            @endif
                                        @else
                                            <div class="text-xs italic text-base-content/50">Belum diatur</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($r->opd)
                                            <div class="text-sm font-semibold mb-1">{{ $r->opd->name }}</div>
                                        @else
                                            <div class="text-sm text-base-content/50 italic mb-1">Tanpa OPD</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($r->face_recognition)
                                            <div class="badge badge-success badge-xs font-bold text-[8px] uppercase">
                                                Aktif
                                            </div>
                                        @else
                                            <div
                                                class="badge badge-ghost badge-xs font-bold text-[8px] uppercase opacity-50">
                                                Non-Aktif</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="dropdown dropdown-left dropdown-end">
                                            <button tabindex="0"
                                                class="btn btn-ghost btn-xs btn-square rounded-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                </svg>
                                            </button>
                                            <ul tabindex="0"
                                                class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                                <li>
                                                    <a wire:navigate
                                                        href="{{ route('personnel.edit', $r->id) }}">Edit</a>
                                                </li>
                                                <li>
                                                    <button type="button" class="text-error"
                                                        wire:click="confirmDelete({{ $r->id }}, '{{ addslashes($r->name) }}')">Delete</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-sm text-base-content/60 py-8">Tidak ada
                                        data Personnel</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">
                    @if ($readyToLoad)
                        {{ $this->personnels->links() }}
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- ─── Modal: Delete Confirmation ─────────────────────────────────────── --}}
    <dialog id="personnel-delete-modal" class="modal"
        x-on:open-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'personnel-delete-modal' && $el.close()">
        <div class="modal-box">
            <h3 class="font-bold text-lg mb-2 text-error">Konfirmasi Hapus</h3>
            <p class="text-sm text-base-content/70">
                Apakah Anda yakin ingin menghapus data Personnel
                <span class="font-semibold">{{ $deleteName }}</span>?
            </p>
            <div class="modal-action">
                <button type="button" class="btn"
                    x-on:click="document.getElementById('personnel-delete-modal').close()">Batal</button>
                <button type="button" class="btn btn-error" wire:click="executeDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading wire:target="executeDelete" class="loading loading-spinner loading-xs"></span>
                    <span wire:loading.remove wire:target="executeDelete">Hapus</span>
                </button>
            </div>
        </div>
    </dialog>

</div>
