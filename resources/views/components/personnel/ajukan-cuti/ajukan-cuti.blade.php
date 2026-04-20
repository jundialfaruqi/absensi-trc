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

                <div class="space-y-12">
                    @forelse($this->myRequests as $req)
                        @php
                            $statusColor = match ($req->status) {
                                'PENDING' => 'amber',
                                'APPROVED' => 'emerald',
                                'REJECTED' => 'red',
                                default => 'slate',
                            };
                            $avatarColor = match ($req->status) {
                                'PENDING' => 'F59E0B',
                                'APPROVED' => '10B981',
                                'REJECTED' => 'EF4444',
                                default => '64748B',
                            };
                        @endphp
                        
                        <div class="space-y-4 animate-in slide-in-from-bottom-4 duration-500">
                            {{-- Personnel Request Bubble --}}
                            <div class="chat chat-start group">
                                <div class="chat-image avatar">
                                    <div class="w-10 rounded-xl border border-white/10 shadow-lg">
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($this->personnel->name) }}&background={{ $avatarColor }}&color=fff&bold=true" />
                                    </div>
                                </div>
                                <div class="chat-header mb-1.5 flex items-center gap-2">
                                    <span class="text-[10px] font-black text-white uppercase italic tracking-wider">{{ $this->personnel->name }}</span>
                                    <time class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">{{ $req->created_at->diffForHumans() }}</time>
                                </div>
                                <div class="chat-bubble chat-bubble-neutral bg-white/5 border border-white/10 text-white min-w-[200px] p-4 rounded-2xl shadow-xl backdrop-blur-md group-hover:border-white/20 transition-all">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-0.5 rounded bg-{{ $statusColor }}-500/10 border border-{{ $statusColor }}-500/20 text-[8px] font-black text-{{ $statusColor }}-500 uppercase italic">
                                            {{ $req->cuti->name }}
                                        </span>
                                    </div>
                                    <div class="text-[11px] font-bold leading-relaxed mb-3">
                                        Mengajukan permohonan {{ strtolower($req->cuti->name) }} dari tanggal 
                                        <span class="text-blue-400">{{ $req->tanggal_mulai->translatedFormat('d M') }}</span> s/d 
                                        <span class="text-blue-400">{{ $req->tanggal_selesai->translatedFormat('d M Y') }}</span>.
                                    </div>
                                    <div class="p-3 bg-black/20 rounded-xl border border-white/5 italic text-[10px] text-slate-400 leading-snug">
                                        "{{ $req->alasan }}"
                                    </div>
                                </div>
                                <div class="chat-footer opacity-60 mt-1.5 flex items-center gap-2">
                                    <span class="text-[9px] font-black text-{{ $statusColor }}-500 uppercase italic tracking-widest">{{ $req->status }}</span>
                                    @if($req->status === 'PENDING')
                                        <button wire:click="cancelRequest({{ $req->id }})" class="btn btn-ghost btn-xs h-auto min-h-0 py-1 px-2 text-[8px] font-black text-red-400 hover:bg-red-500/10 uppercase italic tracking-tighter">Batalkan Pengajuan</button>
                                    @endif
                                </div>
                            </div>

                            {{-- Admin Response Bubble (if processed) --}}
                            @if($req->status !== 'PENDING')
                                <div class="chat chat-end animate-in fade-in slide-in-from-right-4 duration-700 delay-300">
                                    <div class="chat-image avatar">
                                        <div class="w-10 rounded-xl border border-white/10 shadow-lg bg-slate-800 flex items-center justify-center p-1">
                                            <img src="{{ asset('assets/logo/trc-logo.webp') }}" class="object-contain" />
                                        </div>
                                    </div>
                                    <div class="chat-header mb-1.5 flex flex-row-reverse items-center gap-2">
                                        <span class="text-[10px] font-black text-white uppercase italic tracking-wider">Admin TRC</span>
                                        <time class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">{{ $req->processed_at?->diffForHumans() }}</time>
                                    </div>
                                    <div class="chat-bubble {{ $req->status === 'APPROVED' ? 'chat-bubble-success bg-emerald-500/10 border-emerald-500/20' : 'chat-bubble-error bg-red-500/10 border-red-500/20' }} border text-white p-4 rounded-2xl shadow-xl backdrop-blur-md">
                                        <div class="text-[11px] font-black uppercase italic tracking-widest mb-1.5 {{ $req->status === 'APPROVED' ? 'text-emerald-400' : 'text-red-400' }}">
                                            Permohonan {{ $req->status === 'APPROVED' ? 'Disetujui' : 'Ditolak' }}
                                        </div>
                                        @if($req->admin_note)
                                            <div class="text-[11px] font-medium leading-relaxed italic text-slate-300">
                                                "{{ $req->admin_note }}"
                                            </div>
                                        @endif
                                    </div>
                                    <div class="chat-footer opacity-60 mt-1.5 flex flex-row-reverse gap-2">
                                        <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest">Sistem TRC Pekanbaru</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center py-24 bg-white/2 rounded-[2.5rem] border border-dashed border-white/10">
                            <div class="h-20 w-20 rounded-[2rem] bg-white/5 flex items-center justify-center text-slate-600 mb-6 scale-110">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                </svg>
                            </div>
                            <p class="text-xs font-black text-slate-500 uppercase italic tracking-[0.2em]">Belum ada riwayat percakapan</p>
                        </div>
                    @endforelse

                    @if($this->hasMore)
                        <div class="flex justify-center pt-8">
                            <button wire:click="loadMore" wire:loading.attr="disabled" class="group flex flex-col items-center gap-3">
                                <div class="p-4 bg-white/5 border border-white/10 rounded-2xl hover:bg-white/10 hover:border-blue-500/30 transition-all group-active:scale-95">
                                    <span wire:loading.remove wire:target="loadMore">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-hover:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7" />
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="loadMore" class="loading loading-spinner loading-xs text-blue-400"></span>
                                </div>
                                <span class="text-[10px] font-black text-slate-500 group-hover:text-white uppercase italic tracking-[0.2em] transition-colors">Muat Lebih Banyak</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
