<?php

namespace App\Livewire\Public;

use App\Models\Personnel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Download Aplikasi TRC')] #[Layout('layouts::public.app')] class extends Component
{
    public string $pin = '';

    public ?string $recaptchaToken = null;

    public function download()
    {
        $this->resetErrorBag();

        $this->validate([
            'pin' => 'required|string|size:6',
        ], [
            'pin.required' => 'PIN Wajib diisi.',
            'pin.size' => 'PIN harus 6 digit.',
        ]);

        // Verify reCAPTCHA only in Production
        if (! app()->isLocal()) {
            if (! $this->recaptchaToken) {
                $this->addError('pin', 'Silakan centang reCAPTCHA terlebih dahulu.');
                $this->dispatch('reset-captcha');

                return;
            }

            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => env('RECAPTCHA_SECRET_KEY'),
                'response' => $this->recaptchaToken,
                'remoteip' => request()->ip(),
            ]);

            if (! $response->json('success')) {
                $this->addError('pin', 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.');
                $this->dispatch('reset-captcha');

                return;
            }
        }

        $throttleKey = 'download-app:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('pin', "Terlalu banyak percobaan. Silakan coba lagi dalam $seconds detik.");
            $this->dispatch('reset-captcha');

            return;
        }

        $personnel = Personnel::where('pin', $this->pin)->first();

        if ($personnel) {
            RateLimiter::clear($throttleKey);
            $filePath = storage_path('app/protected-downloads/app-arm64-v8a-release.apk');

            if (! file_exists($filePath)) {
                $this->addError('pin', 'Maaf, file aplikasi tidak ditemukan di server.');
                $this->dispatch('reset-captcha');

                return;
            }

            session()->put('apk_download_allowed', $this->pin);

            $this->dispatch('trigger-download', url: route('apk.download.direct', ['pin' => $this->pin]));

            return;
        }

        RateLimiter::hit($throttleKey, 60);
        $this->addError('pin', 'PIN yang Anda masukkan tidak terdaftar atau salah.');
        $this->dispatch('reset-captcha');
    }

    public function render()
    {
        return view('public::download-app.download-app');
    }
};
