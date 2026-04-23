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

    {{-- Today's Focus Card --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="md:col-span-2 glass-panel p-6 rounded-3xl border border-white/5 relative overflow-hidden group">
            <div
                class="absolute -right-20 -top-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl group-hover:bg-blue-500/20 transition-all duration-700">
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-6 relative z-10">
                <div class="h-20 w-20 rounded-2xl border-2 border-white/10 overflow-hidden shrink-0 shadow-2xl">
                    <img src="{{ $personnel->foto ? asset('storage/' . $personnel->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($personnel->name) . '&size=256&background=1e293b&color=38bdf8' }}"
                        class="h-full w-full object-cover">
                </div>
                <div class="flex-1 text-center sm:text-left space-y-1">
                    <p class="text-[10px] font-black text-blue-400 uppercase tracking-[0.2em] italic">Penugasan Hari Ini
                    </p>
                    <h2 class="text-2xl font-black text-white uppercase italic tracking-tighter">{{ $personnel->name }}
                    </h2>
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mt-3">
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/5">
                            <div
                                class="h-2 w-2 rounded-full {{ $this->todayJadwal?->status === 'SHIFT' ? 'bg-blue-500 animate-pulse' : 'bg-slate-500' }}">
                            </div>
                            <span class="text-[10px] font-black text-white uppercase tracking-widest italic">
                                {{ $this->todayJadwal?->status === 'SHIFT' ? $this->todayJadwal->shift->name : ($this->todayJadwal?->status ?? 'LIBUR') }}
                            </span>
                        </div>
                        @if ($this->todayJadwal?->shift)
                            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white/5 border border-white/5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-slate-500" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="text-[10px] font-black text-slate-300 uppercase tracking-widest italic">
                                    {{ \Carbon\Carbon::parse($this->todayJadwal->shift->start_time)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($this->todayJadwal->shift->end_time)->format('H:i') }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="glass-panel p-6 rounded-3xl border border-white/5 flex flex-col justify-center">
            <p class="text-[10px] font-black text-slate-500 uppercase tracking-widest italic mb-2 text-center">Unit
                Penugasan</p>
            <div class="text-center">
                <h4 class="text-lg font-black text-white uppercase italic tracking-tight">
                    {{ $personnel->penugasan?->name ?? 'TRC UNIT' }}</h4>
                <p class="text-[9px] font-bold text-blue-400/60 uppercase tracking-widest mt-1">
                    {{ $personnel->opd?->name ?? 'SAR TRC' }}</p>
            </div>
        </div>
    </div>

    {{-- Schedule Matrix --}}
    <div class="glass-panel rounded-2xl border-white/5 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-separate border-spacing-0">
                <thead>
                    <tr class="bg-white/2">
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
