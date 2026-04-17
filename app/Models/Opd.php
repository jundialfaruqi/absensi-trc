<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opd extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'singkatan',
        'logo',
        'alamat',
    ];

    /**
     * Satu OPD bisa memiliki banyak user.
     * Namun 1 user hanya boleh terdaftar di 1 OPD (unique user_id di pivot).
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'opd_user')
            ->withTimestamps();
    }

    /**
     * Accessor URL logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/' . $this->logo) : null;
    }

    public function personnels(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }
}
