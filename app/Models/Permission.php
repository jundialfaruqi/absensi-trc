<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    /**
     * Kolom tambahan yang dapat diisi secara massal.
     * Kolom bawaan Spatie: name, guard_name
     * Kolom custom: group
     */
    protected $fillable = [
        'name',
        'guard_name',
        'group',
    ];
}
