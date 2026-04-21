<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-bold">Permohonan Cuti</h1>
            <p class="text-sm text-base-content/60 mt-1">Manajemen pengajuan cuti dan izin personil.</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60">
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
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-zebra w-full">
                    <thead>
                        <tr>
                            <th class="text-center w-16">#</th>
                            <th>Personil</th>
                            <th>Jenis Cuti</th>
                            <th>Periode</th>
                            <th>Alasan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center w-24">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->requests as $req)
                            <tr class="hover:bg-base-200/50">
                                <td class="text-center font-bold">{{ $this->requests->firstItem() + $loop->index }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="avatar">
                                            <div class="mask mask-squircle w-10 h-10">
                                                <img
                                                    src="{{ $req->personnel->foto ? asset('storage/' . $req->personnel->foto) : 'https://ui-avatars.com/api/?name=' . urlencode($req->personnel->name) . '&background=random&color=fff' }}" />
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-semibold">{{ $req->personnel->name }}</div>
                                            <div class="text-xs opacity-60">{{ $req->personnel->opd->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-neutral badge-sm">{{ $req->cuti->name }}</span>
                                </td>
                                <td>
                                    <div class="font-medium text-sm">{{ $req->tanggal_mulai->format('d/m/Y') }}</div>
                                    <div class="text-[10px] opacity-60 uppercase font-bold">s/d
                                        {{ $req->tanggal_selesai->format('d/m/Y') }}</div>
                                </td>
                                <td>
                                    <div class="max-w-xs text-xs opacity-70 line-clamp-2 italic">"{{ $req->alasan }}"
                                    </div>
                                </td>
                                <td class="text-center">
                                    @php
                                        $statusClass = match ($req->status) {
                                            'PENDING' => 'badge-warning',
                                            'APPROVED' => 'badge-success',
                                            'REJECTED' => 'badge-error',
                                            default => 'badge-neutral',
                                        };
                                    @endphp
                                    <span
                                        class="badge {{ $statusClass }} badge-sm font-bold">{{ $req->status }}</span>
                                </td>
                                <td class="text-center">
                                    @if ($req->status === 'PENDING')
                                        <div class="dropdown dropdown-left dropdown-end">
                                            <button tabindex="0" class="btn btn-ghost btn-xs btn-square rounded-full">
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
                                                    <button type="button" class="text-success"
                                                        wire:click="openProcessModal({{ $req->id }}, 'APPROVE')">Setujui</button>
                                                </li>
                                                <li>
                                                    <button type="button" class="text-error"
                                                        wire:click="openProcessModal({{ $req->id }}, 'REJECT')">Tolak</button>
                                                </li>
                                            </ul>
                                        </div>
                                    @else
                                        <div class="text-[10px] opacity-40 italic">
                                            {{ $req->processed_at->diffForHumans() }}</div>
                                    @endif
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
