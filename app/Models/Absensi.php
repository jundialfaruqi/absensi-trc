<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'jadwal_id',
        'kantor_id',
        'tanggal',
        'status',
        'jam_masuk',
        'jam_pulang',
        'status_masuk',
        'status_pulang',
        'foto_masuk',
        'foto_pulang',
        'lat_masuk',
        'lng_masuk',
        'lat_pulang',
        'lng_pulang',
        'is_within_radius',
        'jarak_meter',
        'kantor_id_pulang',
        'is_within_radius_pulang',
        'jarak_meter_pulang',
        'edited_by_user_id',
        'edited_at',
        'alasan_edit',
        'original_status_masuk',
        'original_status_pulang',
        'nomor_surat',
        'cuti_id',
        'keterangan',
        'platform_masuk',
        'platform_pulang',
        'device_name_masuk',
        'device_name_pulang',
        'unique_device_id_masuk',
        'unique_device_id_pulang',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_masuk' => 'datetime',
        'jam_pulang' => 'datetime',
        'edited_at' => 'datetime',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class);
    }

    public function kantor(): BelongsTo
    {
        return $this->belongsTo(Kantor::class);
    }

    public function kantorPulang(): BelongsTo
    {
        return $this->belongsTo(Kantor::class, 'kantor_id_pulang');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by_user_id');
    }

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }
}
