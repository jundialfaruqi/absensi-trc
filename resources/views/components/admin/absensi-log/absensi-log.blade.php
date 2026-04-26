<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Log Aktifitas Absensi</h1>
            <p class="text-sm text-base-content/60 mt-1">Riwayat perubahan data absensi oleh Admin</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Settings</li>
                <li>
                    <a href="{{ route('absensi.log') }}">
                        <span class="text-base-content font-bold">Log Absensi</span>
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
                    <option value="10">10</option>
                    <option value="25">25</option>
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
        </div>

        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Cari nama personnel..." wire:model.live.debounce.400ms="search"
                class="input placeholder:text-base-content/60 input-bordered w-full pl-10 pr-10 bg-base-100" />
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

    {{-- ─── Log Table ────────────────────────────────────────────────────── --}}
    <div class="card bg-base-100 shadow-sm border border-base-200 overflow-hidden">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead class="bg-base-100 uppercase text-[10px] tracking-widest text-base-content/50">
                        <tr>
                            <th class="p-4">Waktu Edit</th>
                            <th>Admin</th>
                            <th>Personnel</th>
                            <th>Tanggal Absen</th>
                            <th class="text-center">Perubahan Status</th>
                            <th>Alasan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="text-xs">
                        @forelse ($this->logs as $log)
                            <tr class="group">
                                <td class="p-4">
                                    <div class="font-bold">{{ $log->edited_at->translatedFormat('d F Y') }}</div>
                                    <div class="text-[10px] opacity-60">{{ $log->edited_at->format('H:i:s') }}</div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div
                                                class="bg-primary text-primary-content rounded-full w-8 flex items-center justify-center">
                                                <span>{{ strtoupper(substr($log->editor->name ?? 'A', 0, 1)) }}</span>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $log->editor->name ?? 'System' }}</div>
                                            <div class="text-[10px] opacity-50 uppercase">
                                                {{ $log->editor->roles->first()?->name ?? 'Admin' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar placeholder">
                                            <div class="bg-neutral text-neutral-content rounded-full w-8">
                                                @if ($log->personnel->foto)
                                                    <img src="{{ asset('storage/' . $log->personnel->foto) }}" />
                                                @else
                                                    <span>{{ strtoupper(substr($log->personnel->name, 0, 1)) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold">{{ $log->personnel->name }}</div>
                                            <div class="text-[10px] opacity-50 uppercase">
                                                {{ $log->personnel->opd->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-bold text-primary">{{ $log->tanggal->translatedFormat('d F Y') }}
                                    </div>
                                    <div class="text-[10px] opacity-60 uppercase">
                                        {{ $log->tanggal->translatedFormat('l') }}</div>
                                </td>
                                <td class="text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="badge badge-outline badge-neutral text-[9px] font-bold opacity-40">
                                            {{ $log->original_status_masuk ?? 'N/A' }}</div>
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="2.5" stroke="currentColor" class="size-3 opacity-30">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                        </svg>
                                        <div class="badge badge-primary text-[9px] font-bold">{{ $log->status_masuk }}
                                        </div>
                                    </div>
                                    <div class="mt-1 text-[10px] opacity-50 italic">
                                        {{ $log->jam_masuk ? $log->jam_masuk->format('H:i') : '--:--' }} -
                                        {{ $log->jam_pulang ? $log->jam_pulang->format('H:i') : '--:--' }}
                                    </div>
                                </td>
                                <td class="max-w-xs whitespace-normal">
                                    <div class="text-[11px] leading-relaxed">
                                        {{ $log->alasan_edit ?? '-' }}
                                    </div>
                                    @if ($log->nomor_surat)
                                        <div class="mt-1 flex items-center gap-1 text-primary">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                stroke-width="2" stroke="currentColor" class="size-3">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            <span class="text-[10px] font-bold">{{ $log->nomor_surat }}</span>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-20">
                                    <div class="flex flex-col items-center opacity-30">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-12 mb-2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                        </svg>
                                        <span class="text-sm font-bold uppercase tracking-widest">Belum ada aktifitas
                                            edit</span>
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
</div>
