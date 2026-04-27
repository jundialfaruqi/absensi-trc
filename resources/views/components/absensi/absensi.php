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
    // Stepper state: 1: PIN Identification, 2: Action (In/Out), 3: Result, 4: Portal Closed
    public int $step = 1;
    
    // Step 1: PIN Identification
    public string $pin = '';
    public ?Personnel $selectedPersonnel = null;

    // Step 2: Action state
    public ?Jadwal $activeJadwal = null;
    public ?Absensi $activeAbsensi = null;
    public string $activeDate = '';

    // Step 3: Result
    public bool $isSuccess = false;
    public string $message = '';
    public ?Absensi $lastAbsensi = null;

    // Location info for Step 2
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
            $this->step = 4;
            $this->message = 'Maaf, Portal Absensi Web sedang dinonaktifkan oleh Administrator.';
            return;
        }

        $this->resetErrorBag();
        $this->fetchServerTime();
    }

    public function fetchServerTime($force = false)
    {
        if (!$force && $this->isTimeSynced && $this->fetchTime) {
            try {
                if (Carbon::parse($this->fetchTime)->diffInSeconds(Carbon::now()) < 30) {
                    return;
                }
            } catch (\Exception $e) {}
        }

        try {
            $this->serverTime = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $this->serverTimestamp = Carbon::now('Asia/Jakarta')->timestamp * 1000;
            $this->apiSource = 'server';
            $this->isTimeSynced = true;
        } catch (\Exception $e) {
            try {
                $response = Http::timeout(6)->get('https://timeapi.io/api/time/current/zone?timeZone=Asia/Jakarta');
                if ($response->successful()) {
                    $data = $response->json();
                    $serverNow = Carbon::parse($data['dateTime']);
                    $this->serverTime = $serverNow->toDateTimeString();
                    $this->serverTimestamp = $serverNow->timestamp * 1000;
                    $this->apiSource = 'timeapi';
                    $this->isTimeSynced = true;
                } else { throw new \Exception("TimeAPI Failed"); }
            } catch (\Exception $ex) {
                try {
                    $response = Http::timeout(6)->get('http://worldtimeapi.org/api/timezone/Asia/Jakarta');
                    if ($response->successful()) {
                        $data = $response->json();
                        $serverNow = Carbon::createFromTimestamp($data['unixtime'], 'Asia/Jakarta');
                        $this->serverTime = $serverNow->toDateTimeString();
                        $this->serverTimestamp = $serverNow->timestamp * 1000;
                        $this->apiSource = 'worldtimeapi';
                        $this->isTimeSynced = true;
                    } else { throw new \Exception("WorldTimeAPI Failed"); }
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

    public function appendPin($num)
    {
        if (strlen($this->pin) < 6) {
            $this->pin .= $num;
        }

        if (strlen($this->pin) === 6) {
            $this->verifyPin();
        }
    }

    public function clearPin()
    {
        $this->pin = '';
    }

    public function verifyPin()
    {
        $this->resetErrorBag();
        $ip = request()->ip();
        $userAgent = request()->userAgent();

        // 1. Check Global IP Lockout (Prevent brute-force across different PINs)
        $maxAttempts = (int) \App\Models\Setting::get('pin_max_attempts', 5);
        $lock5 = (int) \App\Models\Setting::get('pin_lock_duration_5', 5);
        $lock15 = (int) \App\Models\Setting::get('pin_lock_duration_15', 15);

        $globalFailureQuery = \App\Models\PinAttemptLog::where('ip_address', $ip)
            ->where('status', 'fail')
            ->where('created_at', '>', now()->subMinutes(60));

        $globalRecentFailures = $globalFailureQuery->count();
        $globalLastFailure = $globalFailureQuery->latest()->first();

        if ($globalRecentFailures >= $maxAttempts && $globalLastFailure) {
            $lockDuration = $globalRecentFailures >= ($maxAttempts * 2) ? $lock15 : $lock5;
            $unlockAt = $globalLastFailure->created_at->addMinutes($lockDuration);

            if (now()->lessThan($unlockAt)) {
                $remaining = ceil(now()->diffInSeconds($unlockAt) / 60);
                $this->addError('pin', "Terlalu banyak percobaan dari perangkat ini. Silakan coba lagi dalam $remaining menit.");
                $this->pin = '';
                return;
            }
        }

        $this->selectedPersonnel = Personnel::where('pin', $this->pin)->first();

        if (!$this->selectedPersonnel) {
            // Log failed attempt for this IP
            \App\Models\PinAttemptLog::create([
                'personnel_id' => null,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
                'status' => 'fail'
            ]);

            $this->addError('pin', 'PIN tidak ditemukan atau salah.');
            $this->pin = '';
            return;
        }

        // 2. Check Specific Personnel Lockout (Prevent brute-force targeting specific personnel)
        $failureQuery = \App\Models\PinAttemptLog::where('personnel_id', $this->selectedPersonnel->id)
            ->where('status', 'fail')
            ->where('created_at', '>', now()->subMinutes(60));

        $recentFailures = $failureQuery->count();
        $lastFailure = $failureQuery->latest()->first();

        if ($recentFailures >= $maxAttempts && $lastFailure) {
            $lockDuration = $recentFailures >= ($maxAttempts * 2) ? $lock15 : $lock5;
            $unlockAt = $lastFailure->created_at->addMinutes($lockDuration);

            if (now()->lessThan($unlockAt)) {
                $remaining = ceil(now()->diffInSeconds($unlockAt) / 60);
                $this->addError('pin', "Terlalu banyak percobaan. Akun terkunci sementara. Silakan coba lagi dalam $remaining menit.");
                $this->pin = '';
                return;
            }
        }

        // Pin is verified (since it matched exactly in our identification-by-pin model)
        \App\Models\PinAttemptLog::create([
            'personnel_id' => $this->selectedPersonnel->id,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'status' => 'success'
        ]);

        $this->fetchServerTime(true);
        $this->prepareActionStep();
    }

    public function prepareActionStep($checkWindowOnly = false)
    {
        $now = $this->getCorrectedNow();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        $this->activeJadwal = null;
        $this->activeDate = '';

        if ($nowTime < '09:00:00') {
            $yesterdayJadwal = Jadwal::where('personnel_id', $this->selectedPersonnel->id)
                ->whereDate('tanggal', $yesterday)
                ->with('shift')
                ->first();

            if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->start_time->format('H:i:s') > $yesterdayJadwal->shift->end_time->format('H:i:s')) {
                $endTimePlusBuffer = $yesterdayJadwal->shift->end_time->copy()->addHours(4)->format('H:i:s');
                if ($nowTime < $endTimePlusBuffer) {
                    $this->activeJadwal = $yesterdayJadwal;
                    $this->activeDate = $yesterday;
                }
            }
        }

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
            $this->step = 3;
            return;
        }

        if ($this->activeJadwal->status === 'LIBUR') {
            $this->isSuccess = false;
            $this->message = 'Anda sedang LIBUR hari ini.';
            $this->step = 3;
            return;
        }

        $this->activeAbsensi = Absensi::where('personnel_id', $this->selectedPersonnel->id)
            ->where('tanggal', $this->activeDate)
            ->first();

        $shift = $this->activeJadwal->shift;
        $mulaiIn = (int) \App\Models\Setting::get('absensi_masuk_mulai', 30);
        $selesaiIn = (int) \App\Models\Setting::get('absensi_masuk_selesai', 120);
        $mulaiOut = (int) \App\Models\Setting::get('absensi_pulang_mulai', 30);
        $selesaiOut = (int) \App\Models\Setting::get('absensi_pulang_selesai', 120);

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

        if ($this->activeAbsensi && $this->activeAbsensi->jam_masuk && $this->activeAbsensi->jam_pulang) {
            $this->isSuccess = false;
            $this->message = "Anda sudah melakukan absen masuk dan pulang hari ini.";
            $this->step = 3;
            return;
        }

        if ($this->activeAbsensi && $this->activeAbsensi->jam_masuk) {
            if ($now->lessThan($windowOutStart)) {
                $this->isSuccess = false;
                $diff = $windowOutStart->diffForHumans($now, true);
                $this->message = "Belum waktunya Absen Pulang. Silakan kembali $diff lagi.";
                $this->step = 3;
                return;
            }
            if ($now->greaterThan($windowOutEnd)) {
                $this->isSuccess = false;
                $this->message = "Batas waktu Absen Pulang sudah berakhir.";
                $this->step = 3;
                return;
            }
            $this->isTooLateToIn = true;
        } else {
            if ($now->lessThan($windowInStart)) {
                $this->isSuccess = false;
                $diff = $windowInStart->diffForHumans($now, true);
                $this->message = "Belum waktunya Absen Masuk. Silakan kembali $diff lagi.";
                $this->step = 3;
                return;
            } elseif ($now->between($windowInStart, $windowInEnd)) {
                $this->isTooLateToIn = false;
            } elseif ($now->between($windowInEnd, $windowOutStart)) {
                $this->isSuccess = false;
                $diff = $windowOutStart->diffForHumans($now, true);
                $this->message = "Batas waktu Absen Masuk sudah berakhir. Silakan kembali $diff lagi untuk Absen Pulang.";
                $this->step = 3;
                return;
            } elseif ($now->between($windowOutStart, $windowOutEnd)) {
                $this->isTooLateToIn = true;
            } else {
                $this->isSuccess = false;
                $this->message = "Batas waktu Absen hari ini sudah berakhir.";
                $this->step = 3;
                return;
            }
        }

        if (!$checkWindowOnly) {
            $this->step = 2;
        }
    }

    public function terimaCoordsLokasi(float $lat, float $lng): void
    {
        $this->lat = (string) $lat;
        $this->lng = (string) $lng;

        if ($this->selectedPersonnel && $this->step === 2) {
            $service = app(\App\Services\AbsensiLokasiService::class);
            $this->infoLokasi = $service->validasiLokasi($this->selectedPersonnel, $lat, $lng);
        }
    }

    public function submitAttendance(string $type, $clientLat = null, $clientLng = null, $clientImage = null)
    {
        $this->fetchServerTime(true);

        if ($clientLat) $this->lat = $clientLat;
        if ($clientLng) $this->lng = $clientLng;
        if ($clientImage) $this->imageData = $clientImage;

        $lokasiService = app(\App\Services\AbsensiLokasiService::class);
        $lokasiResult = $lokasiService->validasiLokasi($this->selectedPersonnel, (float) $this->lat, (float) $this->lng);

        if (!$lokasiResult['boleh']) {
            $this->isSuccess = false;
            $this->message = $lokasiResult['pesan'];
            $this->step = 3;
            return;
        }

        if (!$this->selectedPersonnel || !$this->activeJadwal || $this->activeJadwal->status === 'LIBUR' || !$this->activeJadwal->shift) {
            $this->isSuccess = false;
            $this->message = 'Data jadwal tidak valid.';
            $this->step = 3;
            return;
        }

        $now = $this->getCorrectedNow();
        $nowTime = $now->format('H:i:s');
        $startTime = $this->activeJadwal->shift->start_time->format('H:i:s');
        $endTime = $this->activeJadwal->shift->end_time->format('H:i:s');
        $isNightShift = $startTime > $endTime;

        try {
            $imagePath = null;
            if ($this->imageData) {
                $imageData = str_replace('data:image/jpeg;base64,', '', $this->imageData);
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'absensi/' . $type . '_' . $this->selectedPersonnel->id . '_' . time() . '.jpg';
                Storage::disk('public')->put($imageName, base64_decode($imageData));
                $imagePath = $imageName;
            }

            if ($type === 'in') {
                $status_masuk = 'HADIR';
                $startTimeWithBuffer = $this->activeJadwal->shift->start_time->copy()->addMinute()->format('H:i:s');

                if (($isNightShift && ($nowTime <= $endTime || $nowTime > $startTimeWithBuffer)) || (!$isNightShift && $nowTime > $startTimeWithBuffer)) {
                    $status_masuk = 'TELAT';
                }

                $this->activeAbsensi = Absensi::updateOrCreate(
                    ['personnel_id' => $this->selectedPersonnel->id, 'tanggal' => $this->activeDate],
                    [
                        'jadwal_id' => $this->activeJadwal->id,
                        'status' => 'HADIR',
                        'jam_masuk' => $now->format('H:i:s'),
                        'status_masuk' => $status_masuk,
                        'foto_masuk' => $imagePath,
                        'lat_masuk' => $this->lat ?: 0,
                        'lng_masuk' => $this->lng ?: 0,
                        'kantor_id' => $lokasiResult['kantor_id'],
                        'is_within_radius' => $lokasiResult['is_within_radius'],
                        'jarak_meter' => $lokasiResult['jarak_meter'],
                    ]
                );
                $this->lastAbsensi = $this->activeAbsensi;
                $this->isSuccess = true;
                $this->message = "Absen Masuk Berhasil ($status_masuk)";
            } else {
                $status_pulang = 'HADIR';
                $isNextDay = ($this->activeDate !== $now->format('Y-m-d'));

                if (($isNightShift && (!$isNextDay || $nowTime < $endTime)) || (!$isNightShift && $nowTime < $endTime)) {
                    $status_pulang = 'PC';
                }

                $this->activeAbsensi->update([
                    'status' => 'HADIR',
                    'jam_pulang' => $now->format('H:i:s'),
                    'status_pulang' => $status_pulang,
                    'foto_pulang' => $imagePath,
                    'lat_pulang' => $this->lat ?: 0,
                    'lng_pulang' => $this->lng ?: 0,
                    'kantor_id_pulang' => $lokasiResult['kantor_id'],
                    'is_within_radius_pulang' => $lokasiResult['is_within_radius'],
                    'jarak_meter_pulang' => $lokasiResult['jarak_meter'],
                ]);
                $this->lastAbsensi = $this->activeAbsensi;
                $this->isSuccess = true;
                $this->message = "Absen Pulang Berhasil ($status_pulang)";
            }
        } catch (\Exception $e) {
            $this->isSuccess = false;
            $this->message = 'Terjadi kesalahan: ' . $e->getMessage();
        }
        $this->step = 3;
    }

    public function resetForm()
    {
        $this->reset(['step', 'selectedPersonnel', 'pin', 'isSuccess', 'message', 'lastAbsensi', 'activeJadwal', 'activeAbsensi', 'activeDate', 'lat', 'lng', 'imageData', 'infoLokasi']);
        $this->resetErrorBag();
    }
};