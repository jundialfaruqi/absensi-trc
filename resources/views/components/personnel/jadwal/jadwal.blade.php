<div class="space-y-8 animate-in fade-in duration-700">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
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
            <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter">Jadwal Tugas</h1>
            <p class="text-slate-400 font-medium text-sm">Monitor penugasan shift Anda bulan ini.</p>
        </div>

        {{-- Filters --}}
        <div class="flex items-center gap-3 bg-white/5 p-2 rounded-2xl border border-white/5 backdrop-blur-sm">
            <select wire:model.live="month"
                class="bg-transparent text-xs font-black text-white uppercase tracking-widest border-none focus:ring-0 cursor-pointer p-2">
                @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}" class="bg-[#0a192f] text-white">
                        {{ \Carbon\Carbon::create()->month($i)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
            <div class="w-px h-4 bg-white/10"></div>
            <select wire:model.live="year"
                class="bg-transparent text-xs font-black text-white uppercase tracking-widest border-none focus:ring-0 cursor-pointer p-2">
                @for ($i = date('Y') - 1; $i <= date('Y') + 1; $i++)
                    <option value="{{ $i }}" class="bg-[#0a192f] text-white">{{ $i }}</option>
                @endfor
            </select>
        </div>
    </div>

    {{-- Schedule Matrix --}}
    <div class="glass-panel rounded-2xl border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-white/2">
                        <th
                            class="sticky left-0 z-20 bg-[#0d213f] border-b border-r border-white/5 p-6 min-w-40 text-left">
                            <span
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em]">Personnel</span>
                        </th>
                        @foreach ($this->dates as $date)
                            @php
                                $carbonDate = \Carbon\Carbon::parse($date);
                                $isToday = $carbonDate->isToday();
                                $isWeekend = $carbonDate->isWeekend();
                            @endphp
                            <th
                                class="border-b border-r border-white/5 min-w-20 p-4 {{ $isToday ? 'bg-blue-500/10' : ($isWeekend ? 'bg-white/1' : '') }}">
                                <div class="flex flex-col items-center gap-1">
                                    <span
                                        class="text-[10px] font-black {{ $isWeekend ? 'text-red-400/60' : 'text-slate-500' }} uppercase opacity-50 tracking-widest">
                                        {{ $carbonDate->translatedFormat('D') }}
                                    </span>
                                    <span
                                        class="text-sm font-black {{ $isToday ? 'text-blue-400' : ($isWeekend ? 'text-red-400' : 'text-white') }}">
                                        {{ $carbonDate->format('d') }}
                                    </span>
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr class="group">
                        <td class="sticky left-0 z-10 bg-[#0d213f] border-r border-white/5 p-6">
                            <div class="flex items-center gap-4">
                                <div class="h-10 w-10 rounded-xl border border-white/10 overflow-hidden shrink-0">
                                    <img src="{{ $personnel->foto ? asset('storage/' . $personnel->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($personnel->name) . '&size=256&background=1e293b&color=38bdf8' }}"
                                        class="h-full w-full object-cover">
                                </div>
                                <div class="flex flex-col truncate">
                                    <span
                                        class="text-xs font-black text-white uppercase italic tracking-tight truncate">{{ $personnel->name }}</span>
                                    <span
                                        class="text-[9px] font-bold text-slate-500 uppercase tracking-widest truncate">{{ $personnel->penugasan?->name ?? 'TRC Unit' }}</span>
                                </div>
                            </div>
                        </td>
                        @foreach ($this->dates as $date)
                            @php
                                $j = $this->jadwalMap[$date] ?? null;
                                $isToday = \Carbon\Carbon::parse($date)->isToday();

                                $bgColor = '';
                                $textColor = 'text-white';

                                if ($j) {
                                    if ($j->status === 'SHIFT') {
                                        $bgColor = $j->shift->color ?? '#3b82f6';
                                    } elseif ($j->status === 'LIBUR') {
                                        $bgColor = '#1e293b';
                                        $textColor = 'text-slate-500';
                                    } else {
                                        $bgColor = '#6366f1';
                                    }
                                }
                            @endphp
                            <td
                                class="border-r border-white/5 p-0 h-28 relative {{ $isToday ? 'bg-blue-500/5' : '' }}">
                                @if ($j)
                                    <div class="absolute inset-0 flex flex-col items-center justify-center p-2 text-center"
                                        style="background-color: {{ $bgColor }}20;">
                                        <div class="w-full h-1 absolute top-0"
                                            style="background-color: {{ $bgColor }}"></div>

                                        @if ($j->status === 'SHIFT')
                                            <span
                                                class="text-[10px] font-black uppercase tracking-tight {{ $textColor }} leading-tight mb-1">
                                                {{ $j->shift->name ?? 'N/A' }}
                                            </span>
                                            <span
                                                class="text-[8px] font-bold opacity-60 uppercase tracking-widest {{ $textColor }}">
                                                {{ $j->shift ? \Carbon\Carbon::parse($j->shift->start_time)->format('H:i') : '' }}
                                            </span>
                                        @else
                                            <span
                                                class="text-[10px] font-black uppercase tracking-widest {{ $textColor }}">
                                                {{ $j->status }}
                                            </span>
                                        @endif

                                        @if ($j->keterangan)
                                            <div
                                                class="mt-2 text-[7px] font-medium text-slate-400 uppercase leading-tight line-clamp-2 px-1">
                                                {{ $j->keterangan }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center opacity-[0.03]">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap items-center gap-6 px-4">
        <div class="flex items-center gap-2">
            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Penugasan Shift</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-2 w-2 rounded-full bg-slate-700"></div>
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Libur / Off</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="h-px w-4 bg-blue-500/30"></div>
            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest italic">Geser Horizontal Untuk
                Tanggal Lain</span>
        </div>
    </div>
</div>
