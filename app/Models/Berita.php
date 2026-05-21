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

    protected static function booted()
    {
        static::saved(function ($berita) {
            $activeBanners = static::where('is_banner_active', true)->get();
            $bannerData = $activeBanners->map(function($b) {
                return [
                    'id' => $b->id,
                    'judul' => $b->judul,
                    'deskripsi' => $b->deskripsi,
                    'gambar' => $b->gambar ? asset('storage/' . $b->gambar) : null,
                ];
            })->toArray();

            event(new \App\Events\BannerUpdated($bannerData));
        });

        static::deleted(function ($berita) {
            if ($berita->is_banner_active) {
                $activeBanners = static::where('is_banner_active', true)->get();
                $bannerData = $activeBanners->map(function($b) {
                    return [
                        'id' => $b->id,
                        'judul' => $b->judul,
                        'deskripsi' => $b->deskripsi,
                        'gambar' => $b->gambar ? asset('storage/' . $b->gambar) : null,
                    ];
                })->toArray();

                event(new \App\Events\BannerUpdated($bannerData));
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
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
