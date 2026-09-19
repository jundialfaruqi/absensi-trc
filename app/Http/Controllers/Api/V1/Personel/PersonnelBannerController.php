<?php

namespace App\Http\Controllers\Api\V1\Personel;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonnelBannerController extends Controller
{
    /**
     * Dapatkan daftar banner pengumuman / berita aktif untuk aplikasi mobile.
     */
    public function index(Request $request): JsonResponse
    {
        $banners = Berita::where('is_banner_active', true)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($b) {
                return [
                    'id' => $b->id,
                    'judul' => $b->judul,
                    'deskripsi' => $b->deskripsi,
                    'isi' => $b->isi,
                    'kategori' => $b->kategori,
                    'gambar' => $b->gambar ? asset('storage/'.$b->gambar) : null,
                    'slug' => $b->slug,
                    'created_at' => $b->created_at ? $b->created_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar banner aktif berhasil dimuat.',
            'data' => $banners,
        ]);
    }

    /**
     * Dapatkan detail artikel / pengumuman berdasarkan ID atau Slug.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $berita = Berita::where('id', $id)->orWhere('slug', $id)->first();

        if (! $berita) {
            return response()->json([
                'status' => 'error',
                'message' => 'Artikel tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Detail artikel berhasil dimuat.',
            'data' => [
                'id' => $berita->id,
                'judul' => $berita->judul,
                'deskripsi' => $berita->deskripsi,
                'isi' => $berita->isi,
                'kategori' => $berita->kategori,
                'gambar' => $berita->gambar ? asset('storage/'.$berita->gambar) : null,
                'slug' => $berita->slug,
                'created_at' => $berita->created_at ? $berita->created_at->toIso8601String() : null,
            ],
        ]);
    }
}

