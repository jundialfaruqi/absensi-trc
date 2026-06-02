<?php

use App\Models\Absensi;
use App\Models\ApkRelease;
use App\Models\LeaveRequest;
use App\Models\Personnel;
use App\Models\Shift;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Renderless;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] #[Layout('layouts::admin.app')] class extends Component
{
    public bool $readyToLoad = false;

    public string $filterShift = 'siang';

    public string $filterTanggal = '';

    public function load()
    {
        $this->readyToLoad = true;
        $this->filterTanggal = Carbon::today()->format('Y-m-d');
    }

    #[Renderless]
    public function openEditAbsensiFromDashboard(int $personnelId, string $tanggal): void
    {
        $this->dispatch('openEditAbsensi', personnelId: $personnelId, tanggal: $tanggal);
    }

    #[On('refreshAbsensi')]
    public function refreshDashboard(): void
    {
        // Re-renders dashboard after edit saved
    }

    public function with()
    {
        $user = Auth::user();
        $isSuperAdmin = $user->can('lihat-dashboard');
        $opdId = $user->opd()?->id;
        $shifts = Shift::where('type', 'shift')->orderBy('name')->get();

        if (! $this->readyToLoad) {
            return [
                'stats' => [
                    'total_personnel' => 0,
                    'total_masuk' => 0,
                    'total_pulang' => 0,
                    'total_terlambat' => 0,
                    'total_alpa' => 0,
                    'total_hadir' => 0,
                    'total_izin' => 0,
                    'total_telat' => 0,
                    'pending_leaves_count' => 0,
                    'total_required' => 0,
                    'hadir_percentage' => 0,
                ],
                'activities' => collect(),
                'pendingLeaves' => collect(),
                'latePersonnel' => collect(),
                'absentPersonnel' => collect(),
                'isSuperAdmin' => $isSuperAdmin,
                'opdName' => ! $isSuperAdmin ? $user->opd()?->name : 'Semua OPD',
                'shifts' => $shifts,
                'apkInfo' => [
                    'version' => '',
                    'description' => '',
                    'whats_new' => '',
                    'optional_message' => '',
                ],
            ];
        }

        $today = $this->filterTanggal ?: Carbon::today()->format('Y-m-d');

        // Base query for attendance today
        $absensiBase = Absensi::whereDate('tanggal', $today)
            ->where(function ($q) {
                // Count records that are NOT 'LIBUR'
                $q->whereHas('jadwal.shift', fn ($sq) => $sq->where('type', 'shift'))
                    ->orWhereHas('personnel', fn ($pq) => $pq->where('attendance_type', 'FLEXIBLE'));
            })
            ->when(! $isSuperAdmin, function ($q) use ($opdId) {
                $q->whereHas('personnel', fn ($pq) => $pq->where('opd_id', $opdId));
            })
            ->when($this->filterShift, function ($q) {
                if (is_numeric($this->filterShift)) {
                    $q->whereHas('jadwal.shift', fn ($sq) => $sq->where('id', $this->filterShift));
                } elseif ($this->filterShift === 'flexible') {
                    $q->whereHas('personnel', fn ($pq) => $pq->where('attendance_type', 'FLEXIBLE'));
                } else {
                    $q->whereHas('jadwal.shift.konsumsis', fn ($sq) => $sq->where('nama', $this->filterShift));
                }
            });

        // Detailed Stats
        $totalRequired = (clone $absensiBase)->count();
        $totalHadir = (clone $absensiBase)->where('status', 'HADIR')->count();
        $totalAlpa = (clone $absensiBase)->where('status', 'ALPA')->count();
        $totalIzin = (clone $absensiBase)->whereIn('status', ['CUTI', 'IZIN', 'SAKIT', 'DINAS'])->count();

        $totalMasuk = (clone $absensiBase)->whereNotNull('jam_masuk')->count();
        $totalPulang = (clone $absensiBase)->whereNotNull('jam_pulang')->count();
        $totalTelat = (clone $absensiBase)->where('status_masuk', 'TELAT')->count();

        $hadirPercentage = $totalRequired > 0
            ? round((($totalHadir + $totalIzin) / $totalRequired) * 100)
            : 0;

        // Activities
        $activities = (clone $absensiBase)
            ->join('personnels', 'absensis.personnel_id', '=', 'personnels.id')
            ->join('opds', 'personnels.opd_id', '=', 'opds.id')
            ->select('absensis.*')
            ->with(['personnel.opd', 'jadwal.shift'])
            ->orderBy('opds.singkatan')
            ->latest('absensis.updated_at')
            ->get();

        // Pending Leave Requests
        $pendingLeaves = LeaveRequest::where('status', 'PENDING')
            ->when(! $isSuperAdmin, function ($q) use ($opdId) {
                $q->whereHas('personnel', fn ($pq) => $pq->where('opd_id', $opdId));
            })
            ->with(['personnel.opd', 'cuti'])
            ->latest()
            ->take(5)
            ->get();

        // --- Monitoring lists ---
        $latePersonnel = (clone $absensiBase)->where('status_masuk', 'TELAT')->with(['personnel.opd'])->latest('jam_masuk')->get();
        $absentPersonnel = (clone $absensiBase)->where('status', 'ALPA')->with(['personnel.opd', 'jadwal.shift'])->get();

        // Total Registered Personnel (All in DB, filtered by OPD)
        $totalRegistered = Personnel::when(! $isSuperAdmin, fn ($q) => $q->where('opd_id', $opdId))->count();

        // APK Info from latest release
        $latestApk = ApkRelease::latestRelease();

        return [
            'stats' => [
                'total_personnel' => $totalRegistered,
                'total_masuk' => $totalMasuk,
                'total_pulang' => $totalPulang,
                'total_terlambat' => $totalTelat,
                'total_alpa' => $totalAlpa,
                'total_hadir' => $totalHadir,
                'total_izin' => $totalIzin,
                'total_telat' => $totalTelat,
                'pending_leaves_count' => LeaveRequest::where('status', 'PENDING')->when(! $isSuperAdmin, fn ($q) => $q->whereHas('personnel', fn ($pq) => $pq->where('opd_id', $opdId)))->count(),
                'total_required' => $totalRequired,
                'hadir_percentage' => $hadirPercentage,
            ],
            'activities' => $activities,
            'pendingLeaves' => $pendingLeaves,
            'latePersonnel' => $latePersonnel,
            'absentPersonnel' => $absentPersonnel,
            'isSuperAdmin' => $isSuperAdmin,
            'opdName' => ! $isSuperAdmin ? $user->opd()?->name : 'Semua OPD',
            'shifts' => $shifts,
            'apkInfo' => [
                'version' => $latestApk?->version ?? 'v1.2.0',
                'description' => $latestApk?->description ?? 'Rilis terbaru dengan penguatan sistem keamanan perangkat.',
                'whats_new' => $latestApk?->whats_new ?? [],
                'optional_message' => $latestApk?->optional_message ?? '',
                'release_date' => $latestApk?->release_date?->format('d/m/Y'),
            ],
        ];
    }

    public function approveLeave($id)
    {
        $request = LeaveRequest::findOrFail($id);
        $user = Auth::user();

        // Security check
        if (! $user->can('lihat-dashboard') && $request->personnel->opd_id !== $user->opd()?->id) {
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

            $originalMasuk = $existing ? ($existing->original_status_masuk ?? $existing->status_masuk) : 'ALPA';
            $originalPulang = $existing ? ($existing->original_status_pulang ?? $existing->status_pulang) : 'ALPA';

            Absensi::updateOrCreate(
                ['personnel_id' => $request->personnel_id, 'tanggal' => $dateStr],
                [
                    'status' => 'CUTI',
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

        if (! $user->can('lihat-dashboard') && $request->personnel->opd_id !== $user->opd()?->id) {
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
