<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

new #[Layout('layouts::login.app')] #[Title('Login')] class extends Component {
    public $ready = false;

    #[Validate]
    public $email = '';

    #[Validate]
    public $password = '';

    public $remember = false;

    protected function rules()
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function messages()
    {
        return [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password wajib diisi',
        ];
    }

    public function mount()
    {
        if (Auth::check()) {
            return $this->redirect('/page/dashboard', navigate: true);
        }
    }

    public function load()
    {
        $this->ready = true;
    }

    public function authenticate()
    {
        $this->validate();

        // Rate limiting implementation
        $maxAttempts = 5;
        $lockoutMinutes = 1;
        $attemptsKey = 'login_failed_attempts_' . md5($this->email);
        $lockoutKey = 'login_lockout_until_' . md5($this->email);

        // Check if user is locked out
        $lockoutUntil = Session::get($lockoutKey);
        if ($lockoutUntil) {
            // Hitung detik tersisa dengan urutan parameter yang benar: now()->diffInSeconds(future_date, false)
            $remainingSeconds = Carbon::now()->diffInSeconds($lockoutUntil, false);
            if ($remainingSeconds > 0) {
                // Konversi ke detik bulat untuk kemudahan user
                $remainingSeconds = ceil($remainingSeconds);
                $this->addError('loginError', "Terlalu banyak upaya gagal. Silakan coba lagi dalam {$remainingSeconds} detik.");
                return;
            } else {
                // Reset lockout jika waktu sudah habis
                Session::forget($lockoutKey);
                Session::forget($attemptsKey);
            }
        }

        try {
            if (Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                Session::forget($attemptsKey);
                Session::forget($lockoutKey);
                Session::regenerate();

                return $this->redirect('/page/dashboard', navigate: true);
            }

            // Handle failed login
            $failedAttempts = Session::get($attemptsKey, 0) + 1;
            Session::put($attemptsKey, $failedAttempts);

            // Lockout if max attempts reached
            if ($failedAttempts >= $maxAttempts) {
                Session::put($lockoutKey, Carbon::now()->addMinutes($lockoutMinutes));
                $this->addError('loginError', "Terlalu banyak upaya gagal. Silakan coba lagi dalam {$lockoutMinutes} menit.");
                return;
            }

            // Show remaining attempts
            $remainingAttempts = $maxAttempts - $failedAttempts;
            $this->addError('loginError', "Email atau password salah. (Upaya tersisa: {$remainingAttempts})");
        } catch (\Exception) {
            $this->addError('loginError', 'Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.');
        }
    }
};
