<?php

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Illuminate\Support\Facades\Auth;
use App\Models\Jadwal;
use Carbon\Carbon;

new #[Layout('layouts::personnel.dashboard.app')] #[Title('Jadwal Saya')] class extends Component {
    public $personnel;
    public string $month = '';
    public string $year = '';

    public function mount()
    {
        $this->personnel = Auth::guard('personnel')->user();
        if (!$this->personnel) {
            return $this->redirect('/personnel/login', navigate: true);
        }

        $this->month = Carbon::now()->format('m');
        $this->year = Carbon::now()->format('Y');
    }

    #[Computed]
    public function dates(): array
    {
        $daysInMonth = Carbon::create($this->year, $this->month, 1)->daysInMonth;
        $dates = [];
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $dates[] = Carbon::create($this->year, $this->month, $i)->format('Y-m-d');
        }
        return $dates;
    }

    #[Computed]
    public function jadwalMap()
    {
        return Jadwal::where('personnel_id', $this->personnel->id)
            ->whereYear('tanggal', $this->year)
            ->whereMonth('tanggal', $this->month)
            ->with('shift')
            ->get()
            ->keyBy(fn($j) => $j->tanggal->format('Y-m-d'));
    }

    public function updatedMonth()
    {
        // Reset properties if needed
    }

    public function updatedYear()
    {
        // Reset properties if needed
    }
};
