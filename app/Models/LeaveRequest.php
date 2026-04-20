<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    protected $fillable = [
        'personnel_id',
        'cuti_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'alasan',
        'status',
        'admin_note',
        'processed_by_user_id',
        'processed_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'processed_at' => 'datetime',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function cuti(): BelongsTo
    {
        return $this->belongsTo(Cuti::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }
}
