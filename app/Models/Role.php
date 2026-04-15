<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    /**
     * Kolom tambahan yang dapat diisi secara massal.
     * Kolom bawaan Spatie: name, guard_name
     * Kolom custom: color
     */
    protected $fillable = [
        'name',
        'guard_name',
        'color',
    ];
}
