<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuti extends Model
{
    protected $fillable = ['name', 'keterangan'];

    public function absensis()
    {
        return $this->hasMany(Absensi::class);
    }
}
