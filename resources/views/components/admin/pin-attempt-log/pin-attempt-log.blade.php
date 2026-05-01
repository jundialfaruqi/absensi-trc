<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Log Percobaan PIN</h1>
            <p class="text-sm text-base-content/60 mt-1">Riwayat upaya memasukkan PIN pada Portal Absensi Web</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('absensi.log.pin') }}">
                        <span class="text-base-content font-bold">Log PIN</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar ──────────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row justify-between gap-4 mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3">
            <div class="join">
                <span class="btn btn-disabled join-item text-base-content pointer-events-none">Show</span>
                <select wire:model.live="perPage" class="select select-bordered join-item w-20">
                    <option value="15">15</option>
                    <option value="30">30</option>
                    <option value="50">50</option>
                </select>
            </div>

            @if (Auth::user()->hasRole('super-admin'))
                <select wire:model.live="selectedOpdId" class="select select-bordered w-full sm:w-64">
                    <option value="">-- Semua OPD --</option>
                    @foreach ($this->opds as $opd)
                        <option value="{{ $opd->id }}">{{ $opd->name }}</option>
                    @endforeach
                </select>
            @endif

            <select wire:model.live="selectedStatus" class="select select-bordered w-full sm:w-40">
                <option value="">-- Semua Status --</option>
                <option value="success">BERHASIL</option>
                <option value="fail">GAGAL</option>
            </select>

            <button wire:click="clearOldLogs" wire:loading.attr="disabled"
                class="btn btn-error btn-outline w-full sm:w-auto">
                <span wire:loading wire:target="clearOldLogs" class="loading loading-spinner loading-xs"></span>
                <svg wire:loading.remove wire:target="clearOldLogs" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
                <span wire:loading.remove wire:target="clearOldLogs">Bersihkan Log Lama</span>
            </button>
        </div>

        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Cari nama atau IP..." wire:model.live.debounce.400ms="search"
                class="input input-bordered w-full placeholder:text-base-content/60 pl-10 pr-10 bg-base-100" />
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <svg class="w-4 h-4 text-base-content/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            @if ($search)
                <button type="button" wire:click="$set('search', '')"
                    class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>
    </div>

    {{-- ─── Table ────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-100 uppercase text-[10px] tracking-widest text-base-content/50">
                        <tr>
                            <th class="p-4">Waktu</th>
                            <th>Personel</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse ($this->logs as $log)
                            <tr class="group">
                                <td class="p-4">
                                    <div class="font-bold">{{ $log->created_at->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] opacity-60">{{ $log->created_at->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                @if ($log->personnel && $log->personnel->foto)
                                                    <img src="{{ asset('storage/' . $log->personnel->foto) }}" />
                                                @elseif($log->personnel)
                                                    <span>{{ strtoupper(substr($log->personnel->name, 0, 1)) }}</span>
                                                @else
                                                    <span
                                                        class="text-error font-bold h-full w-full flex items-center justify-center">?</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">
                                                {{ $log->personnel ? $log->personnel->name : 'Tidak Teridentifikasi' }}
                                            </div>
                                            <div class="text-[10px] opacity-50 uppercase">
                                                {{ $log->personnel ? $log->personnel->opd->name : 'Global Brute-force Prevention' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if ($log->status === 'success')
                                        <span
                                            class="badge badge-success badge-sm text-[9px] font-black text-white">SUCCESS</span>
                                    @else
                                        <span
                                            class="badge badge-error badge-sm text-[9px] font-black text-white">FAILED</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-mono text-[10px]">{{ $log->ip_address }}</span>
                                </td>
                                <td class="max-w-xs truncate text-[10px] opacity-60" title="{{ $log->user_agent }}">
                                    {{ $log->user_agent }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-20">
                                    <div class="flex flex-col items-center opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-12 mb-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        <span class="text-sm font-bold uppercase tracking-widest">Belum ada log
                                            percobaan PIN</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-base-200">
                {{ $this->logs->links() }}
            </div>
        </div>
    </div>

    @if ($showConfirmModal)
        <div class="modal modal-open backdrop-blur-sm">
            <div class="modal-box shadow-2xl border border-error/20 max-w-md p-0 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center gap-4 text-error mb-4">
                        <div class="p-3 bg-error/10 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="size-8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-xl leading-tight">Bersihkan Log!</h3>
                            <p class="text-[10px] uppercase font-black opacity-40 tracking-widest">Konfirmasi
                                Penghapusan
                            </p>
                        </div>
                    </div>

                    <div class="py-4 space-y-4">
                        <p class="text-sm leading-relaxed text-base-content/80">
                            Anda akan menghapus data <span class="font-bold text-error">Log Percobaan PIN</span> yang
                            berumur lebih dari <span class="badge badge-error badge-outline font-bold">30 Hari</span>
                            secara
                            massal.
                        </p>
                        <div class="alert alert-error bg-error/5 text-[11px] py-3 rounded-xl border-error/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-error shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="font-medium italic">Tindakan ini permanen dan data yang dihapus tidak dapat
                                dikembalikan lagi.</span>
                        </div>
                    </div>

                    <div class="modal-action grid grid-cols-2 gap-3 mt-2">
                        <button type="button" wire:click="$set('showConfirmModal', false)"
                            class="btn btn-ghost border-base-300">Batal</button>
                        <button type="button" wire:click="confirmClearOldLogs()" class="btn btn-error text-white">
                            <span wire:loading wire:target="confirmClearOldLogs"
                                class="loading loading-spinner loading-xs"></span>
                            <span wire:loading.remove wire:target="confirmClearOldLogs">Ya, Bersihkan Log</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
