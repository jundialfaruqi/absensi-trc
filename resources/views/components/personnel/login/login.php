<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

new #[Layout('layouts::personnel.login.app')] #[Title('Login Personnel')] class extends Component {
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
        if (Auth::guard('personnel')->check()) {
            return $this->redirect('/personnel/dashboard', navigate: true);
        }
    }

    public function load()
    {
        $this->ready = true;
    }

    public function authenticate()
    {
        $this->validate();

        $maxAttempts = 5;
        $lockoutMinutes = 5;
        $attemptsKey = 'personnel_login_attempts_' . md5($this->email);
        $lockoutKey = 'personnel_login_lockout_' . md5($this->email);

        if ($lockoutUntil = Session::get($lockoutKey)) {
            $remainingSeconds = Carbon::now()->diffInSeconds($lockoutUntil, false);
            if ($remainingSeconds > 0) {
                $this->addError('loginError', "Terlalu banyak percobaan. Coba lagi dalam " . ceil($remainingSeconds) . " detik.");
                return;
            } else {
                Session::forget($lockoutKey);
                Session::forget($attemptsKey);
            }
        }

        try {
            if (Auth::guard('personnel')->attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
                Session::forget($attemptsKey);
                Session::forget($lockoutKey);
                Session::regenerate();

                return $this->redirect('/personnel/dashboard', navigate: true);
            }

            $attempts = Session::get($attemptsKey, 0) + 1;
            Session::put($attemptsKey, $attempts);

            if ($attempts >= $maxAttempts) {
                Session::put($lockoutKey, Carbon::now()->addMinutes($lockoutMinutes));
                $this->addError('loginError', "Akun Anda terkunci sementara (5 menit).");
                return;
            }

            $this->addError('loginError', "Email atau password salah. Sisa upaya: " . ($maxAttempts - $attempts));
        } catch (\Exception $e) {
            $this->addError('loginError', 'Sistem sedang sibuk. Silakan coba lagi nanti.');
        }
    }
};
