<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'foto', 'nomor_hp'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * 1 User hanya boleh memiliki 1 OPD (enforced via unique constraint di pivot).
     */
    public function opds(): BelongsToMany
    {
        return $this->belongsToMany(Opd::class, 'opd_user')
            ->withTimestamps();
    }

    /**
     * Helper: ambil OPD tunggal milik user ini.
     */
    public function opd(): ?Opd
    {
        return $this->opds->first();
    }
}
