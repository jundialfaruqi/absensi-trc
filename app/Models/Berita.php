<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Berita extends Model
{
    use HasUuids;

    protected $fillable = [
        'judul',
        'slug',
        'isi',
        'gambar',
        'kategori',
        'deskripsi',
        'created_by',
        'is_banner_active',
    ];

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Generate slug dari tanggal + judul.
     * Format: 2026-05-14-judul-berita
     * Jika sudah ada: 2026-05-14-judul-berita-1
     */
    public static function generateSlug(string $judul, ?string $date = null, ?string $excludeId = null): string
    {
        $datePrefix = $date ? Carbon::parse($date)->format('Y-m-d') : now()->format('Y-m-d');
        $baseSlug = $datePrefix . '-' . Str::slug($judul);

        $query = static::where('slug', $baseSlug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if (!$query->exists()) {
            return $baseSlug;
        }

        // Tambahkan nomor di akhir
        $counter = 1;
        do {
            $slug = $baseSlug . '-' . $counter;
            $existsQuery = static::where('slug', $slug);
            if ($excludeId) {
                $existsQuery->where('id', '!=', $excludeId);
            }
            $counter++;
        } while ($existsQuery->exists());

        return $slug;
    }
}
