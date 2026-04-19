<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kantor extends Model
{
    protected $fillable = [
        'opd_id',
        'name',
        'alamat',
        'latitude',
        'longitude',
        'radius_meter',
        'is_active',
    ];

    /**
     * Relationship with OPD.
     */
    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    /**
     * Relationship with Personnel.
     */
    public function personnels(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }

    /**
     * Relationship with Absensi.
     */
    public function absensis(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    /**
     * Hitung jarak dari titik lat/lng ke kantor dalam meter menggunakan formula Haversine.
     */
    public function hitungJarak(float $lat, float $lng): float
    {
        $R = 6371000; // radius bumi dalam meter
        $φ1 = deg2rad($this->latitude);
        $φ2 = deg2rad($lat);
        $Δφ = deg2rad($lat - $this->latitude);
        $Δλ = deg2rad($lng - $this->longitude);

        $a = sin($Δφ / 2) * sin($Δφ / 2) +
             cos($φ1) * cos($φ2) *
             sin($Δλ / 2) * sin($Δλ / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $R * $c;
    }

    /**
     * Cek apakah titik lat/lng berada dalam radius kantor.
     */
    public function isInRadius(float $lat, float $lng): bool
    {
        return $this->hitungJarak($lat, $lng) <= $this->radius_meter;
    }
}
