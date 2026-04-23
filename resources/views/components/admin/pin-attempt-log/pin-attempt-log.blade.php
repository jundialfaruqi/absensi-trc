<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Log Percobaan PIN</h1>
            <p class="text-sm text-base-content/60 mt-1">Riwayat upaya memasukkan PIN pada Portal Absensi Web</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
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
        </div>

        <div class="relative w-full sm:w-64">
            <input type="text" placeholder="Cari nama atau IP..." wire:model.live.debounce.400ms="search"
                class="input input-bordered w-full pl-10 pr-10 bg-base-100" />
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
                                    <div class="font-bold">{{ $log->attempted_at->translatedFormat('d M Y') }}</div>
                                    <div class="text-[10px] opacity-60">{{ $log->attempted_at->format('H:i:s') }}</div>
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
                                    @if ($log->status === 'success')
                                        <span class="badge badge-success badge-sm text-[9px] font-black text-white">SUCCESS</span>
                                    @else
                                        <span class="badge badge-error badge-sm text-[9px] font-black text-white">FAILED</span>
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
                                        <span class="text-sm font-bold uppercase tracking-widest">Belum ada log percobaan PIN</span>
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
