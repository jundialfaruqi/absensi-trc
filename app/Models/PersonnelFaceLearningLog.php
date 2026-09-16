<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelFaceLearningLog extends Model
{
    protected $fillable = [
        'personnel_id',
        'pose_type',
        'absensi_id',
        'confidence_score',
        'drift_to_master',
        'adaptation_index',
        'device_info',
    ];

    protected $casts = [
        'confidence_score' => 'float',
        'drift_to_master' => 'float',
        'adaptation_index' => 'integer',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function absensi(): BelongsTo
    {
        return $this->belongsTo(Absensi::class);
    }
}
