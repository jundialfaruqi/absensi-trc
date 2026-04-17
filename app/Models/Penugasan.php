<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Penugasan extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function personnels(): HasMany
    {
        return $this->hasMany(Personnel::class);
    }
}
