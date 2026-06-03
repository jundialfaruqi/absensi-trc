<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'shift_id',
        'tanggal',
        'status',
        'keterangan',
        'is_manual',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_manual' => 'boolean',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }

    public static function invalidateCache()
    {
        Cache::put('jadwal_cache_version', time(), 86400 * 30);
    }

    protected static function booted()
    {
        static::saved(fn () => static::invalidateCache());
        static::deleted(fn () => static::invalidateCache());
    }
}
