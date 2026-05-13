<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'keterangan',
        'start_time',
        'end_time',
        'color',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function konsumsis(): BelongsToMany
    {
        return $this->belongsToMany(Konsumsi::class, 'shift_konsumsi');
    }
}
