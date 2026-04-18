<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Personnel;
use App\Models\Absensi;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.absensi.app')] class extends Component
{
    // Stepper state: 1: Selection, 2: PIN, 3: Action (In/Out), 4: Result
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

    // GPS & Image Data (Sent from Client)
    public string $lat = '';
    public string $lng = '';
    public string $imageData = ''; // Base64 selfie

    public function selectPersonnel(int $id)
    {
        $this->selectedPersonnelId = $id;
        $this->selectedPersonnel = Personnel::find($id);
        $this->step = 2;
        $this->pin = '';
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

        if (Hash::check($this->pin, $this->selectedPersonnel->pin)) {
            $this->prepareActionStep();
        } else {
            $this->addError('pin', 'PIN yang Anda masukkan salah.');
            $this->pin = '';
        }
    }

    public function prepareActionStep()
    {
        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $yesterday = $now->copy()->subDay()->format('Y-m-d');
        $nowTime = $now->format('H:i:s');

        // Logic: Prioritize Yesterday's Night Shift if we are currently in the "Morning After"
        // Buffer: 00:00 to 09:00 AM
        if ($nowTime < '09:00:00') {
            $yesterdayJadwal = Jadwal::where('personnel_id', $this->selectedPersonnel->id)
                ->whereDate('tanggal', $yesterday)
                ->with('shift')
                ->first();

            if ($yesterdayJadwal && $yesterdayJadwal->shift && $yesterdayJadwal->shift->start_time > $yesterdayJadwal->shift->end_time) {
                // It was a night shift. Check if we are still within the shift window (End Time + 2 hours buffer)
                $endTimePlusBuffer = Carbon::parse($yesterdayJadwal->shift->end_time)->addHours(2)->format('H:i:s');
                
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

        $this->activeAbsensi = Absensi::where('personnel_id', $this->selectedPersonnel->id)
            ->where('tanggal', $this->activeDate)
            ->first();

        $this->step = 3;
    }

    public function submitAttendance(string $type, $clientLat = null, $clientLng = null, $clientImage = null)
    {
        // Update state with data from client
        if ($clientLat) $this->lat = $clientLat;
        if ($clientLng) $this->lng = $clientLng;
        if ($clientImage) $this->imageData = $clientImage;

        if (!$this->selectedPersonnel || !$this->activeJadwal || !$this->activeJadwal->shift) {
            $this->isSuccess = false;
            $this->message = 'Anda tidak dapat melakukan absensi pada jadwal ini (' . ($this->activeJadwal->status ?? 'ALPHA') . ')';
            $this->step = 4;
            return;
        }

        $now = Carbon::now();
        $nowTime = $now->format('H:i:s');

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
                if ($this->activeAbsensi) {
                    $this->isSuccess = false;
                    $this->message = 'Anda sudah melakukan absen masuk.';
                    $this->step = 4;
                    return;
                }

                $status_masuk = 'HADIR';
                $nowTime = $now->format('H:i:s');
                
                // Night Shift Logic for status
                $isNightShift = $this->activeJadwal->shift->start_time > $this->activeJadwal->shift->end_time;

                if ($isNightShift) {
                    // If arrived between 00:00 and EndTime, definitely late relative to the StartTime (Day 1)
                    if ($nowTime <= $this->activeJadwal->shift->end_time) {
                        $status_masuk = 'TELAT';
                    } else {
                        // Arrived after StartTime but before Midnight
                        if ($nowTime > $this->activeJadwal->shift->start_time) {
                            $status_masuk = 'TELAT';
                        }
                    }
                } else {
                    // Normal shift
                    if ($nowTime > $this->activeJadwal->shift->start_time) {
                        $status_masuk = 'TELAT';
                    }
                }

                $this->lastAbsensi = Absensi::create([
                    'personnel_id' => $this->selectedPersonnel->id,
                    'jadwal_id' => $this->activeJadwal->id,
                    'tanggal' => $this->activeDate,
                    'jam_masuk' => $now->format('H:i:s'),
                    'status_masuk' => $status_masuk,
                    'foto_masuk' => $imagePath,
                    'lat_masuk' => $this->lat ?: 0,
                    'lng_masuk' => $this->lng ?: 0,
                ]);

                $this->isSuccess = true;
                $this->message = "Absen Masuk Berhasil ($status_masuk)";
            } else {
                if (!$this->activeAbsensi) {
                    $this->isSuccess = false;
                    $this->message = 'Anda belum melakukan absen masuk.';
                    $this->step = 4;
                    return;
                }

                if ($this->activeAbsensi->jam_pulang) {
                    $this->isSuccess = false;
                    $this->message = 'Anda sudah melakukan absen pulang.';
                    $this->step = 4;
                    return;
                }

                $status_pulang = 'HADIR';
                
                // Check for early departure
                $isNightShift = $this->activeJadwal->shift->start_time > $this->activeJadwal->shift->end_time;
                $isNextDay = ($this->activeDate !== $now->format('Y-m-d'));

                if ($isNightShift) {
                    if (!$isNextDay) {
                        // Still on day 1 of a night shift, definitely early
                        $status_pulang = 'PC';
                    } else {
                        if ($nowTime < $this->activeJadwal->shift->end_time) {
                            $status_pulang = 'PC';
                        }
                    }
                } else {
                    if ($nowTime < $this->activeJadwal->shift->end_time) {
                        $status_pulang = 'PC';
                    }
                }

                $this->activeAbsensi->update([
                    'jam_pulang' => $now->format('H:i:s'),
                    'status_pulang' => $status_pulang,
                    'foto_pulang' => $imagePath,
                    'lat_pulang' => $this->lat ?: 0,
                    'lng_pulang' => $this->lng ?: 0,
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
        $this->reset(['step', 'selectedPersonnelId', 'selectedPersonnel', 'pin', 'isSuccess', 'message', 'lastAbsensi', 'search', 'activeJadwal', 'activeAbsensi', 'activeDate', 'lat', 'lng', 'imageData']);
    }

    public function personnels()
    {
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