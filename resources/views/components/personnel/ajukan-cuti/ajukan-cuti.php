<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Models\Cuti;
use App\Models\LeaveRequest;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Ajukan Cuti')] class extends Component {
    public $personnel;

    public $cuti_id;
    public $tanggal_mulai;
    public $tanggal_selesai;
    public $alasan;

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }
    }

    #[Computed]
    public function cutis()
    {
        return Cuti::orderBy('name')->get();
    }

    #[Computed]
    public function myRequests()
    {
        return LeaveRequest::where('personnel_id', $this->personnel->id)
            ->with('cuti')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function submit()
    {
        $this->validate([
            'cuti_id' => 'required|exists:cutis,id',
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan' => 'required|min:10',
        ], [
            'cuti_id.required' => 'Pilih jenis cuti.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'alasan.min' => 'Alasan minimal 10 karakter.',
        ]);

        LeaveRequest::create([
            'personnel_id' => $this->personnel->id,
            'cuti_id' => $this->cuti_id,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'alasan' => $this->alasan,
            'status' => 'PENDING',
        ]);

        $this->reset(['cuti_id', 'tanggal_mulai', 'tanggal_selesai', 'alasan']);
        session()->flash('success', 'Permohonan cuti Anda telah berhasil dikirim dan sedang menunggu persetujuan admin.');
    }

    public function cancelRequest($id)
    {
        $request = LeaveRequest::where('personnel_id', $this->personnel->id)
            ->where('status', 'PENDING')
            ->findOrFail($id);
            
        $request->delete();
        session()->flash('success', 'Permohonan cuti berhasil dibatalkan.');
    }
};
