<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

new #[Layout('layouts.absensi.app')] class extends Component
{
    // Stepper state: 1: Selection, 2: PIN, 3: Action (In/Out), 4: Result, 5: Portal Closed
    public int $step = 1;
    
    // Step 1: Selection
    public string $search = '';
    public ?int $selectedPersonnelId = null;
    public ?Personnel $selectedPersonnel = null;

    // Step 2: PIN
    public string $pin = '';

    // Step 3: Action state
    public ?Jadwal $activeJadwal = null;
    public ?Absensi $activeAbsensi = null;
    public string $activeDate = '';

    // Step 4: Result
    public bool $isSuccess = false;
    public string $message = '';
    public ?Absensi $lastAbsensi = null;

    // Location info for Step 3
    public array $infoLokasi = [];

    // GPS & Image Data (Sent from Client)
    public string $lat = '';
    public string $lng = '';
    public string $imageData = ''; // Base64 selfie

    // Server Time Sync
    public $serverTime;
    public $serverTimestamp;
    public $apiSource = 'local';
    public $fetchTime;
    public bool $isTimeSynced = false;
    public bool $isTooLateToIn = false;

    public function mount()
    {
        if (!\App\Models\Setting::get('web_absensi_active', true)) {
            $this->step = 5;
            $this->message = 'Maaf, Portal Absensi Web sedang dinonaktifkan oleh Administrator.';
            return;
        }

        $this->resetErrorBag();
        $this->fetchServerTime();
    }

    public function fetchServerTime($force = false)
    {
        // Guard: Skip if already synced recently (within 30 seconds) unless forced
        if (!$force && $this->isTimeSynced && $this->fetchTime) {
            try {
                if (Carbon::parse($this->fetchTime)->diffInSeconds(Carbon::now()) < 30) {
                    return;
                }
            } catch (\Exception $e) {
                // Fallback to sync if date parsing fails
            }
        }

        try {
            // Priority 1: Local Server (Main Priority)
            $this->serverTime = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $this->serverTimestamp = Carbon::now('Asia/Jakarta')->timestamp * 1000; // in milliseconds
            $this->apiSource = 'server';
            $this->isTimeSynced = true;
        } catch (\Exception $e) {
            try {
                // Priority 2: timeapi.io
                $response = Http::timeout(6)->get('https://timeapi.io/api/time/current/zone?timeZone=Asia/Jakarta');
                if ($response->successful()) {
                    $data = $response->json();
                    $serverNow = Carbon::parse($data['dateTime']);
                    $this->serverTime = $serverNow->toDateTimeString();
                    $this->serverTimestamp = $serverNow->timestamp * 1000;
                    $this->apiSource = 'timeapi';
                    $this->isTimeSynced = true;
                } else {
                    throw new \Exception("TimeAPI Failed");
                }
            } catch (\Exception $ex) {
                try {
                    // Priority 3: worldtimeapi (reliable public API)
                    $response = Http::timeout(6)->get('http://worldtimeapi.org/api/timezone/Asia/Jakarta');
                    if ($response->successful()) {
                        $data = $response->json();
                        $serverNow = Carbon::createFromTimestamp($data['unixtime'], 'Asia/Jakarta');
                        $this->serverTime = $serverNow->toDateTimeString();
                        $this->serverTimestamp = $serverNow->timestamp * 1000;
                        $this->apiSource = 'worldtimeapi';
                        $this->isTimeSynced = true;
                    } else {
                        throw new \Exception("WorldTimeAPI Failed");
                    }
                } catch (\Exception $ex2) {
                    $this->isTimeSynced = false;
                    $this->apiSource = 'failed';
                }
            }
        }
        $this->fetchTime = Carbon::now();
    }

    private function getCorrectedNow()
    {
        if (!$this->serverTime || !$this->fetchTime) {
            return Carbon::now('Asia/Jakarta');
        }

        $elapsedSeconds = (int) Carbon::now()->diffInSeconds($this->fetchTime, false);
        return Carbon::parse($this->serverTime)->addSeconds($elapsedSeconds);
    }

    public function selectPersonnel(int $id)
    {
        $this->selectedPersonnelId = $id;
        $this->selectedPersonnel = Personnel::find($id);

        // Pre-check Schedule & Time Window BEFORE PIN Step
        $this->fetchServerTime(true);
        $this->prepareActionStep(true); // pass true to just check window

        if ($this->step === 4) {
            // If prepareActionStep set step to 4, it means it rejected due to window or no schedule
            return;
        }

        $this->step = 2;
        $this->pin = '';
        $this->resetErrorBag();
    }

    public function appendPin($num)
    {
        if (strlen($this->pin) < 4) {
            $this->pin .= $num;
        }
    }

    public function clearPin()
    {
        $this->pin = '';
    }

    public function verifyPin()
    {
        if (!$this->selectedPersonnel) return;

        $ip = request()->ip();
        $userAgent = request()->userAgent();

        // 1. Check Rate Limit / Lockout
        $maxAttempts = (int) \App\Models\Setting::get('pin_max_attempts', 5);
        $lock5 = (int) \App\Models\Setting::get('pin_lock_duration_5', 5);
        $lock15 = (int) \App\Models\Setting::get('pin_lock_duration_15', 15);

        // Calculate failures since last success
        $lastSuccess = \App\Models\PinAttemptLog::where('personnel_id', $this->selectedPersonnel->id)
            ->where('status', 'success')
            ->latest()
            ->first();

        $failureQuery = \App\Models\PinAttemptLog::where('personnel_id', $this->selectedPersonnel->id)
            ->where('status', 'fail');
        
        if ($lastSuccess) {
            $failureQuery->where('created_at', '>', $lastSuccess->created_at);
        } else {
            $failureQuery->where('created_at', '>', now()->subMinutes(60));
        }

        $recentFailures = $failureQuery->count();

        // Find time of last failure to check if still in lockout
        $lastFailure = \App\Models\PinAttemptLog::where('personnel_id', $this->selectedPersonnel->id)
            ->where('status', 'fail')
            ->latest()
            ->first();

        if ($recentFailures >= $maxAttempts && $lastFailure) {
            $lockDuration = $recentFailures >= ($maxAttempts * 2) ? $lock15 : $lock5;
            $unlockAt = $lastFailure->created_at->addMinutes($lockDuration);

            if (now()->lessThan($unlockAt)) {
                $remaining = $unlockAt->diffInMinutes(now());
                $this->addError('pin', "Terlalu banyak percobaan salah. Akun terkunci sementara. Silakan coba lagi dalam $remaining menit.");
                $this->pin = '';
                return;
            }
        }

        // 2. Verify PIN
        if (Hash::check($this->pin, $this->selectedPersonnel->pin)) {
            // Log Success
            \App\Models\PinAttemptLog::create([
                'personnel_id' => $this->selectedPersonnel->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status' => 'success'
            ]);

            $this->fetchServerTime(true);
            $this->prepareActionStep();
        } else {
            // Log Failure
            \App\Models\PinAttemptLog::create([
                'personnel_id' => $this->selectedPersonnel->id,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status' => 'fail'
            ]);

            $this->addError('pin', 'PIN yang Anda masukkan salah.');
            $this->pin = '';
        }
    }

    public function prepareActionStep($checkWindowOnly = false)
    {
        $now = $this->getCorrectedNow();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // Reset state for calculation
        $this->activeJadwal = null;
        $this->activeDate = '';

        // Logic: Prioritize Yesterday's Night Shift if we are currently in the "Morning After"
        // Buffer: 00:00 to 09:00 AM
        if ($nowTime < '09:00:00') {
            $yesterdayJadwal = Jadwal::where('personnel_id', $this->selectedPersonnel->id)
                ->whereDate('tanggal', $yesterday)
                ->with('shift')
                ->first();

            if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->start_time->format('H:i:s') > $yesterdayJadwal->shift->end_time->format('H:i:s')) {
                // It was a night shift. Check if we are still within the shift window (End Time + 4 hours buffer for safety)
                $endTimePlusBuffer = $yesterdayJadwal->shift->end_time->copy()->addHours(4)->format('H:i:s');
                
                // If now is before the cutoff, use yesterday's context
                if ($nowTime < $endTimePlusBuffer) {
                    $this->activeJadwal = $yesterdayJadwal;
                    $this->activeDate = $yesterday;
                }
            }
        }

        // If no night shift from yesterday was found/selected, use Today's schedule
        if (!$this->activeJadwal) {
            $this->activeJadwal = Jadwal::where('personnel_id', $this->selectedPersonnel->id)
                ->whereDate('tanggal', $today)
                ->with('shift')
                ->first();
            $this->activeDate = $today;
        }

        if (!$this->activeJadwal) {
            $this->isSuccess = false;
            $this->message = 'Anda tidak memiliki jadwal shift hari ini.';
            $this->step = 4;
            return;
        }

        if ($this->activeJadwal->status === 'LIBUR') {
            $this->isSuccess = false;
            $this->message = 'Anda sedang LIBUR hari ini.';
            $this->step = 4;
            return;
        }

        // ─── TIME WINDOW VALIDATION (GLOBAL) ───
        $this->activeAbsensi = Absensi::where('personnel_id', $this->selectedPersonnel->id)
            ->where('tanggal', $this->activeDate)
            ->first();

        $shift = $this->activeJadwal->shift;

        // Configuration
        $mulaiIn = (int) \App\Models\Setting::get('absensi_masuk_mulai', 30);
        $selesaiIn = (int) \App\Models\Setting::get('absensi_masuk_selesai', 120);
        $mulaiOut = (int) \App\Models\Setting::get('absensi_pulang_mulai', 30);
        $selesaiOut = (int) \App\Models\Setting::get('absensi_pulang_selesai', 120);

        // Windows Calculation
        $startTime = Carbon::parse($this->activeDate)->setTimeFrom($shift->start_time);
        $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
        $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

        $pulangDate = $this->activeDate;
        if ($shift->start_time->format('H:i:s') > $shift->end_time->format('H:i:s')) {
            $pulangDate = Carbon::parse($this->activeDate)->addDay()->format('Y-m-d');
        }
        $endTime = Carbon::parse($pulangDate)->setTimeFrom($shift->end_time);
        $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
        $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

        // Check already completed both
        if ($this->activeAbsensi && $this->activeAbsensi->jam_masuk && $this->activeAbsensi->jam_pulang) {
            $this->isSuccess = false;
            $this->message = "Anda sudah melakukan absen masuk dan pulang hari ini.";
            $this->step = 4;
            return;
        }

        // Determine Mode & Validate
        if ($this->activeAbsensi && $this->activeAbsensi->jam_masuk) {
            // MODE: CLOCK OUT (Normal)
            if ($now->lessThan($windowOutStart)) {
                $this->isSuccess = false;
                $diff = $windowOutStart->diffForHumans($now, true);
                $this->message = "Belum waktunya Absen Pulang. Silakan kembali $diff lagi.";
                $this->step = 4;
                return;
            }
            if ($now->greaterThan($windowOutEnd)) {
                $this->isSuccess = false;
                $this->message = "Batas waktu Absen Pulang sudah berakhir (Maksimal $selesaiOut menit setelah jadwal).";
                $this->step = 4;
                return;
            }
            $this->isTooLateToIn = true; // In case they want to check out, this flag ensures blade shows OUT button
        } else {
            // MODE: NO CLOCK IN RECORD
            if ($now->lessThan($windowInStart)) {
                // Too early for anything
                $this->isSuccess = false;
                $diff = $windowInStart->diffForHumans($now, true);
                $this->message = "Belum waktunya Absen Masuk. Silakan kembali $diff lagi.";
                $this->step = 4;
                return;
            } elseif ($now->between($windowInStart, $windowInEnd)) {
                // WITHIN IN WINDOW
                $this->isTooLateToIn = false;
            } elseif ($now->between($windowInEnd, $windowOutStart)) {
                // GAP BETWEEN IN AND OUT
                $this->isSuccess = false;
                $diff = $windowOutStart->diffForHumans($now, true);
                $this->message = "Batas waktu Absen Masuk sudah berakhir. Silakan kembali $diff lagi untuk Absen Pulang.";
                $this->step = 4;
                return;
            } elseif ($now->between($windowOutStart, $windowOutEnd)) {
                // WITHIN OUT WINDOW (MISSING IN)
                $this->isTooLateToIn = true;
            } else {
                // PAST OUT WINDOW
                $this->isSuccess = false;
                $this->message = "Batas waktu Absen hari ini sudah berakhir.";
                $this->step = 4;
                return;
            }
        }

        if (!$checkWindowOnly) {
            $this->step = 3;
        }
    }

    public function terimaCoordsLokasi(float $lat, float $lng): void
    {
        $this->lat = (string) $lat;
        $this->lng = (string) $lng;

        if ($this->selectedPersonnel && $this->step === 3) {
            $service = app(\App\Services\AbsensiLokasiService::class);
            $this->infoLokasi = $service->validasiLokasi(
                $this->selectedPersonnel,
                $lat,
                $lng
            );
        }
    }

    public function submitAttendance(string $type, $clientLat = null, $clientLng = null, $clientImage = null)
    {
        // Force refresh network time right before saving to prevent stale data
        $this->fetchServerTime(true);

        // Update state with data from client
        if ($clientLat) $this->lat = $clientLat;
        if ($clientLng) $this->lng = $clientLng;
        if ($clientImage) $this->imageData = $clientImage;

        // Validasi lokasi kantor
        $lokasiService = app(\App\Services\AbsensiLokasiService::class);
        $lokasiResult = $lokasiService->validasiLokasi(
            $this->selectedPersonnel,
            (float) $this->lat,
            (float) $this->lng
        );

        if (!$lokasiResult['boleh']) {
            $this->isSuccess = false;
            $this->message = $lokasiResult['pesan'];
            $this->step = 4;
            return;
        }

        if (!$this->selectedPersonnel || !$this->activeJadwal || $this->activeJadwal->status === 'LIBUR' || !$this->activeJadwal->shift) {
            $this->isSuccess = false;
            $this->message = $this->activeJadwal->status === 'LIBUR' 
                ? 'Anda sedang LIBUR hari ini.' 
                : 'Anda tidak dapat melakukan absensi pada jadwal ini (' . ($this->activeJadwal->status ?? 'ALPHA') . ')';
            $this->step = 4;
            return;
        }

        $now = $this->getCorrectedNow();
        $nowTime = $now->format('H:i:s');
        $startTime = $this->activeJadwal->shift->start_time->format('H:i:s');
        $endTime = $this->activeJadwal->shift->end_time->format('H:i:s');
        $isNightShift = $startTime > $endTime;

        try {
            // Process Image Storage
            $imagePath = null;
            if ($this->imageData) {
                $imageData = str_replace('data:image/jpeg;base64,', '', $this->imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'absensi/' . $type . '_' . $this->selectedPersonnel->id . '_' . time() . '.jpg';
                Storage::disk('public')->put($imageName, base64_decode($imageData));
                $imagePath = $imageName;
            }

            if ($type === 'in') {
                if ($this->activeAbsensi && $this->activeAbsensi->jam_masuk) {
                    $this->isSuccess = false;
                    $this->message = 'Anda sudah melakukan absen masuk.';
                    $this->step = 4;
                    return;
                }

                $status_masuk = 'HADIR';

                // Tolerance: Allow 1 minute buffer for being considered "on time"
                $startTimeWithBuffer = $this->activeJadwal->shift->start_time->copy()->addMinute()->format('H:i:s');

                if ($isNightShift) {
                    // If arrived between 00:00 and EndTime, definitely late relative to the StartTime (Day 1)
                    if ($nowTime <= $endTime) {
                        $status_masuk = 'TELAT';
                    } else {
                        // Arrived after StartTime but before Midnight
                        if ($nowTime > $startTimeWithBuffer) {
                            $status_masuk = 'TELAT';
                        }
                    }
                } else {
                    // Normal shift
                    if ($nowTime > $startTimeWithBuffer) {
                        $status_masuk = 'TELAT';
                    }
                }

                // Update existing placeholder or create if missing
                $this->activeAbsensi = Absensi::updateOrCreate(
                    [
                        'personnel_id' => $this->selectedPersonnel->id,
                        'tanggal' => $this->activeDate,
                    ],
                    [
                        'jadwal_id' => $this->activeJadwal->id,
                        'status' => $status_masuk,
                        'jam_masuk' => $now->format('H:i:s'),
                        'status_masuk' => $status_masuk,
                        'foto_masuk' => $imagePath,
                        'lat_masuk' => $this->lat ?: 0,
                        'lng_masuk' => $this->lng ?: 0,
                        'kantor_id'        => $lokasiResult['kantor_id'],
                        'is_within_radius' => $lokasiResult['is_within_radius'],
                        'jarak_meter'      => $lokasiResult['jarak_meter'],
                    ]
                );
                
                $this->lastAbsensi = $this->activeAbsensi;

                $this->isSuccess = true;
                $this->message = "Absen Masuk Berhasil ($status_masuk)";
            } else {
                if (!$this->activeAbsensi) {
                    // This case shouldn't really happen with pre-created records, but for safety:
                    $this->activeAbsensi = Absensi::updateOrCreate([
                        'personnel_id' => $this->selectedPersonnel->id,
                        'tanggal' => $this->activeDate,
                    ], [
                        'jadwal_id' => $this->activeJadwal->id,
                        'status' => 'ALFA',
                        'status_masuk' => 'ALFA',
                        'kantor_id' => $lokasiResult['kantor_id'],
                        'is_within_radius' => false,
                        'jarak_meter' => 0,
                        'lat_masuk' => 0,
                        'lng_masuk' => 0,
                    ]);
                }

                if ($this->activeAbsensi->jam_pulang) {
                    $this->isSuccess = false;
                    $this->message = 'Anda sudah melakukan absen pulang.';
                    $this->step = 4;
                    return;
                }

                $status_pulang = 'HADIR';
                
                // Check for early departure
                $isNextDay = ($this->activeDate !== $now->format('Y-m-d'));

                if ($isNightShift) {
                    if (!$isNextDay) {
                        // Still on day 1 of a night shift, definitely early
                        $status_pulang = 'PC';
                    } else {
                        if ($nowTime < $endTime) {
                            $status_pulang = 'PC';
                        }
                    }
                } else {
                    if ($nowTime < $endTime) {
                        $status_pulang = 'PC';
                    }
                }

                $this->activeAbsensi->update([
                    'jam_pulang' => $now->format('H:i:s'),
                    'status_pulang' => $status_pulang,
                    'foto_pulang' => $imagePath,
                    'lat_pulang' => $this->lat ?: 0,
                    'lng_pulang' => $this->lng ?: 0,
                    'kantor_id_pulang'        => $lokasiResult['kantor_id'],
                    'is_within_radius_pulang' => $lokasiResult['is_within_radius'],
                    'jarak_meter_pulang'      => $lokasiResult['jarak_meter'],
                ]);

                $this->lastAbsensi = $this->activeAbsensi;
                $this->isSuccess = true;
                $this->message = "Absen Pulang Berhasil ($status_pulang)";
            }
        } catch (\Exception $e) {
            $this->isSuccess = false;
            $this->message = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        $this->step = 4;
    }

    public function resetForm()
    {
        $this->reset(['step', 'selectedPersonnelId', 'selectedPersonnel', 'pin', 'isSuccess', 'message', 'lastAbsensi', 'search', 'activeJadwal', 'activeAbsensi', 'activeDate', 'lat', 'lng', 'imageData', 'infoLokasi']);
        $this->resetErrorBag();
    }

    public function personnels()
    {
        if (strlen($this->search) < 3) {
            return collect();
        }

        return Personnel::query()
            ->when($this->search, function ($q) {
                // Escape special characters to prevent "Wildcard Injection"
                $term = str_replace(['%', '_'], ['\%', '\_'], $this->search);
                $q->where('name', 'like', '%' . $term . '%');
            })
            ->orderBy('name')
            ->take(5)
            ->get();
    }
};