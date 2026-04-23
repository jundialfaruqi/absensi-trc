<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PinAttemptLog extends Model
{
    protected $fillable = ['personnel_id', 'ip_address', 'user_agent', 'status', 'attempted_at'];

    protected $casts = [
        'attempted_at' => 'datetime',
    ];

    public function personnel()
    {
        return $this->belongsTo(Personnel::class);
    }
}
