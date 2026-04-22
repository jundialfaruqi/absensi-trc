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
use Illuminate\Support\Facades\DB;

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
        $personnelQuery = Personnel::whereHas('jadwals', function ($q) use ($today) {
            $q->whereDate('tanggal', $today);
        });
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
        $totalScheduled = $personnelQuery->count(); // Total (incl. LIBUR)
        $totalRequired = (clone $personnelQuery)->whereHas('jadwals', function($q) use ($today) {
            $q->whereDate('tanggal', $today)->where('status', '!=', 'LIBUR');
        })->count(); // Only active shifts

        $totalMasuk = (clone $absensiQuery)->whereNotNull('jam_masuk')->count();
        $totalPulang = (clone $absensiQuery)->whereNotNull('jam_pulang')->count();
        $totalTerlambat = (clone $absensiQuery)->where('status_masuk', 'TELAT')->count();

        // Activities (latest records) - STICK TO TODAY'S DATE
        $activities = Absensi::query()
            ->whereDate('tanggal', $today)
            ->whereNotNull('jadwal_id')
            ->when(!$isSuperAdmin, function ($q) use ($opdId) {
                $q->whereHas('personnel', fn($pq) => $pq->where('opd_id', $opdId));
            })
            ->with(['personnel.opd', 'jadwal.shift'])
            ->orderByRaw("jam_masuk IS NULL DESC")
            ->latest('updated_at')
            ->get();
        
        // Pending Leave Requests
        $pendingLeaves = $leaveRequestQuery->with(['personnel.opd', 'cuti'])
            ->latest()
            ->take(5)
            ->get();

        // --- Monitoring: Pegawai Terlambat (Today) ---
        $latePersonnel = Absensi::whereDate('tanggal', $today)
            ->where('status_masuk', 'TELAT')
            ->when(!$isSuperAdmin, function($q) use ($opdId) {
                $q->whereHas('personnel', fn($pq) => $pq->where('opd_id', $opdId));
            })
            ->with(['personnel.opd'])
            ->latest('jam_masuk')
            ->get();

        // --- Monitoring: Belum Absen (Today) ---
        $absentPersonnel = Absensi::whereDate('tanggal', $today)
            ->where('status', 'ALFA')
            ->whereNotNull('jadwal_id')
            ->when(!$isSuperAdmin, function($q) use ($opdId) {
                $q->whereHas('personnel', fn($pq) => $pq->where('opd_id', $opdId));
            })
            ->with(['personnel.opd', 'jadwal.shift'])
            ->get();

        // --- Statistics: Weekly Chart ---
        $startDay = Carbon::now()->subDays(6)->startOfDay();
        $endDay = Carbon::now()->endOfDay();

        $rawWeeklyStats = Absensi::whereBetween('tanggal', [$startDay, $endDay])
            ->when(!$isSuperAdmin, function($q) use ($opdId) {
                $q->whereHas('personnel', fn($pq) => $pq->where('opd_id', $opdId));
            })
            ->select(
                DB::raw('DATE(tanggal) as date'),
                DB::raw("SUM(CASE WHEN status_masuk = 'HADIR' THEN 1 ELSE 0 END) as ontime_count"),
                DB::raw("SUM(CASE WHEN status_masuk = 'TELAT' THEN 1 ELSE 0 END) as late_count"),
                DB::raw("SUM(CASE WHEN status = 'ALFA' THEN 1 ELSE 0 END) as alfa_count"),
                DB::raw('count(*) as total_count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date')
            ->toArray();

        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->isoFormat('ddd');
            $data = $rawWeeklyStats[$date] ?? null;
            
            $weeklyStats[] = [
                'date' => $date,
                'label' => $label,
                'total' => $data['total_count'] ?? 0,
                'ontime' => $data['ontime_count'] ?? 0,
                'late' => $data['late_count'] ?? 0,
                'alfa' => $data['alfa_count'] ?? 0,
            ];
        }

        return [
            'stats' => [
                'total_personnel' => $totalScheduled,
                'total_masuk' => $totalMasuk,
                'total_pulang' => $totalPulang,
                'total_terlambat' => $totalTerlambat,
                'pending_leaves_count' => $leaveRequestQuery->count(),
                'hadir_percentage' => $totalRequired > 0 ? round(($totalMasuk / $totalRequired) * 100) : 0,
            ],
            'activities' => $activities,
            'pendingLeaves' => $pendingLeaves,
            'latePersonnel' => $latePersonnel,
            'absentPersonnel' => $absentPersonnel,
            'weeklyStats' => $weeklyStats,
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
