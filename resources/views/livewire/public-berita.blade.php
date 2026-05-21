<?php

use Livewire\Volt\Component;
use App\Models\Berita;

new class extends \Livewire\Component {
    public int $amount = 6;

    public function loadMore()
    {
        // Simulasi delay untuk melihat skeleton (opsional, bisa dihapus di production)
        // sleep(1);
        $this->amount += 6;
    }

    public function with(): array
    {
        return [
            'beritas' => Berita::latest()->take($this->amount)->get(),
            'total' => Berita::count()
        ];
    }
}; ?>

<div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($beritas as $berita)
            <div class="glass-panel rounded-4xl hover:border-blue-500/30 transition-all group overflow-hidden relative flex flex-col h-full border border-white/5">
                {{-- Image --}}
                <div class="w-full h-48 overflow-hidden bg-slate-800 shrink-0">
                    @if($berita->gambar)
                        <img src="{{ asset('storage/' . $berita->gambar) }}" alt="{{ $berita->judul }}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>

                {{-- Content --}}
                <div class="p-6 flex flex-col grow">
                    <div class="text-[10px] font-bold text-blue-400 uppercase tracking-widest mb-3">
                        {{ $berita->created_at->format('d M Y') }}
                    </div>
                    <a href="{{ route('public.berita.show', $berita->slug) }}" class="block group/title">
                        <h3 class="text-xl font-black text-white leading-tight mb-3 line-clamp-2 group-hover/title:text-blue-400 transition-colors" title="{{ $berita->judul }}">
                            {{ $berita->judul }}
                        </h3>
                    </a>
                    <p class="text-sm font-medium text-slate-400 mb-6 line-clamp-3 grow">
                        {{ $berita->deskripsi }}
                    </p>

                    {{-- Button --}}
                    <div class="mt-auto pt-4 border-t border-white/5">
                        <a href="{{ route('public.berita.show', $berita->slug) }}" class="inline-flex items-center gap-2 text-xs font-bold text-white hover:text-blue-400 transition-colors uppercase tracking-widest group/btn">
                            BACA SELENGKAPNYA
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transform group-hover/btn:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-1 md:col-span-3 text-center py-12 glass-panel rounded-3xl border border-white/5">
                <p class="text-slate-400 font-medium">Belum ada berita terbaru.</p>
            </div>
        @endforelse
    </div>

    <!-- Skeleton Loading UI (Muncul saat loadMore) -->
    <div wire:loading wire:target="loadMore" class="w-full mt-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="glass-panel rounded-4xl overflow-hidden relative flex flex-col h-full border border-white/5 animate-pulse">
                    <div class="w-full h-48 bg-slate-800/50 shrink-0"></div>
                    <div class="p-6 flex flex-col grow">
                        <div class="h-3 w-24 bg-blue-500/20 rounded mb-4"></div>
                        <div class="h-6 w-full bg-slate-700/50 rounded mb-2"></div>
                        <div class="h-6 w-2/3 bg-slate-700/50 rounded mb-4"></div>
                        <div class="h-4 w-full bg-slate-800/50 rounded mb-2"></div>
                        <div class="h-4 w-5/6 bg-slate-800/50 rounded mb-6 grow"></div>
                        <div class="mt-auto pt-4 border-t border-white/5">
                            <div class="h-4 w-32 bg-slate-700/50 rounded"></div>
                        </div>
                    </div>
                </div>
            @endfor
        </div>
    </div>

    <!-- Tombol Muat Lainnya -->
    @if($total > $amount)
        <div class="flex justify-center mt-8">
            <button wire:click="loadMore" wire:loading.attr="disabled" class="px-8 py-3 glass-panel hover:bg-white/5 border border-white/10 rounded-xl text-white font-bold tracking-widest text-sm transition-all uppercase flex items-center gap-2 group relative overflow-hidden">
                <span class="relative z-10 flex items-center gap-2" wire:loading.remove wire:target="loadMore">
                    MUAT LAINNYA
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 group-hover:translate-y-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </span>
                <span class="relative z-10 flex items-center gap-2" wire:loading wire:target="loadMore">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    MEMUAT...
                </span>
            </button>
        </div>
    @endif
</div>
