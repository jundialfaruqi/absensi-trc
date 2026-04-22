<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use App\Models\LeaveRequest;
use App\Models\Absensi;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

new #[Layout('layouts::admin.app')] #[Title('Permohonan Cuti')] class extends Component {
    use WithPagination;

    public $search = '';
    public $statusFilter = 'PENDING';
    public $perPage = 10;

    // Process Modal
    public $processingId;
    public $processingAction; // 'APPROVE' or 'REJECT'
    public $adminNote = '';

    public function updatedPerPage()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    #[Computed]
    public function requests()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $opdId = $user->opd()?->id;

        return LeaveRequest::query()
            ->with(['personnel.opd', 'cuti'])
            ->when(!$isSuperAdmin, function ($q) use ($opdId) {
                $q->whereHas('personnel', function ($pq) use ($opdId) {
                    $pq->where('opd_id', $opdId);
                });
            })
            ->when($this->search, function ($q) {
                $q->whereHas('personnel', function ($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($q) {
                $q->where('status', $this->statusFilter);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    public function openProcessModal($id, $action)
    {
        $this->processingId = $id;
        $this->processingAction = $action;
        $this->adminNote = '';
        $this->dispatch('open-modal', id: 'process-cuti-modal');
    }

    public function process()
    {
        $request = LeaveRequest::findOrFail($this->processingId);
        
        // Authorization check
        $user = Auth::user();
        if (!$user->hasRole('super-admin') && $request->personnel->opd_id !== $user->opd()?->id) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak memiliki akses ke permohonan ini.');
            return;
        }

        if ($this->processingAction === 'APPROVE') {
            $request->update([
                'status' => 'APPROVED',
                'admin_note' => $this->adminNote,
                'processed_by_user_id' => $user->id,
                'processed_at' => now(),
            ]);

            // SYNC TO ABSENSI
            $period = CarbonPeriod::create($request->tanggal_mulai, $request->tanggal_selesai);
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                // Get existing original status if editing
                $existing = Absensi::where('personnel_id', $request->personnel_id)
                    ->whereDate('tanggal', $dateStr)
                    ->first();
                
                $originalMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALFA';
                $originalPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALFA';

                Absensi::updateOrCreate(
                    [
                        'personnel_id' => $request->personnel_id,
                        'tanggal' => $dateStr,
                    ],
                    [
                        'status' => 'CUTI',
                        'status_masuk' => 'CUTI',
                        'status_pulang' => 'CUTI',
                        'cuti_id' => $request->cuti_id,
                        'keterangan' => $request->alasan,
                        'alasan_edit' => 'Cuti Disetujui (Sistem)',
                        'edited_by_user_id' => $user->id,
                        'edited_at' => now(),
                        'original_status_masuk' => $originalMasuk,
                        'original_status_pulang' => $originalPulang,
                    ]
                );
            }

            $this->dispatch('toast', type: 'success', message: 'Permohonan cuti disetujui dan data absensi telah di sinkronisasi.');
        } else {
            $request->update([
                'status' => 'REJECTED',
                'admin_note' => $this->adminNote,
                'processed_by_user_id' => $user->id,
                'processed_at' => now(),
            ]);
            $this->dispatch('toast', type: 'success', message: 'Permohonan cuti telah ditolak.');
        }

        $this->dispatch('close-modal', id: 'process-cuti-modal');
    }
};
