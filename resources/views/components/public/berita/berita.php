<?php

use App\Models\Berita;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts::public.app')] class extends Component
{
    public Berita $berita;
    public $beritaTerbaru;
    public $beritaTerkait;

    /**
     * Display the specified news article.
     *
     * @param  \App\Models\Berita  $berita
     * @return void
     */
    public function mount(Berita $berita)
    {
        $this->berita = $berita;

        $this->beritaTerbaru = Berita::where('id', '!=', $this->berita->id)
            ->latest()
            ->take(5)
            ->get();

        $this->beritaTerkait = Berita::where('id', '!=', $this->berita->id)
            ->where('kategori', $this->berita->kategori)
            ->latest()
            ->take(2)
            ->get();
    }

    /**
     * Render the component.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('components.public.berita.berita')
            ->layoutData([
                'berita' => $this->berita
            ]);
    }
};
