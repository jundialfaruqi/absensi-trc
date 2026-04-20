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
            <p class="text-slate-400 font-medium text-sm">Daftar kehadiran Anda dalam 50 record terakhir.</p>
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
                        <tr class="group hover:bg-white/2 transition-colors">
                            <td class="px-6 py-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-black text-white italic uppercase tracking-tighter">
                                        {{ $item->tanggal->translatedFormat('d F Y') }}
                                    </span>
                                    <span class="text-[9px] font-bold text-blue-400 uppercase tracking-widest">
                                        {{ $item->jadwal?->shift?->nama ?? 'Shift Khusus' }}
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
                                                class="text-[8px] font-bold uppercase tracking-widest {{ $item->status_masuk === 'tepat_waktu' ? 'text-emerald-500' : 'text-amber-500' }}">
                                                {{ str_replace('_', ' ', $item->status_masuk) }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-slate-600 italic">BELUM ABSEN</span>
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
                                                class="text-[8px] font-bold uppercase tracking-widest {{ $item->status_pulang === 'tepat_waktu' ? 'text-emerald-500' : 'text-amber-500' }}">
                                                {{ str_replace('_', ' ', $item->status_pulang) }}
                                            </span>
                                        @else
                                            <span class="text-xs font-bold text-slate-600 italic">-</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-6">
                                <span class="text-[10px] font-medium text-slate-400 capitalize">
                                    {{ $item->keterangan ?: '-' }}
                                </span>
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
</div>
