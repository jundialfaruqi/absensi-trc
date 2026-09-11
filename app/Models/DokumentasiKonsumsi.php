<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumentasiKonsumsi extends Model
{
    protected $table = 'dokumentasi_konsumsis';

    protected $fillable = [
        'opd_id',
        'tanggal',
        'jumlah_siang',
        'foto_siang',
        'foto_siang_2',
        'jumlah_malam',
        'foto_malam',
        'foto_malam_2',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah_siang' => 'integer',
        'jumlah_malam' => 'integer',
    ];

    public function opd(): BelongsTo
    {
        return $this->belongsTo(Opd::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
