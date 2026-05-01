<?php
 
use Livewire\Component;
use App\Models\User;
use App\Models\Personnel;
use Illuminate\Support\Facades\Route;
 
new class extends Component
{
    public $search = '';
    public $recentSearches = [];
 
    public function mount()
    {
        $this->recentSearches = session()->get('recent_searches', []);
    }
 
    public function selectResult($item)
    {
        $recent = session()->get('recent_searches', []);
        
        // Remove if already exists to move to top
        $recent = collect($recent)->reject(fn($i) => $i['url'] === $item['url'])->values()->toArray();
        
        // Add to top
        array_unshift($recent, $item);
        
        // Limit to 5
        $recent = array_slice($recent, 0, 5);
        
        session()->put('recent_searches', $recent);
        $this->recentSearches = $recent;
 
        return $this->redirect($item['url'], navigate: true);
    }
 
    public function getResultsProperty()
    {
        if (strlen($this->search) < 2) {
            return [];
        }
 
        $user = auth()->user();
        $results = [];
 
        // 1. Search Menus (Filtered by Permission)
        $allMenus = [
            ['title' => 'Dashboard', 'url' => route('dashboard'), 'icon' => 'home', 'permission' => null],
            ['title' => 'Profil Saya', 'url' => route('profile'), 'icon' => 'user', 'permission' => null],
            ['title' => 'Manajemen Admin', 'url' => route('user'), 'icon' => 'users-cog', 'permission' => 'manajemen-user'],
            ['title' => 'Manajemen Personel', 'url' => route('personnel'), 'icon' => 'users', 'permission' => 'manajemen-personel'],
            ['title' => 'Absensi', 'url' => route('absensi'), 'icon' => 'calendar-check', 'permission' => 'manajemen-absensi'],
            ['title' => 'Jadwal Kerja', 'url' => route('jadwal'), 'icon' => 'calendar', 'permission' => 'manajemen-jadwal'],
            ['title' => 'Shift', 'url' => route('shift'), 'icon' => 'clock', 'permission' => 'manajemen-shift'],
            ['title' => 'Permohonan Cuti', 'url' => route('permohonan-cuti'), 'icon' => 'envelope-open', 'permission' => 'manajemen-permohonan-cuti'],
            ['title' => 'Log Absensi', 'url' => route('absensi.log'), 'icon' => 'clipboard-list', 'permission' => 'lihat-log-absensi'],
            ['title' => 'Manajemen Perangkat', 'url' => route('perangkat'), 'icon' => 'smartphone', 'permission' => 'manajemen-perangkat'],
            ['title' => 'Pengaturan Sistem', 'url' => route('pengaturan'), 'icon' => 'settings', 'permission' => 'manajemen-pengaturan'],
        ];
 
        $filteredMenus = collect($allMenus)->filter(function ($menu) use ($user) {
            if (!str_contains(strtolower($menu['title']), strtolower($this->search))) return false;
            if ($menu['permission'] && !$user->can($menu['permission'])) return false;
            return true;
        })->map(function ($menu) {
            $menu['type'] = 'Menu';
            return $menu;
        });
 
        // 2. Data Personnel, Data Absensi, Data Jadwal
        $personnelQuery = Personnel::query()
            ->when(!$user->hasRole('super-admin'), function ($q) use ($user) {
                $q->where('opd_id', $user->opd()?->id);
            })
            ->where('name', 'like', '%' . $this->search . '%')
            ->limit(5)
            ->get();
 
        $personnels = $personnelQuery->map(function ($p) {
            return [
                'title' => $p->name,
                'description' => $p->opd->name ?? '-',
                'url' => route('personnel') . '?search=' . $p->name,
                'icon' => 'user',
                'type' => 'Personel'
            ];
        });
 
        $absensiResults = [];
        if ($user->can('manajemen-absensi')) {
            $absensiResults = $personnelQuery->map(function ($p) {
                return [
                    'title' => 'Absensi: ' . $p->name,
                    'description' => 'Lihat data kehadiran personil ini',
                    'url' => route('absensi') . '?search=' . $p->name,
                    'icon' => 'calendar-check',
                    'type' => 'Data Absensi'
                ];
            });
        }
 
        $jadwalResults = [];
        if ($user->can('manajemen-jadwal')) {
            $jadwalResults = $personnelQuery->map(function ($p) {
                return [
                    'title' => 'Jadwal: ' . $p->name,
                    'description' => 'Lihat pengaturan jadwal personil ini',
                    'url' => route('jadwal') . '?search=' . $p->name,
                    'icon' => 'calendar',
                    'type' => 'Data Jadwal'
                ];
            });
        }
 
        // 3. Search Admin Users (Only if has permission)
        $users = [];
        if ($user->can('manajemen-user')) {
            $users = User::where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->limit(3)
                ->get()
                ->map(function ($u) {
                    return [
                        'title' => $u->name,
                        'description' => $u->email,
                        'url' => route('user') . '?search=' . $u->email,
                        'icon' => 'shield-check',
                        'type' => 'Admin'
                    ];
                });
        }
 
        return collect($filteredMenus)
            ->concat($personnels)
            ->concat($absensiResults)
            ->concat($jadwalResults)
            ->concat($users)
            ->all();
    }
 
    public function render()
    {
        return view('components.admin.global-search.global-search');
    }
};
