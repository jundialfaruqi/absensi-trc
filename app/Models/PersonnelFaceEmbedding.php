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
        'foto',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}
