<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Sanctum\HasApiTokens;

class Personnel extends Model
{
    use HasApiTokens;

    protected $fillable = [
        'name', 'opd_id', 'penugasan_id', 'nomor_hp', 'foto', 'email', 'password', 'pin'
    ];

    protected $hidden = [
        'password', 'pin'
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class);
    }

    public function jadwals()
    {
        return $this->hasMany(Jadwal::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}
