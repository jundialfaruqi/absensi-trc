<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftCycleTemplate extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'opd_id', 'sequence'];

    protected $casts = [
        'sequence' => 'array',
    ];

    public function opd()
    {
        return $this->belongsTo(Opd::class);
    }
}
