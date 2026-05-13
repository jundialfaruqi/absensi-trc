@if ($paginator->hasPages())
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 w-full">
        {{-- Information --}}
        <div class="text-sm text-base-content/60">
            Menampilkan
            <span class="font-bold text-base-content">{{ $paginator->firstItem() }}</span>
            sampai
            <span class="font-bold text-base-content">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-base-content">{{ $paginator->total() }}</span>
            data
        </div>

        {{-- Pagination Links --}}
        
        {{-- Mobile View: Ringkas (Hanya Prev, Current/Total, Next) --}}
        <div class="flex md:hidden join">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-sm btn-disabled" disabled aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
            @else
                <button class="join-item btn btn-sm" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="prev">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
            @endif

            {{-- Current Page / Total Page --}}
            <button class="join-item btn btn-sm btn-disabled text-base-content font-bold" disabled>
                {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
            </button>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button class="join-item btn btn-sm" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="next">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @else
                <button class="join-item btn btn-sm btn-disabled" disabled aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @endif
        </div>

        {{-- Desktop View: Deretan Angka dengan Auto-Cut dari Laravel --}}
        <div class="hidden md:flex join">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <button class="join-item btn btn-sm btn-disabled" disabled aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
            @else
                <button class="join-item btn btn-sm" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="prev">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <button class="join-item btn btn-sm btn-disabled" disabled>{{ $element }}</button>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <button class="join-item btn btn-sm btn-primary active">{{ $page }}</button>
                        @else
                            <button class="join-item btn btn-sm" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">{{ $page }}</button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button class="join-item btn btn-sm" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled" rel="next">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @else
                <button class="join-item btn btn-sm btn-disabled" disabled aria-disabled="true">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>
            @endif
        </div>
    </div>
@elseif ($paginator->total() > 0)
    <div class="text-sm text-base-content/60 text-center md:text-left">
        Menampilkan
        <span class="font-bold text-base-content">{{ $paginator->firstItem() }}</span>
        sampai
        <span class="font-bold text-base-content">{{ $paginator->lastItem() }}</span>
        dari
        <span class="font-bold text-base-content">{{ $paginator->total() }}</span>
        data
    </div>
@endif
