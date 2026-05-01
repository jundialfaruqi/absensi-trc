<?php
 
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
 
new #[Title('Profil Saya')] #[Layout('layouts::admin.app')] class extends Component
{
    use WithFileUploads;
 
    public $name;
    public $email;
    public $nomor_hp;
    public $foto;
    public $old_foto;
    public $password;
    public $password_confirmation;
 
    public function mount()
    {
        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->nomor_hp = $user->nomor_hp;
        $this->old_foto = $user->foto;
    }
 
    public function updateProfile()
    {
        $user = auth()->user();
        
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'nomor_hp' => 'nullable|string|max:20',
            'foto' => 'nullable|image|mimes:png,jpeg,jpg,webp|max:2048',
            'password' => 'nullable|min:8|confirmed',
        ], [
            'name.required' => 'Nama wajib diisi.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan oleh pengguna lain.',
            'foto.image' => 'File harus berupa gambar.',
            'foto.mimes' => 'Format foto harus png, jpeg, jpg, atau webp.',
            'foto.max' => 'Ukuran foto maksimal 2MB.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
 
        $data = [
            'name' => $this->name,
            'email' => $this->email,
            'nomor_hp' => $this->nomor_hp,
        ];
 
        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }
 
        if ($this->foto) {
            if ($user->foto) {
                Storage::disk('public')->delete($user->foto);
            }
            $data['foto'] = $this->foto->store('user-fotos', 'public');
        } elseif (!$this->old_foto && $user->foto) {
            Storage::disk('public')->delete($user->foto);
            $data['foto'] = null;
        }
 
        $user->update($data);
 
        $this->password = '';
        $this->password_confirmation = '';
        $this->old_foto = $user->foto;
        $this->foto = null;
 
        $this->dispatch('toast', type: 'success', title: 'Berhasil', message: 'Profil Anda telah diperbarui.');
        $this->dispatch('profile-updated');
    }
 
    public function removeFoto()
    {
        $this->foto = null;
        $this->old_foto = null;
        $this->dispatch('profile-updated');
    }
};
