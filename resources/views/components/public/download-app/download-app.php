<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\Personnel;
use Illuminate\Support\Facades\Response;

new #[Title('Download Aplikasi TRC')] #[Layout('layouts::public')] class extends Component
{
    public string $pin = '';

    public function download()
    {
        $this->validate([
            'pin' => 'required|string|size:6',
        ]);

        $personnel = Personnel::where('pin', $this->pin)->first();

        if ($personnel) {
            $filePath = storage_path('app/protected-downloads/app-arm64-v8a-release.apk');
            
            if (!file_exists($filePath)) {
                $this->addError('pin', 'Maaf, file aplikasi tidak ditemukan di server.');
                return;
            }

            return redirect()->route('apk.download.direct', ['pin' => $this->pin]);
        }

        $this->addError('pin', 'PIN yang Anda masukkan tidak terdaftar atau salah.');
    }

    public function render()
    {
        return view('public::download-app.download-app');
    }
}
