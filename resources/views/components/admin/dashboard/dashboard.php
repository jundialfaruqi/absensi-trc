<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Absensi;
use App\Models\Personnel;
use App\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;

new #[Title('Dashboard')] #[Layout('layouts::admin.app')] class extends Component
{
    public function with()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->hasRole('super-admin');
        $opdId = $user->opd()?->id;

        $today = Carbon::today();

        // Base queries
        $absensiQuery = Absensi::whereDate('tanggal', $today);
        $personnelQuery = Personnel::query();
        $leaveRequestQuery = LeaveRequest::where('status', 'PENDING');

        // Apply OPD filtering
        if (!$isSuperAdmin) {
            $absensiQuery->whereHas('personnel', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
            $personnelQuery->where('opd_id', $opdId);
            $leaveRequestQuery->whereHas('personnel', function ($q) use ($opdId) {
                $q->where('opd_id', $opdId);
            });
        }

        // Stats
        $totalPersonnel = $personnelQuery->count();
        $totalMasuk = (clone $absensiQuery)->whereNotNull('jam_masuk')->count();
        $totalPulang = (clone $absensiQuery)->whereNotNull('jam_pulang')->count();
        $totalTerlambat = (clone $absensiQuery)->where('status_masuk', 'TELAT')->count();

        // Activities (latest records)
        $activities = $absensiQuery->with(['personnel.opd'])
            ->latest('updated_at')
            ->take(15)
            ->get();

        // Pending Leave Requests for Quick Action
        $pendingLeaves = $leaveRequestQuery->with(['personnel.opd', 'cuti'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'stats' => [
                'total_personnel' => $totalPersonnel,
                'total_masuk' => $totalMasuk,
                'total_pulang' => $totalPulang,
                'total_terlambat' => $totalTerlambat,
                'pending_leaves_count' => $leaveRequestQuery->count(),
                'hadi_percentage' => $totalPersonnel > 0 ? round(($totalMasuk / $totalPersonnel) * 100) : 0,
            ],
            'activities' => $activities,
            'pendingLeaves' => $pendingLeaves,
            'isSuperAdmin' => $isSuperAdmin,
            'opdName' => !$isSuperAdmin ? $user->opd()?->name : 'Semua OPD',
        ];
    }

    public function approveLeave($id)
    {
        $request = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        // Security check
        if (!$user->hasRole('super-admin') && $request->personnel->opd_id !== $user->opd()?->id) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak memiliki akses.');
            return;
        }

        $request->update([
            'status' => 'APPROVED',
            'processed_by_user_id' => $user->id,
            'processed_at' => now(),
            'admin_note' => 'Disetujui',
        ]);

        // Sync to Absensi table
        $period = CarbonPeriod::create($request->tanggal_mulai, $request->tanggal_selesai);
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');

            $existing = Absensi::where('personnel_id', $request->personnel_id)
                ->whereDate('tanggal', $dateStr)
                ->first();

            $originalMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALFA';
            $originalPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALFA';

            Absensi::updateOrCreate(
                ['personnel_id' => $request->personnel_id, 'tanggal' => $dateStr],
                [
                    'status_masuk' => 'CUTI',
                    'status_pulang' => 'CUTI',
                    'cuti_id' => $request->cuti_id,
                    'keterangan' => $request->alasan,
                    'alasan_edit' => 'Cuti Disetujui (Dashboard)',
                    'edited_by_user_id' => $user->id,
                    'edited_at' => now(),
                    'original_status_masuk' => $originalMasuk,
                    'original_status_pulang' => $originalPulang,
                ]
            );
        }

        $this->dispatch('toast', type: 'success', message: 'Permohonan cuti disetujui.');
    }

    public function rejectLeave($id)
    {
        $request = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        if (!$user->hasRole('super-admin') && $request->personnel->opd_id !== $user->opd()?->id) {
            $this->dispatch('toast', type: 'error', message: 'Anda tidak memiliki akses.');
            return;
        }

        $request->update([
            'status' => 'REJECTED',
            'processed_by_user_id' => $user->id,
            'processed_at' => now(),
            'admin_note' => 'Ditolak',
        ]);

        $this->dispatch('toast', type: 'success', message: 'Permohonan cuti ditolak.');
    }
};
