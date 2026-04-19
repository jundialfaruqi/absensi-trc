<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Ubah Data Personnel')] class extends Component {
    public $personnel;

    public $email;
    public $password;
    public $password_confirmation;
    public $pin;

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }

        $this->email = $this->personnel->email;
    }

    public function updateProfile()
    {
        $validated = $this->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('personnels', 'email')->ignore($this->personnel->id),
            ],
            'password' => 'nullable|min:8|confirmed',
            'pin' => 'nullable|numeric|digits:4',
        ], [
            'email.required' => 'Email wajib diisi',
            'email.unique' => 'Email sudah digunakan oleh personil lain',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password.min' => 'Password minimal 8 karakter',
            'pin.numeric' => 'PIN harus berupa angka',
            'pin.digits' => 'PIN harus tepat 4 digit angka',
        ]);

        $data = [
            'email' => $this->email,
        ];

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->pin) {
            $data['pin'] = Hash::make($this->pin);
        }

        $this->personnel->update($data);

        $this->password = '';
        $this->password_confirmation = '';
        $this->pin = '';

        session()->flash('success', 'Data profil Anda telah berhasil diperbarui.');
    }
};
