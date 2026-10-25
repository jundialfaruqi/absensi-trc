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

    public bool $showOptionModal = false;

    public ?string $personnelName = null;

    public ?string $directDownloadUrl = null;

    public string $gdriveUrl = '';

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
                'secret' => config('services.recaptcha.secret_key'),
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

            session()->put('apk_download_allowed', [
                'pin' => $this->pin,
                'expired_at' => now()->addMinutes(15)->timestamp,
            ]);

            $this->personnelName = $personnel->name;
            $this->directDownloadUrl = route('apk.download.direct', ['pin' => $this->pin]);
            $this->gdriveUrl = config('services.apk.gdrive_url', 'https://drive.google.com/drive/folders/1m1QFHXHM5yuhwcVIDntJaVH4EePc29QK?usp=sharing');
            $this->showOptionModal = true;

            return;
        }

        RateLimiter::hit($throttleKey, 60);
        $this->addError('pin', 'PIN yang Anda masukkan tidak terdaftar atau salah.');
        $this->dispatch('reset-captcha');
    }

    public function triggerDirectDownload()
    {
        if ($this->directDownloadUrl) {
            $this->dispatch('trigger-download', url: $this->directDownloadUrl);
        }
    }

    public function closeOptionModal()
    {
        $this->showOptionModal = false;
        $this->dispatch('reset-captcha');
    }

    public function render()
    {
        return view('public::download-app.download-app');
    }
};
