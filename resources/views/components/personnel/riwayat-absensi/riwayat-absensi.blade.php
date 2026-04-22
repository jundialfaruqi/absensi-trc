<div class="space-y-8 animate-in fade-in duration-700">
    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div class="space-y-1">
            <a href="{{ url('/personnel/dashboard') }}"
                class="group inline-flex items-center gap-2 text-slate-500 hover:text-blue-400 text-[10px] font-black uppercase tracking-widest transition-colors mb-2">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4 transform group-hover:-translate-x-1 transition-transform" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard
            </a>
            <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter">Riwayat Absensi</h1>
            <p class="text-slate-400 font-medium text-sm">Monitoring kehadiran Anda secara berkala.</p>
        </div>

        {{-- Filters --}}
        <div class="flex items-center gap-3 bg-white/5 p-2 rounded-2xl border border-white/5 backdrop-blur-sm">
            <select wire:model.live="month"
                class="bg-transparent text-xs font-black text-white uppercase tracking-widest border-none focus:ring-0 cursor-pointer p-2 outline-none">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" class="bg-[#0a192f] text-white">
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <div class="w-px h-4 bg-white/10"></div>
            <select wire:model.live="year"
                class="bg-transparent text-xs font-black text-white uppercase tracking-widest border-none focus:ring-0 cursor-pointer p-2 outline-none">
                @for ($y = date('Y') - 1; $y <= date('Y'); $y++)
                    <option value="{{ $y }}" class="bg-[#0a192f] text-white">{{ $y }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Content --}}
    <div class="glass-panel rounded-2xl border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 bg-white/2">
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Tanggal /
                            Shift</th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Masuk
                        </th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Pulang
                        </th>
                        <th class="px-6 py-5 text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">
                            Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse ($this->riwayat as $item)
                        @php
                            $isAlfa = $item->status === 'ALFA' && !$item->jam_masuk;
                            $isLibur = $item->status === 'LIBUR';
                            $rowClass = $isAlfa ? 'bg-red-500/5' : ($isLibur ? 'bg-blue-500/5' : '');
                        @endphp
                        <tr class="group hover:bg-white/2 transition-colors {{ $rowClass }}">
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-black text-white italic uppercase tracking-tighter">
                                        {{ $item->tanggal->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">
                                        {{ $item->jadwal?->shift?->name ?? ($isLibur ? 'LIBUR' : 'OFF SCHEDULE') }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    @if ($item->foto_masuk)
                                        <div
                                            class="h-10 w-10 rounded-lg border border-white/10 overflow-hidden shrink-0">
                                            <img src="{{ asset('storage/' . $item->foto_masuk) }}"
                                                class="h-full w-full object-cover">
                                        </div>
                                    @endif
                                    <div class="flex flex-col gap-1">
                                        @if ($item->jam_masuk)
                                            <span class="text-sm font-black text-white italic">
                                                {{ \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') }}
                                            </span>
                                            <span
                                                class="text-[8px] font-bold uppercase tracking-widest {{ in_array($item->status_masuk, ['HADIR', 'DINAS']) ? 'text-emerald-500' : 'text-amber-500' }}">
                                                {{ $item->status_masuk }}
                                            </span>
                                        @elseif($isAlfa)
                                            <span
                                                class="text-[10px] font-black text-red-500 uppercase tracking-widest italic">ALFA</span>
                                        @elseif($isLibur)
                                            <span
                                                class="text-[10px] font-black text-blue-400 uppercase tracking-widest italic">LIBUR</span>
                                        @else
                                            <span
                                                class="text-xs font-bold text-slate-500 uppercase tracking-tighter italic">{{ $item->status_masuk ?: $item->status }}</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex items-center gap-4">
                                    @if ($item->foto_pulang)
                                        <div
                                            class="h-10 w-10 rounded-lg border border-white/10 overflow-hidden shrink-0">
                                            <img src="{{ asset('storage/' . $item->foto_pulang) }}"
                                                class="h-full w-full object-cover">
                                        </div>
                                    @endif
                                    <div class="flex flex-col gap-1">
                                        @if ($item->jam_pulang)
                                            <span class="text-sm font-black text-white italic">
                                                {{ \Carbon\Carbon::parse($item->jam_pulang)->format('H:i') }}
                                            </span>
                                            <span
                                                class="text-[8px] font-bold uppercase tracking-widest {{ $item->status_pulang === 'HADIR' ? 'text-emerald-500' : 'text-amber-500' }}">
                                                {{ $item->status_pulang }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-slate-600 italic">--:--</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-[10px] font-medium text-slate-400">
                                        {{ $item->status ?: '-' }}
                                    </span>
                                    @if ($item->edited_at)
                                        <span
                                            class="text-[8px] font-black text-blue-400/50 uppercase italic tracking-tighter">DIEDIT
                                            ADMIN</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-20 text-center">
                                <div class="flex flex-col items-center gap-3">
                                    <div
                                        class="h-12 w-12 rounded-2xl bg-white/5 flex items-center justify-center text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <p class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Belum
                                        ada riwayat absensi</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="mt-6 [&_nav]:flex [&_nav]:flex-col [&_nav]:md:flex-row [&_nav]:items-center [&_nav]:justify-between [&_nav]:gap-6 [&_p]:text-[10px] [&_p]:font-black [&_p]:text-slate-500 [&_p]:uppercase [&_p]:tracking-[0.2em] [&_p]:leading-none">
        {{ $this->riwayat->links() }}
    </div>
</div>
