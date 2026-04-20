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
            <h1 class="text-3xl font-black text-white uppercase italic tracking-tighter">Ajukan Cuti</h1>
            <p class="text-slate-400 font-medium text-sm">Formulir permohonan izin dan cuti personil.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Form Section --}}
        <div class="lg:col-span-1 space-y-6">
            <div class="glass-panel p-8 rounded-[2.5rem] border-white/5 relative overflow-hidden">
                <div class="absolute -top-24 -right-24 h-48 w-48 bg-blue-600/10 blur-3xl rounded-full"></div>

                <h2 class="text-xl font-black text-white uppercase italic mb-6 relative z-10">Form Pengajuan</h2>

                @if (session()->has('success'))
                    <div
                        class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl animate-in zoom-in duration-300">
                        <div class="flex gap-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-500 shrink-0"
                                viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <p class="text-xs font-bold text-emerald-400 italic leading-snug">{{ session('success') }}
                            </p>
                        </div>
                    </div>
                @endif

                <form wire:submit.prevent="submit" class="space-y-6 relative z-10">
                    <div class="space-y-2.5">
                        <label
                            class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1 italic opacity-70">Jenis
                            Izin / Cuti</label>
                        <div class="relative group">
                            <select wire:model="cuti_id"
                                class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-5 text-sm text-white focus:outline-none focus:border-blue-500/50 focus:bg-white/10 transition-all cursor-pointer appearance-none">
                                <option value="" class="bg-[#0a192f]">-- Pilih Opsi --</option>
                                @foreach ($this->cutis as $c)
                                    <option value="{{ $c->id }}" class="bg-[#0a192f]">{{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div
                                class="absolute right-5 top-1/2 -translate-y-1/2 pointer-events-none text-slate-500 group-focus-within:text-blue-400 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                        @error('cuti_id')
                            <span
                                class="text-[9px] font-bold text-red-400 italic ml-1 tracking-wider uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-2.5">
                            <label
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1 italic opacity-70">Mulai
                                Tanggal</label>
                            <input type="date" wire:model="tanggal_mulai"
                                class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-5 text-sm text-white focus:outline-none focus:border-blue-500/50 focus:bg-white/10 transition-all [color-scheme:dark]">
                            @error('tanggal_mulai')
                                <span
                                    class="text-[9px] font-bold text-red-400 italic ml-1 tracking-wider uppercase">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="space-y-2.5">
                            <label
                                class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1 italic opacity-70">Sampai
                                Tanggal</label>
                            <input type="date" wire:model="tanggal_selesai"
                                class="w-full h-14 bg-white/5 border border-white/10 rounded-2xl px-5 text-sm text-white focus:outline-none focus:border-blue-500/50 focus:bg-white/10 transition-all [color-scheme:dark]">
                            @error('tanggal_selesai')
                                <span
                                    class="text-[9px] font-bold text-red-400 italic ml-1 tracking-wider uppercase">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="space-y-2.5">
                        <label
                            class="text-[10px] font-black text-slate-500 uppercase tracking-[0.15em] ml-1 italic opacity-70">Alasan
                            / Keperluan</label>
                        <textarea wire:model="alasan" rows="5"
                            class="w-full bg-white/5 border border-white/10 rounded-2xl p-5 text-sm text-white focus:outline-none focus:border-blue-500/50 focus:bg-white/10 transition-all placeholder:text-slate-600 leading-relaxed"
                            placeholder="Berikan penjelasan singkat mengenai alasan pengajuan Anda..."></textarea>
                        @error('alasan')
                            <span
                                class="text-[9px] font-bold text-red-400 italic ml-1 tracking-wider uppercase">{{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" wire:loading.attr="disabled"
                        class="w-full h-16 bg-blue-600 hover:bg-blue-500 text-white font-black uppercase rounded-2xl shadow-xl shadow-blue-600/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 group mt-2">
                        <span wire:loading.remove>Kirim Pengajuan</span>
                        <span wire:loading class="loading loading-spinner loading-sm text-white"></span>
                    </button>
                </form>
            </div>
        </div>

        {{-- History Section --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="glass-panel p-8 rounded-[2.5rem] border-white/5 relative overflow-hidden min-h-[400px]">
                <h2 class="text-xl font-black text-white uppercase italic mb-6 relative z-10">Riwayat Pengajuan</h2>

                <div class="space-y-4">
                    @forelse($this->myRequests as $req)
                        @php
                            $statusColor = match ($req->status) {
                                'PENDING' => 'amber',
                                'APPROVED' => 'emerald',
                                'REJECTED' => 'red',
                                default => 'slate',
                            };
                        @endphp
                        <div
                            class="p-6 bg-white/2 rounded-3xl border border-white/5 hover:border-white/10 transition-colors group">
                            <div class="flex flex-col sm:flex-row justify-between gap-4">
                                <div class="flex gap-4">
                                    <div
                                        class="h-12 w-12 rounded-2xl bg-{{ $statusColor }}-500/10 flex items-center justify-center text-{{ $statusColor }}-500 shrink-0">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-2">
                                            <span
                                                class="text-xs font-black text-white uppercase italic tracking-wider">{{ $req->cuti->name }}</span>
                                            <span
                                                class="px-2 py-0.5 rounded bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-[8px] font-black text-{{ $statusColor }}-500 uppercase italic">
                                                {{ $req->status }}
                                            </span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-400 italic">
                                            {{ $req->tanggal_mulai->translatedFormat('d M') }} -
                                            {{ $req->tanggal_selesai->translatedFormat('d M Y') }}
                                        </p>
                                        <div
                                            class="text-[11px] text-slate-500 font-medium leading-relaxed mt-2 line-clamp-2 italic">
                                            "{{ $req->alasan }}"
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-col items-end gap-2 shrink-0">
                                    @if ($req->status === 'PENDING')
                                        <button wire:click="cancelRequest({{ $req->id }})"
                                            class="text-[9px] font-black text-red-400 uppercase tracking-widest hover:text-red-300 transition-colors p-2 bg-red-400/5 rounded-lg border border-red-400/10">Batalkan</button>
                                    @endif

                                    @if ($req->status === 'REJECTED' && $req->admin_note)
                                        <div
                                            class="max-w-[200px] bg-red-500/5 border border-red-500/10 p-3 rounded-2xl mt-1">
                                            <p
                                                class="text-[8px] font-black text-red-500 uppercase tracking-widest mb-1 italic">
                                                Catatan Admin:</p>
                                            <p class="text-[10px] font-bold text-red-400 leading-tight italic">
                                                "{{ $req->admin_note }}"</p>
                                        </div>
                                    @endif

                                    @if ($req->status === 'APPROVED' && $req->processed_at)
                                        <p class="text-[8px] font-bold text-emerald-500/60 italic">Disetujui pada
                                            {{ $req->processed_at->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div
                            class="flex flex-col items-center justify-center py-20 bg-white/2 rounded-[2.5rem] border border-dashed border-white/5">
                            <div
                                class="h-16 w-16 rounded-3xl bg-white/5 flex items-center justify-center text-slate-600 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <p class="text-sm font-black text-slate-500 uppercase italic tracking-widest">Belum ada
                                riwayat pengajuan</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
