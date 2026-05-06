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
    public ?string $recaptchaToken = null;

    public function download()
    {
        $this->validate([
            'pin' => 'required|string|size:6',
        ]);

        // Verify reCAPTCHA only in Production
        if (!app()->isLocal()) {
            if (!$this->recaptchaToken) {
                $this->addError('pin', 'Silakan centang reCAPTCHA terlebih dahulu.');
                return;
            }

            $response = \Illuminate\Support\Facades\Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $this->recaptchaToken,
                'remoteip' => request()->ip(),
            ]);

            if (!$response->json('success')) {
                $this->addError('pin', 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                return;
            }
        }

        $throttleKey = 'download-app:' . request()->ip();

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = \Illuminate\Support\Facades\RateLimiter::availableIn($throttleKey);
            $this->addError('pin', "Terlalu banyak percobaan. Silakan coba lagi dalam $seconds detik.");
            return;
        }

        $personnel = Personnel::where('pin', $this->pin)->first();

        if ($personnel) {
            \Illuminate\Support\Facades\RateLimiter::clear($throttleKey);
            $filePath = storage_path('app/protected-downloads/app-arm64-v8a-release.apk');
            
            if (!file_exists($filePath)) {
                $this->addError('pin', 'Maaf, file aplikasi tidak ditemukan di server.');
                return;
            }

            return redirect()->route('apk.download.direct', ['pin' => $this->pin]);
        }

        \Illuminate\Support\Facades\RateLimiter::hit($throttleKey, 60);
        $this->addError('pin', 'PIN yang Anda masukkan tidak terdaftar atau salah.');
    }

    public function render()
    {
        return view('public::download-app.download-app');
    }
}
