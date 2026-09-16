<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelFaceEmbedding extends Model
{
    protected $fillable = [
        'personnel_id',
        'pose_type',
        'face_descriptor',
        'face_descriptor_mobile',
        'adaptive_descriptor_mobile',
        'adaptation_count',
        'last_adapted_at',
        'foto',
    ];

    protected $casts = [
        'adaptation_count' => 'integer',
        'last_adapted_at' => 'datetime',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}

