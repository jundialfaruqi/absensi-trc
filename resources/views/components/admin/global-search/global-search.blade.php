<div x-data="{
    isOpen: false,
    open() {
        this.isOpen = true;
        $nextTick(() => $refs.searchInput.focus());
    },
    close() { this.isOpen = false; }
}" x-on:open-global-search.window="open()" x-on:keydown.window.cmd.k.prevent="open()"
    x-on:keydown.window.ctrl.k.prevent="open()" x-on:keydown.window.slash.prevent="open()" x-on:keydown.escape="close()"
    class="relative z-[9999]">

    {{-- Search Trigger in Navbar --}}
    <div @click="open()"
        class="hidden sm:flex items-center gap-3 px-4 py-2 bg-base-200/50 rounded-full border border-base-300 group hover:border-primary/50 transition-all cursor-pointer">
        <svg class="w-4 h-4 text-base-content/40 group-hover:text-primary transition-colors" fill="none"
            stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
        <span
            class="text-xs font-medium text-base-content/40 group-hover:text-base-content/60 transition-colors pr-6">Cari
            data atau menu...</span>
        <div class="flex items-center gap-1 opacity-40 group-hover:opacity-100 transition-opacity">
            <kbd class="kbd kbd-xs bg-base-100">⌘</kbd>
            <kbd class="kbd kbd-xs bg-base-100">K</kbd>
        </div>
    </div>

    {{-- Modal Overlay --}}
    <div x-show="isOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="fixed inset-0 flex items-start justify-center pt-[15vh] px-4 sm:px-0" x-cloak>

        <div class="fixed inset-0 bg-base-300/60 backdrop-blur-md" @click="close()"></div>

        <div
            class="bg-base-100 w-full max-w-2xl rounded-xl shadow-2xl border border-base-content/10 relative overflow-hidden flex flex-col max-h-[70vh]">
            {{-- Header --}}
            <div class="p-4 border-b border-base-content/5 flex items-center gap-4 bg-base-200/30">
                <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input x-ref="searchInput" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Ketik untuk mencari (Personel, Menu, dll...)"
                    class="p-1 bg-transparent rounded-lg border-none focus:ring-0 w-full placeholder:text-base-content/30 font-medium"
                    @keydown.escape="close()" />
                <button @click="close()" class="btn btn-ghost btn-xs btn-circle opacity-50">✕</button>
            </div>

            {{-- Content --}}
            <div class="flex-1 overflow-y-auto p-2 custom-scrollbar">
                @if (strlen($search) < 2)
                    @if (!empty($recentSearches))
                        <div class="px-2 py-3">
                            <h3
                                class="text-[10px] font-black uppercase tracking-[0.2em] text-base-content/30 mb-3 px-2">
                                Pencarian Terakhir</h3>
                            <div class="space-y-1">
                                @foreach ($recentSearches as $recent)
                                    <button wire:click.prevent="selectResult({{ json_encode($recent) }})"
                                        class="w-full flex items-center gap-4 p-3 rounded-2xl hover:bg-base-200 group transition-all border border-transparent">
                                        <div
                                            class="size-8 rounded-lg bg-base-300 flex items-center justify-center text-base-content/30 group-hover:bg-primary group-hover:text-primary-content transition-all">
                                            @if ($recent['icon'] == 'home')
                                                <svg class="size-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                                    </path>
                                                </svg>
                                            @elseif($recent['icon'] == 'users')
                                                <svg class="size-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path
                                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                                    </path>
                                                </svg>
                                            @else
                                                <svg class="size-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="flex-1 text-left min-w-0">
                                            <p
                                                class="text-xs font-bold text-base-content/60 group-hover:text-primary transition-colors">
                                                {{ $recent['title'] }}</p>
                                        </div>
                                        <div
                                            class="text-[9px] font-black uppercase tracking-widest text-base-content/20">
                                            {{ $recent['type'] }}
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center space-y-4">
                            <div class="size-16 bg-primary/5 rounded-full flex items-center justify-center mx-auto">
                                <svg class="w-8 h-8 text-primary/40" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-base-content/60">Cari Apapun...</p>
                                <p class="text-xs text-base-content/40 mt-1">Gunakan kata kunci untuk mencari menu atau
                                    data
                                    personel.</p>
                            </div>
                        </div>
                    @endif
                @elseif(empty($this->results))
                    <div class="p-12 text-center">
                        <p class="text-sm font-bold text-base-content/60">Tidak ada hasil untuk "{{ $search }}"
                        </p>
                        <p class="text-xs text-base-content/40 mt-1">Coba gunakan kata kunci lain.</p>
                    </div>
                @else
                    <div class="space-y-1">
                        @foreach ($this->results as $result)
                            <button wire:click.prevent="selectResult({{ json_encode($result) }})"
                                class="w-full flex items-center gap-4 p-3 rounded-2xl hover:bg-primary/5 group transition-all border border-transparent hover:border-primary/20">
                                <div
                                    class="size-10 rounded-xl bg-base-200 flex items-center justify-center text-base-content/40 group-hover:bg-primary group-hover:text-primary-content transition-all">
                                    @if ($result['icon'] == 'home')
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
                                            </path>
                                        </svg>
                                    @elseif($result['icon'] == 'users')
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                            </path>
                                        </svg>
                                    @else
                                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 text-left min-w-0">
                                    <p
                                        class="text-sm font-bold text-base-content group-hover:text-primary transition-colors">
                                        {{ $result['title'] }}</p>
                                    @if (isset($result['description']))
                                        <p class="text-[10px] text-base-content/50 truncate">
                                            {{ $result['description'] }}</p>
                                    @endif
                                </div>
                                <div
                                    class="px-3 py-1 rounded-lg bg-base-200 text-[9px] font-black uppercase tracking-widest text-base-content/40 group-hover:bg-primary/20 group-hover:text-primary transition-colors">
                                    {{ $result['type'] }}
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="p-3 bg-base-200/50 border-t border-base-content/5 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1">
                        <kbd class="kbd kbd-xs bg-base-100 font-sans">ESC</kbd>
                        <span class="text-[9px] font-bold text-base-content/40 uppercase">Tutup</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd class="kbd kbd-xs bg-base-100 font-sans">↵</kbd>
                        <span class="text-[9px] font-bold text-base-content/40 uppercase">Pilih</span>
                    </div>
                </div>
                <div class="text-[9px] font-black text-primary/40 uppercase tracking-widest">
                    {{ count($this->results) }} Hasil Ditemukan
                </div>
            </div>
        </div>
    </div>
</div>
