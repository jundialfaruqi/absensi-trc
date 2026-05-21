<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use Illuminate\Http\Request;

class BeritaController extends Controller
{
    /**
     * Display the specified news article.
     *
     * @param  \App\Models\Berita  $berita
     * @return \Illuminate\View\View
     */
    public function show(Berita $berita)
    {
        $beritaTerbaru = Berita::where('id', '!=', $berita->id)
            ->latest()
            ->take(5)
            ->get();
        
        $beritaTerkait = Berita::where('id', '!=', $berita->id)
            ->where('kategori', $berita->kategori)
            ->latest()
            ->take(5)
            ->get();

        return view('public.berita.show', compact('berita', 'beritaTerbaru', 'beritaTerkait'));
    }
}
