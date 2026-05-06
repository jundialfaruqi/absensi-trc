<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'release_date',
        'description',
        'whats_new',
        'optional_message'
    ];

    protected $casts = [
        'release_date' => 'date',
        'whats_new' => 'array',
    ];

    /**
     * Get the latest active release
     */
    public static function latestRelease()
    {
        return self::orderByDesc('release_date')
            ->orderByDesc('id')
            ->first();
    }
}
