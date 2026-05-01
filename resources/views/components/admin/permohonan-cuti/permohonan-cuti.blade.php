<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Permohonan Cuti</h1>
            <p class="text-sm text-base-content/60 mt-1">Manajemen pengajuan cuti dan izin personil.</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Data</li>
                <li>
                    <a href="{{ route('permohonan-cuti') }}">
                        <span class="text-base-content font-bold">Permohonan Cuti</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    {{-- ─── Toolbar: Search + Filters ──────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <div class="form-control">
            <div class="flex flex-col sm:flex-row items-center gap-3">
                <div class="join">
                    <span
                        class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Show</span>
                    <select wire:model.live="perPage" class="select join-item w-20 rounded-end-md">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                </div>
                <div class="join">
                    <span
                        class="btn btn-disabled join-item text-base-content pointer-events-none rounded-left-md">Status</span>
                    <select wire:model.live="statusFilter" class="select join-item rounded-end-md">
                        <option value="PENDING">Pending</option>
                        <option value="APPROVED">Disetujui</option>
                        <option value="REJECTED">Ditolak</option>
                        <option value="">Semua</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-auto">
                    <input type="text" placeholder="Search personil..." wire:model.live.debounce.400ms="search"
                        class="input input-bordered w-full sm:max-w-xs pl-10 pr-10 bg-base-100" />
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                        <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </span>
                    @if ($search)
                        <button type="button" wire:click="$set('search', '')"
                            class="absolute inset-y-0 right-0 pr-3 text-base-content/50">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ─── Table ─────────────────────────────────────────────────────── --}}
    <div class="card bg-transparent md:bg-base-100 shadow-none md:shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto md:overflow-visible">
                <table class="table table-zebra w-full border-separate border-spacing-y-2 md:border-spacing-y-0">
                    <thead class="hidden md:table-header-group">
                        <tr class="bg-base-200/50">
                            <th class="text-center w-16">#</th>
                            <th>Personil</th>
                            <th>Jenis Cuti</th>
                            <th>Periode</th>
                            <th>Alasan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody class="block md:table-row-group">
                        @forelse($this->requests as $req)
                            <tr
                                class="flex flex-col md:table-row bg-base-100 md:bg-transparent border border-base-200 md:border-0 rounded-xl md:rounded-none p-4 md:p-0 mb-4 md:mb-0 shadow-sm md:shadow-none hover:bg-base-200/50 transition-colors">
                                {{-- Nomor - Hidden on mobile, or styled as badge --}}
                                <td class="hidden md:table-cell text-center font-bold">
                                    {{ $this->requests->firstItem() + $loop->index }}</td>

                                {{-- Personil --}}
                                <td class="block md:table-cell p-0 md:p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-12 h-12 md:w-10 md:h-10">
                                                <img
                                                    src="{{ $req->personnel->foto ? asset('storage/' . $req->personnel->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($req->personnel->name) . '&background=random&color=fff' }}" />
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-bold text-sm md:font-semibold">{{ $req->personnel->name }}
                                            </div>
                                            <div class="text-[10px] md:text-xs opacity-60">
                                                {{ $req->personnel->opd->name }}</div>
                                        </div>
                                        <div class="md:hidden">
                                            @php
                                                $statusClass = match ($req->status) {
                                                    'PENDING' => 'badge-warning',
                                                    'APPROVED' => 'badge-success',
                                                    'REJECTED' => 'badge-error',
                                                    default => 'badge-neutral',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $statusClass }} badge-xs font-black">{{ $req->status }}</span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Jenis Cuti --}}
                                <td
                                    class="flex justify-between items-center md:table-cell py-3 border-b border-base-200 border-dashed md:border-0">
                                    <span
                                        class="text-[10px] font-black uppercase opacity-40 tracking-widest md:hidden">Jenis
                                        Cuti</span>
                                    <span
                                        class="text-sm font-medium bg-base-200 md:bg-transparent px-2 py-1 rounded-lg md:p-0">{{ $req->cuti->name }}</span>
                                </td>

                                {{-- Periode --}}
                                <td
                                    class="flex justify-between items-center md:table-cell py-3 border-b border-base-200 border-dashed md:border-0">
                                    <span
                                        class="text-[10px] font-black uppercase opacity-40 tracking-widest md:hidden">Periode</span>
                                    <div class="text-right md:text-left">
                                        <div class="font-bold md:font-medium text-xs md:text-sm">
                                            {{ $req->tanggal_mulai->format('d/m/Y') }}</div>
                                        <div class="text-[9px] md:text-[10px] opacity-60 uppercase font-bold">s/d
                                            {{ $req->tanggal_selesai->format('d/m/Y') }}</div>
                                    </div>
                                </td>

                                {{-- Alasan --}}
                                <td
                                    class="flex flex-col md:table-cell py-3 border-b border-base-200 border-dashed md:border-0">
                                    <span
                                        class="text-[10px] font-black uppercase opacity-40 tracking-widest md:hidden mb-1 text-left">Alasan</span>
                                    <div class="max-w-xs text-xs opacity-70 line-clamp-2 italic text-left">
                                        "{{ $req->alasan }}"</div>
                                </td>

                                {{-- Status (Desktop Only in original position) --}}
                                <td class="hidden md:table-cell text-center">
                                    <span
                                        class="badge {{ $statusClass }} badge-sm font-bold">{{ $req->status }}</span>
                                </td>

                                {{-- Action --}}
                                <td class="block md:table-cell pt-4 md:pt-0">
                                    <div class="flex items-center justify-between md:justify-center">
                                        <span
                                            class="text-[10px] font-black uppercase opacity-40 tracking-widest md:hidden"></span>
                                        @if ($req->status === 'PENDING')
                                            <div class="flex gap-2 md:hidden w-full max-w-[200px]">
                                                <button type="button"
                                                    class="btn btn-success btn-sm flex-1 text-white text-[10px] font-black"
                                                    wire:click="openProcessModal({{ $req->id }}, 'APPROVE')">SETUJUI</button>
                                                <button type="button"
                                                    class="btn btn-error btn-sm flex-1 text-white text-[10px] font-black"
                                                    wire:click="openProcessModal({{ $req->id }}, 'REJECT')">TOLAK</button>
                                            </div>

                                            <div class="hidden md:inline-block dropdown dropdown-left dropdown-end">
                                                <button tabindex="0"
                                                    class="btn btn-ghost btn-xs btn-square rounded-full">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-5 h-5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                                                    </svg>
                                                </button>
                                                <ul tabindex="0"
                                                    class="dropdown-content menu p-2 shadow-md bg-base-100 rounded-box w-36 z-50">
                                                    <li>
                                                        <button type="button" class="text-success font-bold"
                                                            wire:click="openProcessModal({{ $req->id }}, 'APPROVE')">Setujui</button>
                                                    </li>
                                                    <li>
                                                        <button type="button" class="text-error font-bold"
                                                            wire:click="openProcessModal({{ $req->id }}, 'REJECT')">Tolak</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        @else
                                            <div class="text-[10px] opacity-40 italic">
                                                {{ $req->processed_at->diffForHumans() }}</div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-sm text-base-content/60 py-12">
                                    Belum ada data permohonan cuti baru
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-actions justify-between items-center p-4 border-t border-base-200">
                <div class="w-full">{{ $this->requests->links() }}</div>
            </div>

        </div>
    </div>

    {{-- Process Modal --}}
    <dialog id="process-cuti-modal" class="modal backdrop-blur-xs" wire:ignore.self
        x-on:open-modal.window="$event.detail.id === 'process-cuti-modal' && $el.showModal()"
        x-on:close-modal.window="$event.detail.id === 'process-cuti-modal' && $el.close()">
        <div class="modal-box shadow w-11/12 max-w-xl p-0 overflow-hidden bg-base-100 rounded-2xl">
            <div class="p-6 border-b border-base-200 bg-base-200/30 flex justify-between items-center">
                <h3 class="font-black text-lg uppercase italic tracking-tight">
                    {{ $processingAction === 'APPROVE' ? 'Setujui Permohonan' : 'Tolak Permohonan' }}
                </h3>
                <button type="button" class="btn btn-ghost btn-sm btn-circle"
                    onclick="document.getElementById('process-cuti-modal').close()">✕</button>
            </div>

            <div class="p-6 space-y-4">
                <div class="p-4 bg-base-200 rounded-2xl border border-base-300">
                    <p class="text-[10px] font-black text-base-content/50 uppercase tracking-widest mb-2 italic">
                        Konfirmasi Tindakan:</p>
                    <p class="text-sm font-medium">Apakah Anda yakin ingin <span
                            class="font-black {{ $processingAction === 'APPROVE' ? 'text-success' : 'text-error' }}">{{ $processingAction === 'APPROVE' ? 'MENYETUJUI' : 'MENOLAK' }}</span>
                        permohonan ini?</p>
                    @if ($processingAction === 'APPROVE')
                        <div class="mt-4 p-3 bg-success/10 rounded-xl border border-success/20">
                            <p class="text-[10px] text-success font-bold italic leading-tight">Sistem akan secara
                                otomatis membuat/memperbarui record absensi menjadi status 'CUTI' untuk rentang tanggal
                                yang diajukan.</p>
                        </div>
                    @endif
                </div>

                <div class="form-control w-full">
                    <label class="label mb-1 px-1">
                        <span
                            class="label-text font-black text-[10px] uppercase tracking-widest italic opacity-50">Catatan
                            Admin (Opsional)</span>
                    </label>
                    <textarea wire:model="adminNote" class="textarea textarea-bordered w-full h-24 font-medium text-sm"
                        placeholder="{{ $processingAction === 'APPROVE' ? 'Tambahkan catatan persetujuan...' : 'Jelaskan alasan penolakan permohonan...' }}"></textarea>
                </div>
            </div>

            <div class="modal-action bg-base-200/40 p-5 border-t border-base-200 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" class="btn btn-ghost font-bold uppercase tracking-widest text-xs"
                    onclick="document.getElementById('process-cuti-modal').close()">Batal</button>
                <button wire:click="process"
                    class="btn {{ $processingAction === 'APPROVE' ? 'btn-success' : 'btn-error' }} text-white font-black uppercase tracking-widest text-xs px-8">
                    <span wire:loading.remove
                        wire:target="process">{{ $processingAction === 'APPROVE' ? 'Ya, Setujui' : 'Ya, Tolak' }}</span>
                    <span wire:loading wire:target="process" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </dialog>
</div>
