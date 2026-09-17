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
                    'gambar' => $b->gambar ? asset('storage/'.$b->gambar) : null,
                    'slug' => $b->slug,
                ];
            });

        return response()->json([
            'status' => 'success',
            'message' => 'Daftar banner aktif berhasil dimuat.',
            'data' => $banners,
        ]);
    }
}
