<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class Personnel extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name', 'nik', 'opd_id', 'penugasan_id', 'regu', 'kantor_id', 'nomor_hp', 'foto', 'face_descriptor', 'face_descriptor_mobile', 'email', 'password', 'pin', 'wajib_absen_di_lokasi', 'face_recognition', 'attendance_type', 'fcm_token',
    ];

    protected $hidden = [
        'password', 'pin',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Personnel $personnel) {
            // 1. Hapus semua foto pose 3D dari storage disk public
            foreach ($personnel->faceEmbeddings as $embedding) {
                if ($embedding->foto) {
                    Storage::disk('public')->delete($embedding->foto);
                }
            }

            // 2. Hapus foto utama profil jika ada
            if ($personnel->foto) {
                Storage::disk('public')->delete($personnel->foto);
            }

            // 3. Hapus seluruh data record device yang terhubung ke personel ini
            foreach ($personnel->devices as $device) {
                $device->delete();
            }
        });
    }

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function faceEmbeddings(): HasMany
    {
        return $this->hasMany(PersonnelFaceEmbedding::class);
    }

    public function faceLearningLogs(): HasMany
    {
        return $this->hasMany(PersonnelFaceLearningLog::class);
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(PersonnelRefreshToken::class);
    }
}

