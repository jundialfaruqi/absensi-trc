<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<!--

  Name              : ABSENSI TRC
  Version           : 1.0
  Date              : April 14, 2026
  Url               : -
  Type              : Web APP
  Project Analyst   : Deni Hidayat
  Developer         : M. Jundi Al faruqi

  ============================================================================
  NOTE :
  Website ini dibuat oleh tim DISKOMINFOTIKSAN Pekanbaru.
  ============================================================================

-->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @include('layouts.admin.app-scripts')

    @stack('styles')
</head>

<body class="bg-base-200 font-inter text-base-content">

    <div class="drawer lg:drawer-open">
        <input id="my-drawer" type="checkbox" class="drawer-toggle" />

        <!-- Drawer Content (Main Page) -->
        <div class="drawer-content flex flex-col h-screen overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-base-100 flex items-center justify-between px-6 border-b border-base-200 shrink-0">
                <style>
                    @keyframes bell-ring {

                        0%,
                        100% {
                            transform: rotate(0deg);
                        }

                        5%,
                        15%,
                        25% {
                            transform: rotate(15deg);
                        }

                        10%,
                        20%,
                        30% {
                            transform: rotate(-15deg);
                        }

                        35% {
                            transform: rotate(0deg);
                        }
                    }

                    .animate-bell-ring {
                        animation: bell-ring 2s ease-in-out infinite;
                        transform-origin: top center;
                    }
                </style>
                <div class="flex items-center gap-4">
                    <!-- Hamburger Menu (Mobile Toggle) -->
                    <label for="my-drawer" class="btn btn-square btn-ghost lg:hidden drawer-button">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-6 h-6 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16">
                            </path>
                        </svg>
                    </label>
                    <!-- Search -->
                    <div class="form-control hidden sm:block">
                        <div class="input-group">
                            <div class="relative">
                                <input type="text" placeholder="Search" disabled
                                    class="input input-bordered w-full max-w-xs pl-10 h-10 bg-base-100 rounded-full" />
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-base-content/50" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Section Notifications -->
                    @php
                        $user = auth()->user();
                        $isSuperAdmin = $user->hasRole('super-admin');
                        $opdId = $user->opd()?->id;

                        $dashboardNotifications = \App\Models\LeaveRequest::query()
                            ->with(['personnel', 'cuti'])
                            ->where('status', 'PENDING')
                            ->when(!$isSuperAdmin, function ($q) use ($opdId) {
                                $q->whereHas('personnel', function ($pq) use ($opdId) {
                                    $pq->where('opd_id', $opdId);
                                });
                            })
                            ->orderBy('created_at', 'desc')
                            ->take(10)
                            ->get()
                            ->map(function ($req) {
                                return [
                                    'url' => route('permohonan-cuti'),
                                    'color' => 'warning',
                                    'icon' => 'calendar',
                                    'category' => 'PENGAJUAN CUTI',
                                    'type' => 'PENDING',
                                    'title' => $req->personnel->name,
                                    'message' =>
                                        'Mengajukan ' .
                                        $req->cuti->name .
                                        ' (' .
                                        \Carbon\Carbon::parse($req->tanggal_mulai)->format('d/m/Y') .
                                        ')',
                                ];
                            });
                    @endphp
                    <div class="dropdown dropdown-end">
                        <button tabindex="0" class="btn btn-secondary btn-circle">
                            <div class="indicator">
                                @if ($dashboardNotifications->count() > 0)
                                    <div class="indicator-item inline-grid *:[grid-area:1/1] scale-75">
                                        <div class="status status-error animate-ping"></div>
                                        <div class="status status-error"></div>
                                    </div>
                                @endif
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor"
                                    class="w-5 h-5 @if ($dashboardNotifications->count() > 0) animate-bell-ring @endif">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                </svg>
                            </div>
                        </button>
                        <div tabindex="0"
                            class="dropdown-content z-1 card card-compact w-80 shadow bg-base-100 border border-base-200 mt-2">
                            <div class="card-body p-3">
                                <div class="flex items-center justify-between mb-1.5">
                                    <h3 class="font-bold text-sm">Notifikasi</h3>
                                    <span
                                        class="badge badge-sm badge-neutral">{{ $dashboardNotifications->count() }}</span>
                                </div>
                                <div class="max-h-80 overflow-y-auto space-y-1 -mx-1 px-1">
                                    @forelse($dashboardNotifications as $notif)
                                        <a href="{{ $notif['url'] }}"
                                            class="flex items-start gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors border border-transparent hover:border-base-300 group">
                                            <div
                                                class="w-8 h-8 rounded-full bg-{{ $notif['color'] }}/10 flex items-center justify-center shrink-0 group-hover:bg-{{ $notif['color'] }}/20">
                                                @if ($notif['icon'] === 'calendar')
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4 text-{{ $notif['color'] }}">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                    </svg>
                                                @elseif($notif['icon'] === 'ticket')
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4 text-{{ $notif['color'] }}">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                        class="w-4 h-4 text-{{ $notif['color'] }}">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                                    </svg>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-1 mb-0.5">
                                                    <span
                                                        class="text-[9px] font-bold uppercase tracking-wider text-base-content/40">{{ $notif['category'] }}</span>
                                                    <span
                                                        class="text-[9px] font-bold px-1 rounded-full bg-{{ $notif['color'] }}/10 text-{{ $notif['color'] }}">{{ $notif['type'] }}</span>
                                                </div>
                                                <p class="text-xs font-bold truncate text-base-content">
                                                    {{ $notif['title'] }}
                                                </p>
                                                <p
                                                    class="text-[11px] text-base-content/60 line-clamp-2 mt-0.5 leading-tight">
                                                    {{ $notif['message'] }}</p>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="py-10 text-center">
                                            <div
                                                class="w-12 h-12 rounded-full bg-base-200 flex items-center justify-center mx-auto mb-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                                    class="w-6 h-6 opacity-20">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                                </svg>
                                            </div>
                                            <p class="text-sm font-medium text-base-content/40">Tidak ada data yang
                                                memerlukan
                                                perhatian</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="btn btn-circle btn-primary hover:bg-base hover:text-white">
                        <!-- Sun Icon -->
                        <svg id="sun-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <!-- Moon Icon -->
                        <svg id="moon-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-5 h-5 hidden">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>

                    <!-- Divider -->
                    <div class="hidden md:block h-8 border-l border-dotted border-base-content/20"></div>

                    <!-- Date and Time -->
                    <div class="hidden md:flex flex-col items-start leading-tight">
                        <span class="text-xs font-bold text-base-content/80">
                            {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('dddd') }}
                        </span>
                        <span class="text-[10px] text-base-content/50">
                            {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->locale('id')->isoFormat('D MMMM YYYY') }}
                        </span>
                    </div>
                </div>
            </header>


            <!-- Scrollable Main Content -->
            <main class="flex-1 overflow-y-auto p-6 bg-base-200">
                {{ $slot }}
            </main>

            <footer class="footer footer-center p-4 bg-base-100 text-base-content border-t border-base-200 mt-auto">
                <aside>
                    <p>Copyright © {{ date('Y') }} - <span class="font-bold">DISKOMINFOTIKSAN</span></p>
                </aside>
            </footer>
        </div>

        <!-- Sidebar (Drawer Side) -->
        <div class="drawer-side z-130">
            <label for="my-drawer" aria-label="close sidebar" class="drawer-overlay"></label>
            <aside class="menu p-0 w-64 h-full bg-base-100 border-r border-base-300 flex flex-col overflow-hidden">
                <!-- Logo -->
                <div class="h-16 flex items-center px-2 border-b border-base-200 shrink-0">
                    <div class="flex items-center gap-0 text-secondary font-bold text-2xl group">
                        <img src="{{ asset('assets/logo/trc-logo.webp') }}" alt="Logo"
                            class="h-13 w-13 sm:h-13 sm:w-13 object-contain">
                        <div class="flex flex-col">
                            <span
                                class="font-black tracking-tight uppercase text-base-content group-hover:text-base-content transition-colors leading-none">Absensi
                                TRC</span>
                            <span
                                class="text-[8px] tracking-widest uppercase text-base-content/50 group-hover:text-base-content/70 transition-colors leading-none">Dinas
                                Komunikasi, Informatika & Statistik</span>
                        </div>
                    </div>
                </div>

                <!-- Scrollable Navigation -->
                <div class="flex-1 overflow-y-auto no-scrollbar py-4">
                    <ul class="menu w-full px-4 gap-1">

                        <li class="menu-title text-xs font-semibold opacity-50 uppercase mb-1">Overview</li>

                        <li>
                            <a wire:navigate href="{{ route('dashboard') }}"
                                class="{{ request()->routeIs('dashboard*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                    </svg>
                                    <span>Dashboard</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Halaman utama
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('absensi') }}"
                                class="{{ request()->routeIs('absensi*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-calendar-check size-5">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M11.5 21h-5.5a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v6" />
                                        <path d="M16 3v4" />
                                        <path d="M8 3v4" />
                                        <path d="M4 11h16" />
                                        <path d="M15 19l2 2l4 -4" />
                                    </svg>
                                    <span>Absensi</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Monitoring Kehadiran
                                </span>
                            </a>
                        </li>

                        <li>
                            @php
                                $pendingCutiCount = \App\Models\LeaveRequest::where('status', 'PENDING')
                                    ->when(!auth()->user()->hasRole('super-admin'), function ($q) {
                                        $q->whereHas('personnel', function ($pq) {
                                            $pq->where('opd_id', auth()->user()->opd()?->id);
                                        });
                                    })
                                    ->count();
                            @endphp
                            <a wire:navigate href="{{ route('permohonan-cuti') }}"
                                class="{{ request()->routeIs('permohonan-cuti*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5 relative">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                    </svg>
                                    <span>Permohonan Cuti</span>
                                    @if ($pendingCutiCount > 0)
                                        <span
                                            class="absolute top-2 right-4 badge badge-xs badge-error animate-pulse">{{ $pendingCutiCount }}</span>
                                    @endif
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Persetujuan Izin & Cuti
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('personnel') }}"
                                class="{{ request()->routeIs('personnel*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                    </svg>
                                    <span>Personnel</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">Data Personnel</span>
                            </a>
                        </li>

                        <li class="menu-title text-xs font-semibold opacity-50 uppercase mt-4 mb-1">Data</li>

                        <li>
                            <a wire:navigate href="{{ route('opd') }}"
                                class="{{ request()->routeIs('opd*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                                    </svg>
                                    <span>OPD</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Data Master OPD
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('penugasan') }}"
                                class="{{ request()->routeIs('penugasan*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                    </svg>
                                    <span>Penugasan</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Data Penugasan Karyawan
                                </span>
                            </a>
                        </li>

                        <li>
                            <a href="{{ route('kantor') }}"
                                class="{{ request()->routeIs('kantor*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                    <span>Kantor</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Lokasi & Radius Absensi
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('shift') }}"
                                class="{{ request()->routeIs('shift*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span>Shift</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Data Jam Kerja
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('jadwal') }}"
                                class="{{ request()->routeIs('jadwal*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-18 0h18" />
                                    </svg>
                                    <span>Jadwal</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Penjadwalan Personnel
                                </span>
                            </a>
                        </li>

                        <li>
                            <a wire:navigate href="{{ route('cuti') }}"
                                class="{{ request()->routeIs('cuti*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                <div class="flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                                    </svg>
                                    <span>Master Cuti</span>
                                </div>
                                <span class="text-[8px] text-base-content opacity-50 ml-7">
                                    Manajemen Jenis Cuti
                                </span>
                            </a>
                        </li>

                        <li class="menu-title text-xs font-semibold opacity-50 uppercase mt-4 mb-1">Settings</li>

                        <li>
                            <details open>
                                <summary class="group font-medium mb-1 hover:bg-base-100">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                    </svg>
                                    User
                                </summary>
                                <ul>
                                    <li>
                                        <a wire:navigate href="{{ route('user') }}"
                                            class="{{ request()->routeIs('user*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                            Manajemen User
                                            <span class="text-[8px] text-base-content opacity-50">
                                                Lihat, Ubah dan Aktivasi User
                                            </span>
                                        </a>
                                    </li>
                                    <li>
                                        <a wire:navigate href="{{ route('role-permission') }}"
                                            class="{{ request()->routeIs('role-permission*') || request()->routeIs('role-permission*') ? 'active bg-base-300 text-base-content font-medium' : '' }} flex flex-col items-start gap-0.5">
                                            Roles & Permissions
                                            <span class="text-[8px] text-base-content opacity-50">
                                                Manajemen Role & Permission
                                            </span>
                                        </a>
                                    </li>
                                </ul>
                            </details>
                        </li>

                        <li class="mt-4">
                            <a href="{{ route('absensi-web') }}" class="text-primary font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 7.5l-2.25-1.313M21 7.5v2.25m0-2.25l-2.25 1.313M3 7.5l2.25-1.313M3 7.5l2.25 1.313M3 7.5v2.25m9 3l2.25-1.313M12 12.75l-2.25-1.313M12 12.75V15m0 6.75l2.25-1.313M12 21.75V19.5m0 2.25l-2.25-1.313m0-16.875L12 2.25l2.25 1.313M21 14.25v2.25l-2.25 1.313m-13.5 0L3 16.5v-2.25" />
                                </svg>
                                Isi Absen
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- User Profile (Bottom Sidebar) -->
                <div class="p-4 border-t border-base-200 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="avatar">
                            <div class="w-10 rounded-md">
                                <img src="{{ asset('assets/logo/logo-pemko.webp') }}" alt="Logo Pemko" />
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold truncate">
                                {{ auth()->user()->name ?? 'Nama User' }}
                            </p>
                            <p class="text-xs text-base-content/60 truncate">
                                {{ auth()->user()->email ?? 'emailuser@mail.com' }}
                            </p>
                        </div>
                        <div class="dropdown dropdown-end dropdown-top">
                            <label tabindex="0" class="btn btn-ghost btn-xs btn-circle">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
                                </svg>
                            </label>
                            <ul tabindex="0"
                                class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-40 border border-base-200">
                                <li class="{{ request()->routeIs('profile') ? 'bg-primary/10 rounded-lg' : '' }}">
                                    <a href="#"
                                        class="text-primary-content font-medium opacity-40 pointer-events-none cursor-not-allowed">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                        Profile
                                    </a>
                                </li>
                                <li>
                                    @csrf
                                    <button type="button" onclick="logout_modal_profile.showModal()"
                                        class="flex items-center gap-2 cursor-pointer w-full text-left font-medium">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M5.636 5.636a9 9 0 1 0 12.728 0M12 3v9" />
                                        </svg>
                                        Logout
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </aside>

        </div>

    </div>
    @livewireScripts

    <div id="global-toast" class="toast toast-top toast-end" wire:ignore></div>

    <livewire:logout />
</body>

</html>
