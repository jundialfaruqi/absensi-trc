<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-base-content uppercase italic tracking-tight">Permohonan Cuti</h1>
            <p class="text-sm text-base-content/50">Manajemen pengajuan cuti dan izin personil.</p>
        </div>

        <div class="flex items-center gap-3">
            <div class="join shadow-sm border border-base-300">
                <select wire:model.live="statusFilter" class="select select-sm join-item bg-base-100 border-none focus:ring-0 font-bold text-xs uppercase tracking-widest">
                    <option value="PENDING">Pending</option>
                    <option value="APPROVED">Disetujui</option>
                    <option value="REJECTED">Ditolak</option>
                    <option value="">Semua</option>
                </select>
                <div class="join-item flex items-center px-4 bg-base-100 border-l border-base-300">
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama personil..." class="bg-transparent border-none focus:ring-0 text-xs font-medium w-40 md:w-64">
                </div>
            </div>
        </div>
    </div>

    {{-- Content --}}
    <div class="bg-base-100 rounded-3xl border border-base-300 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="table table-md">
                <thead>
                    <tr class="bg-base-200/50">
                        <th class="text-[10px] font-black uppercase tracking-widest opacity-50">Personil</th>
                        <th class="text-[10px] font-black uppercase tracking-widest opacity-50">Jenis Cuti</th>
                        <th class="text-[10px] font-black uppercase tracking-widest opacity-50">Periode</th>
                        <th class="text-[10px] font-black uppercase tracking-widest opacity-50">Alasan</th>
                        <th class="text-[10px] font-black uppercase tracking-widest opacity-50">Status</th>
                        <th class="text-right text-[10px] font-black uppercase tracking-widest opacity-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-base-300">
                    @forelse($this->requests as $req)
                        <tr class="hover:bg-base-200/30 transition-colors group">
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="mask mask-squircle w-10 h-10">
                                            <img src="{{ $req->personnel->foto ? asset('storage/'.$req->personnel->foto) : 'https://ui-avatars.com/api/?name='.urlencode($req->personnel->name).'&background=random&color=fff' }}" />
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-black text-sm uppercase italic tracking-tight">{{ $req->personnel->name }}</div>
                                        <div class="text-[10px] font-bold opacity-50 uppercase tracking-widest">{{ $req->personnel->opd->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-sm font-black uppercase italic tracking-widest">{{ $req->cuti->name }}</span>
                            </td>
                            <td>
                                <div class="text-xs font-bold">{{ $req->tanggal_mulai->format('d/m/Y') }}</div>
                                <div class="text-[10px] opacity-50 uppercase font-black">s/d {{ $req->tanggal_selesai->format('d/m/Y') }}</div>
                            </td>
                            <td>
                                <div class="max-w-xs text-xs font-medium italic opacity-70 line-clamp-2">"{{ $req->alasan }}"</div>
                            </td>
                            <td>
                                @php
                                    $statusClass = match($req->status) {
                                        'PENDING' => 'badge-warning',
                                        'APPROVED' => 'badge-success',
                                        'REJECTED' => 'badge-error',
                                        default => 'badge-neutral'
                                    };
                                @endphp
                                <span class="badge badge-sm font-black uppercase tracking-widest {{ $statusClass }} py-3">{{ $req->status }}</span>
                            </td>
                            <td class="text-right">
                                @if($req->status === 'PENDING')
                                    <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openProcessModal({{ $req->id }}, 'APPROVE')" class="btn btn-square btn-sm btn-success text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </button>
                                        <button wire:click="openProcessModal({{ $req->id }}, 'REJECT')" class="btn btn-square btn-sm btn-error text-white">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                @else
                                    <div class="text-[10px] font-bold opacity-30 italic">Diproses {{ $req->processed_at->diffForHumans() }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-20 opacity-30">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="text-sm font-black uppercase tracking-widest">Tidak ada record permohonan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($this->requests->hasPages())
            <div class="p-4 bg-base-200/50 border-t border-base-300">
                {{ $this->requests->links() }}
            </div>
        @endif
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
                        <span class="label-text font-black text-[10px] uppercase tracking-widest italic opacity-50">Catatan
                            Admin (Opsional)</span>
                    </label>
                    <textarea wire:model="adminNote" class="textarea textarea-bordered h-24 font-medium text-sm"
                        placeholder="{{ $processingAction === 'APPROVE' ? 'Tambahkan catatan persetujuan...' : 'Jelaskan alasan penolakan permohonan...' }}"></textarea>
                </div>
            </div>

            <div
                class="modal-action bg-base-200/40 p-5 border-t border-base-200 flex justify-end gap-3 rounded-b-2xl">
                <button type="button" class="btn btn-ghost font-bold uppercase tracking-widest text-xs"
                    onclick="document.getElementById('process-cuti-modal').close()">Batal</button>
                <button wire:click="process"
                    class="btn {{ $processingAction === 'APPROVE' ? 'btn-success' : 'btn-error' }} text-white font-black uppercase tracking-widest text-xs px-8">
                    <span wire:loading.remove wire:target="process">{{ $processingAction === 'APPROVE' ? 'Ya, Setujui' : 'Ya, Tolak' }}</span>
                    <span wire:loading wire:target="process" class="loading loading-spinner loading-xs"></span>
                </button>
            </div>
        </div>
    </dialog>
</div>
