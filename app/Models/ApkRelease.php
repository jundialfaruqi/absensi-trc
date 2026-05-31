<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApkRelease extends Model
{
    use HasFactory;

    protected $fillable = [
        'version',
        'min_version_code',
        'release_date',
        'description',
        'whats_new',
        'optional_message',
    ];

    protected $casts = [
        'release_date' => 'date',
        'whats_new' => 'array',
    ];

    /**
     * Get the latest active release
     */
    public static function latestRelease(): ?self
    {
        return self::orderByDesc('release_date')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Get the minimum APK version code required to use the app.
     * Falls back to 1 if no release exists.
     */
    public static function minimumVersionCode(): int
    {
        return (int) (self::orderByDesc('id')->value('min_version_code') ?? 1);
    }
}
