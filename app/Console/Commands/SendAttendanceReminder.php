<?php

namespace App\Console\Commands;

use App\Jobs\SendFcmNotificationJob;
use App\Models\Absensi;
use App\Models\Jadwal;
use App\Models\Personnel;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('attendance:send-reminder')]
#[Description('Kirim notifikasi peringatan absen masuk jika jadwal sudah dibuka tapi personel belum absen.')]
class SendAttendanceReminder extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $todayDate = $now->format('Y-m-d');
        $yesterdayDate = $now->copy()->subDay()->format('Y-m-d');

        // Ambil konfigurasi window absensi masuk dari database (default: mulai 30 menit sebelum, selesai 120 menit setelah)
        $mulaiIn = (int) Setting::get('absensi_masuk_mulai', 30);
        $selesaiIn = (int) Setting::get('absensi_masuk_selesai', 120);
        $mulaiOut = (int) Setting::get('absensi_pulang_mulai', 30);
        $selesaiOut = (int) Setting::get('absensi_pulang_selesai', 120);

        // Ambil semua personel berlisensi personal yang memiliki FCM Token aktif dan tipenya bukan FLEXIBLE
        $personnels = Personnel::whereNotNull('fcm_token')
            ->where('attendance_type', '!=', 'FLEXIBLE')
            ->get();

        $this->info('Memproses '.$personnels->count().' personel untuk peringatan absen...');

        foreach ($personnels as $personnel) {
            // Check both yesterday and today schedules (essential for overnight shifts)
            foreach ([$yesterdayDate, $todayDate] as $date) {
                // Cari jadwal personel untuk tanggal tersebut
                $jadwal = Jadwal::where('personnel_id', $personnel->id)
                    ->whereDate('tanggal', $date)
                    ->with('shift')
                    ->first();

                // Skip jika tidak ada jadwal, status libur, atau shift off
                if (! $jadwal || $jadwal->status === 'LIBUR') {
                    continue;
                }

                $shift = $jadwal->shift;
                if (! $shift || $shift->type === 'off' || ! $shift->start_time) {
                    continue;
                }

                // ─── ABSEN MASUK REMINDER ───
                $startTime = Carbon::parse($date)->setTimeFrom($shift->start_time);
                $windowInStart = $startTime->copy()->subMinutes($mulaiIn);
                $windowInEnd = $startTime->copy()->addMinutes($selesaiIn);

                if ($now->between($windowInStart, $windowInEnd)) {
                    $absensi = Absensi::where('personnel_id', $personnel->id)
                        ->whereDate('tanggal', $date)
                        ->first();

                    // Jika belum absen masuk
                    if (! $absensi || ! $absensi->jam_masuk) {
                        $cacheKey = "attendance_reminder_in_{$personnel->id}_{$jadwal->id}";

                        // Mencegah pengiriman berulang dalam satu window (anti-spam)
                        if (! Cache::has($cacheKey)) {
                            $title = '🚨 Peringatan Absen Masuk!';
                            $body = 'Anda belum melakukan absen masuk, absen sekarang';
                            $data = [
                                'type' => 'attendance_reminder',
                                'jadwal_id' => (string) $jadwal->id,
                                'action' => 'check_in',
                            ];

                            // Simpan cache terlebih dahulu sebelum dispatch pekerjaan ke antrean
                            $secondsRemaining = $now->diffInSeconds($windowInEnd);
                            $cacheDuration = max($secondsRemaining, 600); // minimal 10 menit
                            Cache::put($cacheKey, true, $cacheDuration);

                            $this->info("Mengantrekan notifikasi absen masuk ke {$personnel->name}...");
                            SendFcmNotificationJob::dispatch($personnel->fcm_token, $title, $body, $data);
                        }
                    }
                }

                // ─── ABSEN PULANG REMINDER ───
                $pulangDate = $date;
                if ($shift->start_time >= $shift->end_time) {
                    $pulangDate = Carbon::parse($date)->addDay()->format('Y-m-d');
                }
                $endTime = Carbon::parse($pulangDate)->setTimeFrom($shift->end_time);
                $windowOutStart = $endTime->copy()->subMinutes($mulaiOut);
                $windowOutEnd = $endTime->copy()->addMinutes($selesaiOut);

                if ($now->between($windowOutStart, $windowOutEnd)) {
                    $absensi = Absensi::where('personnel_id', $personnel->id)
                        ->whereDate('tanggal', $date)
                        ->first();

                    // Jika belum absen pulang
                    if (! $absensi || ! $absensi->jam_pulang) {
                        $cacheKey = "attendance_reminder_out_{$personnel->id}_{$jadwal->id}";

                        // Mencegah pengiriman berulang dalam satu window (anti-spam)
                        if (! Cache::has($cacheKey)) {
                            $title = '🚨 Peringatan Absen Pulang!';
                            $body = 'Anda belum melakukan absen pulang, absen sekarang';
                            $data = [
                                'type' => 'attendance_reminder',
                                'jadwal_id' => (string) $jadwal->id,
                                'action' => 'check_out',
                            ];

                            // Simpan cache terlebih dahulu sebelum dispatch pekerjaan ke antrean
                            $secondsRemaining = $now->diffInSeconds($windowOutEnd);
                            $cacheDuration = max($secondsRemaining, 600); // minimal 10 menit
                            Cache::put($cacheKey, true, $cacheDuration);

                            $this->info("Mengantrekan notifikasi absen pulang ke {$personnel->name}...");
                            SendFcmNotificationJob::dispatch($personnel->fcm_token, $title, $body, $data);
                        }
                    }
                }
            }
        }

        $this->info('Proses pengiriman notifikasi peringatan selesai.');

        return Command::SUCCESS;
    }
}
