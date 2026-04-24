<div class="space-y-8" wire:init="load">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-base-content tracking-tight uppercase italic">Dashboard TRC</h1>
            <p class="text-xs font-bold text-base-content/50 uppercase tracking-[0.3em]">
                {{ $opdName }} • {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}
            </p>
        </div>
        <div class="flex items-center gap-2 px-4 py-2 bg-base-100 rounded-xl border border-base-300 shadow-sm">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-success opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-success"></span>
            </span>
            <span class="text-[10px] font-black uppercase tracking-widest text-base-content opacity-70">Sistem Berjalan
                Normal</span>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @if (!$readyToLoad)
            @for ($i = 0; $i < 4; $i++)
                <div class="stats shadow bg-base-100 border border-base-200">
                    <div class="stat">
                        <div class="stat-figure text-base-content/10">
                            <div class="skeleton w-10 h-10 rounded-full"></div>
                        </div>
                        <div class="stat-title"><div class="skeleton h-3 w-20"></div></div>
                        <div class="stat-value"><div class="skeleton h-10 w-16 mt-2"></div></div>
                        <div class="stat-desc mt-2"><div class="skeleton h-2 w-24"></div></div>
                    </div>
                </div>
            @endfor
        @else
            {{-- Total Personnel --}}
            <div
                class="stats shadow bg-base-100 border border-base-200 group hover:border-primary/30 transition-all duration-300">
                <div class="stat">
                    <div
                        class="stat-figure text-primary opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                    </div>
                    <div class="stat-title text-[10px] font-black uppercase tracking-widest text-base-content/50">Total
                        Personel</div>
                    <div class="stat-value text-primary">{{ number_format($stats['total_personnel']) }}</div>
                    <div class="stat-desc font-bold text-[9px] uppercase tracking-tighter mt-1">Aktif dalam sistem</div>
                </div>
            </div>

            {{-- Absen Masuk --}}
            <div
                class="stats shadow bg-base-100 border border-base-200 group hover:border-success/30 transition-all duration-300">
                <div class="stat">
                    <div
                        class="stat-figure text-success opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M11.5 21l-5.5-2V7l5.5 2V21zM11.5 21l5.5-2V7l-5.5 2V21zM9.5 13H13.5M11.5 11V15" />
                        </svg>
                    </div>
                    <div class="stat-title text-[10px] font-black uppercase tracking-widest text-base-content/50">Absen
                        Masuk</div>
                    <div class="stat-value text-success">{{ number_format($stats['total_masuk']) }}</div>
                    <div class="stat-desc font-bold text-[9px] uppercase tracking-tighter mt-1">
                        {{ $stats['hadir_percentage'] }}% dari total personel</div>
                </div>
            </div>

            {{-- Absen Pulang --}}
            <div
                class="stats shadow bg-base-100 border border-base-200 group hover:border-secondary/30 transition-all duration-300">
                <div class="stat">
                    <div
                        class="stat-figure text-secondary opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                    </div>
                    <div class="stat-title text-[10px] font-black uppercase tracking-widest text-base-content/50">Selesai
                        Tugas</div>
                    <div class="stat-value text-secondary">{{ number_format($stats['total_pulang']) }}</div>
                    <div class="stat-desc font-bold text-[9px] uppercase tracking-tighter mt-1">Absen pulang tercatat</div>
                </div>
            </div>

            {{-- Terlambat --}}
            <div
                class="stats shadow bg-base-100 border border-base-200 group hover:border-error/30 transition-all duration-300">
                <div class="stat">
                    <div
                        class="stat-figure text-error opacity-20 group-hover:opacity-100 group-hover:scale-110 transition-all duration-500">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-10 h-10">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-title text-[10px] font-black uppercase tracking-widest text-base-content/50">Terlambat
                    </div>
                    <div class="stat-value text-error">{{ number_format($stats['total_terlambat']) }}</div>
                    <div class="stat-desc font-bold text-[9px] uppercase tracking-tighter mt-1 text-error">Memerlukan
                        perhatian</div>
                </div>
            </div>
        @endif
    </div>

    {{-- Main Dashboard Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left Column (Main Content) --}}
        <div class="lg:col-span-2 space-y-8">

            {{-- Log Aktifitas --}}
            <div class="space-y-4">
                <div class="flex items-center justify-between px-2">
                    <div class="flex items-center gap-2">
                        <div class="h-8 w-1 bg-primary rounded-full"></div>
                        <h2 class="text-lg font-black text-base-content uppercase italic">Log Aktifitas Hari Ini</h2>
                    </div>
                    <button wire:click="$refresh" wire:loading.attr="disabled"
                        class="btn btn-ghost btn-xs gap-2 text-base-content/40 hover:text-primary disabled:bg-transparent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor" class="w-3 h-3" wire:loading.class="animate-spin">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <span wire:loading.remove>Refresh</span>
                        <span wire:loading>Memperbarui...</span>
                    </button>
                </div>

                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="table table-md">
                            <thead>
                                <tr class="bg-base-200/50">
                                    <th class="text-[10px] font-black uppercase tracking-widest">Personel</th>
                                    <th class="text-[10px] font-black uppercase tracking-widest">Waktu</th>
                                    <th class="text-[10px] font-black uppercase tracking-widest">Status</th>
                                    <th class="text-[10px] font-black uppercase tracking-widest">Detail</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if (!$readyToLoad)
                                    @for ($i = 0; $i < 5; $i++)
                                        <tr>
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="skeleton w-10 h-10 mask mask-squircle"></div>
                                                    <div class="flex flex-col gap-2">
                                                        <div class="skeleton h-3 w-28"></div>
                                                        <div class="skeleton h-2 w-20"></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><div class="skeleton h-4 w-16"></div></td>
                                            <td><div class="skeleton h-5 w-12 rounded-full"></div></td>
                                            <td><div class="skeleton h-3 w-24"></div></td>
                                        </tr>
                                    @endfor
                                @else
                                    @forelse($activities as $log)
                                        <tr
                                            class="hover:bg-base-200/30 transition-colors border-b border-base-200/50 last:border-0 group {{ $log->status === 'LIBUR' ? 'bg-base-200/80 opacity-25 grayscale' : '' }}">
                                            <td>
                                                <div class="flex items-center gap-3">
                                                    <div class="avatar">
                                                        <div class="mask mask-squircle w-10 h-10 bg-base-200">
                                                            @if ($log->personnel->foto)
                                                                <img src="{{ asset('storage/' . $log->personnel->foto) }}"
                                                                    alt="Avatar" />
                                                            @else
                                                                <div
                                                                    class="flex items-center justify-center h-full text-base-content/20">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                                        viewBox="0 0 24 24" stroke-width="1.5"
                                                                        stroke="currentColor" class="w-6 h-6">
                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                                    </svg>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-col">
                                                        <span
                                                            class="text-xs font-black text-base-content uppercase tracking-tight">{{ $log->personnel->name }}</span>
                                                        <span
                                                            class="text-[9px] font-bold text-base-content/40 uppercase">{{ $log->personnel->opd->name }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    @if ($log->jam_masuk)
                                                        <div class="flex items-center gap-1.5">
                                                            <div class="h-1.5 w-1.5 rounded-full bg-success"></div>
                                                            <span class="text-[11px] font-bold text-base-content/70">In:
                                                                {{ \Carbon\Carbon::parse($log->jam_masuk)->format('H:i') }}</span>
                                                        </div>
                                                    @endif
                                                    @if ($log->jam_pulang)
                                                        <div class="flex items-center gap-1.5">
                                                            <div class="h-1.5 w-1.5 rounded-full bg-secondary"></div>
                                                            <span class="text-[11px] font-bold text-base-content/70">Out:
                                                                {{ \Carbon\Carbon::parse($log->jam_pulang)->format('H:i') }}</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    @if ($log->status === 'ALFA')
                                                        <span
                                                            class="badge badge-error badge-xs font-black text-[8px] tracking-widest uppercase">ALFA</span>
                                                    @elseif($log->status === 'LIBUR')
                                                        <span
                                                            class="badge badge-neutral badge-xs font-black text-[8px] tracking-widest uppercase">LIBUR</span>
                                                    @endif

                                                    @if ($log->status_masuk)
                                                        <span
                                                            class="badge badge-{{ $log->status_masuk === 'HADIR' ? 'success' : 'warning' }} badge-xs font-black text-[8px] tracking-widest">{{ $log->status_masuk }}</span>
                                                    @endif
                                                    @if ($log->status_pulang)
                                                        <span
                                                            class="badge badge-secondary badge-xs font-black text-[8px] tracking-widest uppercase">{{ $log->status_pulang }}</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="flex flex-col gap-1">
                                                    @if ($log->is_within_radius === true)
                                                        <span
                                                            class="text-[9px] font-bold text-success/70 flex items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor" class="w-3 h-3">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Radius OK
                                                        </span>
                                                    @elseif($log->is_within_radius === false)
                                                        <span
                                                            class="text-[9px] font-bold text-error/70 flex items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                                fill="currentColor" class="w-3 h-3">
                                                                <path fill-rule="evenodd"
                                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                                                                    clip-rule="evenodd" />
                                                            </svg>
                                                            Luar Radius
                                                        </span>
                                                    @endif
                                                    <span
                                                        class="text-[9px] text-base-content/40 font-medium italic truncate max-w-25">
                                                        {{ $log->keterangan ?: '-' }}
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="py-12 text-center">
                                                <div class="flex flex-col items-center opacity-20">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"
                                                        class="w-16 h-16 mb-2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                                    </svg>
                                                    <p class="text-sm font-black uppercase tracking-widest">Belum ada
                                                        aktifitas hari ini</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                @endif
                            </tbody>
                        </table>
                    </div>
                    @if ($activities->count() > 0)
                        <div class="p-4 bg-base-200/30 border-t border-base-200/50 space-y-4">
                            <div class="flex items-center justify-center gap-6">
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-success"></div>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest opacity-60">HADIR:</span>
                                    <span class="text-xs font-black text-success">{{ $stats['total_hadir'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-error"></div>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest opacity-60">ALFA:</span>
                                    <span class="text-xs font-black text-error">{{ $stats['total_alfa'] }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-error"></div>
                                    <span
                                        class="text-[10px] font-black uppercase tracking-widest opacity-60">TELAT:</span>
                                    <span class="text-xs font-black text-error">{{ $stats['total_telat'] }}</span>
                                </div>
                            </div>
                            <a href="{{ route('absensi') }}"
                                class="btn btn-sm btn-neutral btn-block text-[10px] font-black uppercase tracking-widest gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="size-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Lihat Semua Data Monitoring
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Monitoring Hub: Late & Absent --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Pegawai Terlambat --}}
                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="p-4 bg-error/5 border-b border-base-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-xs font-black uppercase tracking-widest italic">Terlambat Hari Ini</h3>
                        </div>
                        <span
                            class="badge badge-error badge-sm font-black text-[10px]">{{ $latePersonnel->count() }}</span>
                    </div>
                    <div class="max-h-100 overflow-y-auto divide-y divide-base-200">
                        @forelse($latePersonnel as $late)
                            <div class="flex items-center gap-4 p-4 hover:bg-base-200/30 transition-colors">
                                <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                        @if ($late->personnel->foto)
                                            <img src="{{ asset('storage/' . $late->personnel->foto) }}" />
                                        @else
                                            <img
                                                src="https://ui-avatars.com/api/?name={{ urlencode($late->personnel->name) }}&background=fee2e2&color=ef4444" />
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-black text-base-content uppercase truncate">
                                        {{ $late->personnel->name }}</div>
                                    <div
                                        class="text-[9px] font-bold text-base-content/40 uppercase truncate tracking-tighter">
                                        {{ $late->personnel->opd->name }}</div>
                                </div>
                                <div class="flex-none text-right">
                                    <div class="text-xs font-black text-error italic uppercase">
                                        {{ $late->jam_masuk?->format('H:i') }}</div>
                                    <div class="text-[8px] font-bold text-base-content/30 uppercase tracking-widest">
                                        Jam Masuk</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="opacity-20 italic text-xs uppercase font-black tracking-widest mb-1">Tidak
                                    Ada Keterlambatan</div>
                                <div class="text-[9px] font-bold text-base-content/30 uppercase">Semua staff hadir
                                    tepat waktu</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Belum Absen --}}
                <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                    <div class="p-4 bg-warning/5 border-b border-base-200 flex items-center justify-between">
                        <div class="flex items-center gap-2 text-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <h3 class="text-xs font-black uppercase tracking-widest italic">Belum Absen Masuk</h3>
                        </div>
                        <span
                            class="badge badge-warning badge-sm font-black text-[10px]">{{ $absentPersonnel->count() }}</span>
                    </div>
                    <div class="max-h-100 overflow-y-auto divide-y divide-base-200">
                        @forelse($absentPersonnel as $absent)
                            <div class="flex items-center gap-4 p-4 hover:bg-base-200/30 transition-colors">
                                <div class="avatar">
                                    <div class="mask mask-squircle w-12 h-12">
                                        @if ($absent->personnel->foto)
                                            <img src="{{ asset('storage/' . $absent->personnel->foto) }}" />
                                        @else
                                            <img
                                                src="https://ui-avatars.com/api/?name={{ urlencode($absent->personnel->name) }}&background=fef3c7&color=d97706" />
                                        @endif
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-[11px] font-black text-base-content uppercase truncate">
                                        {{ $absent->personnel->name }}</div>
                                    <div
                                        class="text-[9px] font-bold text-base-content/40 uppercase truncate tracking-tighter">
                                        {{ $absent->personnel->opd->name }}</div>
                                </div>
                                <div class="flex-none">
                                    <span
                                        class="badge badge-warning badge-outline text-[8px] font-black uppercase px-2">{{ $absent->jadwal?->shift?->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-12 text-center">
                                <div class="opacity-20 italic text-xs uppercase font-black tracking-widest mb-1">Semua
                                    Telah Absen</div>
                                <div class="text-[9px] font-bold text-base-content/30 uppercase">Monitoring kehadiran
                                    lengkap</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Statistik: Weekly Chart --}}
            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div
                    class="p-6 border-b border-base-200 bg-base-200/30 flex flex-col xl:flex-row xl:items-center justify-between gap-6">
                    <div>
                        <h3 class="text-sm font-black uppercase tracking-widest italic leading-none">Statistik
                            Kedisiplinan</h3>
                        <p class="text-[10px] font-bold text-base-content/40 uppercase mt-1 tracking-widest">Aktivitas
                            7 Hari Terakhir</p>
                    </div>

                    <div class="flex flex-wrap items-center gap-6">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500 shadow-sm"></span>
                            <span class="text-[9px] font-black uppercase text-base-content/60 tracking-widest">Tepat
                                Waktu</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500 shadow-sm"></span>
                            <span
                                class="text-[9px] font-black uppercase text-base-content/60 tracking-widest">Alfa</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-amber-500 shadow-sm"></span>
                            <span
                                class="text-[9px] font-black uppercase text-base-content/60 tracking-widest">Terlambat</span>
                        </div>
                        <div class="h-4 w-px bg-base-300 hidden sm:block"></div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <div class="text-[10px] font-black text-base-content leading-none">
                                    {{ collect($weeklyStats)->sum('total') }}</div>
                                <div class="text-[7px] font-bold text-base-content/30 uppercase">Total Absen</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-4 sm:p-10 overflow-x-auto min-h-[350px]">
                    @if (!$readyToLoad)
                        <div class="flex flex-col items-center justify-center h-64 gap-4 opacity-20">
                            <div class="loading loading-spinner loading-lg text-primary"></div>
                            <span class="text-[10px] font-black uppercase tracking-[0.3em]">Mengkalkulasi Tren...</span>
                        </div>
                    @else
                        @php
                            $totalValues = array_column($weeklyStats, 'total');
                            $maxVal = !empty($totalValues) ? max($totalValues) : 10;
                            if ($maxVal <= 0)
                                $maxVal = 10;
                            $count = count($weeklyStats);

                            $generatePath = function ($key) use ($weeklyStats, $maxVal, $count) {
                                if ($count < 2)
                                    return 'M 0,100 L 100,100';
                                $pts = [];
                                foreach ($weeklyStats as $i => $stat) {
                                    $pts[] = [
                                        'x' => ($i / ($count - 1)) * 100,
                                        'y' => 100 - ($stat[$key] / $maxVal) * 100,
                                    ];
                                }

                                $path = 'M ' . $pts[0]['x'] . ',' . $pts[0]['y'];
                                for ($i = 0; $i < count($pts) - 1; $i++) {
                                    $curr = $pts[$i];
                                    $next = $pts[$i + 1];
                                    $cp1x = $curr['x'] + ($next['x'] - $curr['x']) / 2;
                                    $cp2x = $curr['x'] + ($next['x'] - $curr['x']) / 2;
                                    $path .= " C $cp1x," . $curr['y'] . " $cp2x," . $next['y'] . ' ' . $next['x'] . ',' . $next['y'];
                                }
                                return $path;
                            };

                            $pathOntime = $generatePath('ontime');
                            $pathAlfa = $generatePath('alfa');
                            $pathLate = $generatePath('late');
                        @endphp

                        <div class="min-w-125 h-72 relative px-2">
                            {{-- SVG Chart --}}
                            <svg class="w-full h-full overflow-visible" viewBox="0 0 100 100"
                                preserveAspectRatio="none">
                                {{-- Grid Lines --}}
                                @foreach ([0, 25, 50, 75, 100] as $grid)
                                    <line x1="0" y1="{{ $grid }}" x2="100"
                                        y2="{{ $grid }}" stroke="currentColor" stroke-width="0.1"
                                        class="text-base-content/10" />
                                @endforeach

                                {{-- Shaded Areas (Gradients) --}}
                                <defs>
                                    <linearGradient id="grad-ontime" x1="0" y1="0" x2="0"
                                        y2="1">
                                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.15" />
                                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                                    </linearGradient>
                                    <linearGradient id="grad-alfa" x1="0" y1="0" x2="0"
                                        y2="1">
                                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.15" />
                                        <stop offset="100%" stop-color="#ef4444" stop-opacity="0" />
                                    </linearGradient>
                                </defs>

                                {{-- Area Fills --}}
                                <path d="{{ $pathOntime }} L 100,100 L 0,100 Z" fill="url(#grad-ontime)" />
                                <path d="{{ $pathAlfa }} L 100,100 L 0,100 Z" fill="url(#grad-alfa)" />

                                {{-- Smooth Lines --}}
                                <path d="{{ $pathOntime }}" fill="none" stroke="#3b82f6" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="{{ $pathLate }}" fill="none" stroke="#f59e0b" stroke-width="1"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="2,2" />
                                <path d="{{ $pathAlfa }}" fill="none" stroke="#ef4444" stroke-width="1.5"
                                    stroke-linecap="round" stroke-linejoin="round" />

                                {{-- Points --}}
                                @foreach ($weeklyStats as $i => $stat)
                                    @php $x = ($i / ($count - 1)) * 100; @endphp
                                    <circle cx="{{ $x }}" cy="{{ 100 - ($stat['ontime'] / $maxVal) * 100 }}"
                                        r="0.8" fill="white" stroke="#3b82f6" stroke-width="0.5" />
                                    <circle cx="{{ $x }}" cy="{{ 100 - ($stat['alfa'] / $maxVal) * 100 }}"
                                        r="0.8" fill="white" stroke="#ef4444" stroke-width="0.5" />
                                @endforeach
                            </svg>

                            {{-- X-Axis Labels --}}
                            <div class="absolute -bottom-6 left-0 right-0 flex justify-between">
                                @foreach ($weeklyStats as $stat)
                                    <div class="flex flex-col items-center">
                                        <span
                                            class="text-[9px] font-black {{ $stat['date'] === date('Y-m-d') ? 'text-primary' : 'text-base-content/40' }} uppercase tracking-widest">
                                            {{ $stat['label'] }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Scroll Indicator for Mobile --}}
                        <div class="lg:hidden flex items-center justify-center gap-2 mt-12 opacity-30">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                            </svg>
                            <span class="text-[8px] font-black uppercase tracking-widest">Geser untuk tren</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </div>
                    @endif

                    <div class="mt-8"></div>
                </div>
            </div>
        </div>

        {{-- Side Column (Right) --}}
        <div class="space-y-8">
            {{-- Quick Actions: Leave Approval --}}
            <div class="card bg-base-100 shadow-xl border border-base-200 overflow-hidden">
                <div class="p-4 bg-base-200/50 border-b border-base-200 flex items-center justify-between">
                    <h3 class="text-xs font-black text-base-content/70 uppercase tracking-widest italic">Aksi Cepat:
                        Cuti</h3>
                    @if ($stats['pending_leaves_count'] > 0)
                        <span
                            class="badge badge-error badge-xs font-black text-[8px]">{{ $stats['pending_leaves_count'] }}</span>
                    @endif
                </div>
                <div class="p-4 space-y-3">
                    @forelse($pendingLeaves as $leave)
                        <div class="flex flex-col p-3 rounded-xl bg-base-200/30 border border-base-200 group relative">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex flex-col">
                                    <span
                                        class="text-[11px] font-black text-base-content uppercase tracking-tight">{{ $leave->personnel->name }}</span>
                                    <span
                                        class="text-[9px] font-bold text-primary uppercase">{{ $leave->cuti->name }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button wire:click="approveLeave({{ $leave->id }})"
                                        wire:target="approveLeave({{ $leave->id }})" wire:loading.attr="disabled"
                                        class="btn btn-square btn-xs btn-success rounded-lg hover:scale-110 transition-transform shadow-sm"
                                        title="Setujui">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" class="w-3 h-3">
                                            <path fill-rule="evenodd"
                                                d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button wire:click="rejectLeave({{ $leave->id }})"
                                        wire:target="rejectLeave({{ $leave->id }})" wire:loading.attr="disabled"
                                        class="btn btn-square btn-xs btn-error rounded-lg hover:scale-110 transition-transform shadow-sm"
                                        title="Tolak">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" class="w-3 h-3">
                                            <path
                                                d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 text-[9px] font-bold text-base-content/40 uppercase">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="w-3 h-3">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                {{ $leave->tanggal_mulai->format('d/m') }} -
                                {{ $leave->tanggal_selesai->format('d/m') }}
                                <span>({{ $leave->tanggal_mulai->diffInDays($leave->tanggal_selesai) + 1 }}
                                    Hari)</span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center">
                            <div
                                class="w-10 h-10 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-2 opacity-20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-[10px] font-black text-base-content/20 uppercase tracking-widest">Antrian
                                Kosong</p>
                        </div>
                    @endforelse
                </div>
                @if ($stats['pending_leaves_count'] > 0)
                    <div class="p-3 bg-base-200/50 border-t border-base-200">
                        <a href="{{ route('permohonan-cuti') }}"
                            class="btn btn-ghost btn-xs btn-block text-[9px] font-black uppercase tracking-widest opacity-60 hover:opacity-100">
                            Kelola Semua Permohonan
                        </a>
                    </div>
                @endif
            </div>

            {{-- Kinerja Analytics --}}
            <div class="card bg-base-100 shadow-xl border border-base-200 p-6 space-y-4">
                <h3 class="text-xs font-black text-base-content/50 uppercase tracking-widest italic">Kinerja Attendance
                </h3>
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-base-content/70">Kehadiran Hari Ini</span>
                    <span class="text-sm font-black text-primary">{{ $stats['hadir_percentage'] }}%</span>
                </div>
                <progress class="progress progress-primary w-full h-1.5" value="{{ $stats['hadir_percentage'] }}"
                    max="100"></progress>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div class="p-3 bg-base-200/50 rounded-xl border border-base-200">
                        <span
                            class="block text-[8px] font-black text-base-content/40 uppercase tracking-widest mb-1">Masuk</span>
                        <span class="text-lg font-black text-success">{{ $stats['total_masuk'] }}</span>
                    </div>
                    <div class="p-3 bg-base-200/50 rounded-xl border border-base-200">
                        <span
                            class="block text-[8px] font-black text-base-content/40 uppercase tracking-widest mb-1">Pulang</span>
                        <span class="text-lg font-black text-secondary">{{ $stats['total_pulang'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Help Card --}}
            <div class="card bg-primary text-primary-content shadow-xl p-6 relative overflow-hidden group">
                <div
                    class="absolute -right-4 -bottom-4 opacity-10 group-hover:scale-110 transition-transform duration-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-32 h-32">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <div class="relative z-10 space-y-3">
                    <h3 class="text-lg font-black uppercase italic tracking-tighter leading-none">Bantuan &
                        <br>Informasi
                    </h3>
                    <p class="text-xs font-medium opacity-80 leading-relaxed uppercase tracking-tighter">
                        Butuh bantuan dalam mengelola absensi? <br>Klik tombol di bawah ini.
                    </p>
                    <button
                        class="btn btn-sm bg-white/20 hover:bg-white/30 border-none text-white text-[10px] font-black uppercase tracking-widest mt-2">
                        Pusat Bantuan
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
