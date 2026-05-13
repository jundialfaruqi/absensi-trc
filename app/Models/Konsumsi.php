<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Konsumsi extends Model
{
    protected $fillable = ['nama'];

    public function shifts(): BelongsToMany
    {
        return $this->belongsToMany(Shift::class, 'shift_konsumsi');
    }
}
