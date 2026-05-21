<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
     * Generate slug dari judul.
     * Format: judul-berita
     * Jika sudah ada: judul-berita-1
     */
    public static function generateSlug(string $judul, ?string $excludeId = null): string
    {
        $baseSlug = Str::slug($judul);

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
