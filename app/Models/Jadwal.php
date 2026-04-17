<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'shift_id',
        'tanggal',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}
