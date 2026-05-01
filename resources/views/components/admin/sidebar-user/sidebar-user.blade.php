<div class="p-4 border-t border-base-200 shrink-0">
    <div class="flex items-center gap-3">
        <div class="avatar">
            <div class="w-10 rounded-md overflow-hidden bg-base-200">
                @if(auth()->user()->foto)
                    <img src="{{ asset('storage/' . auth()->user()->foto) }}" alt="User Photo" />
                @else
                    <div class="flex items-center justify-center h-full bg-primary/10 text-primary font-bold text-lg">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                @endif
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
                <li class="{{ request()->routeIs('profile') ? 'rounded-lg' : '' }}">
                    <a href="{{ route('profile') }}" wire:navigate class="font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                        </svg>
                        Profil Saya
                    </a>
                </li>
                <li>
                    <button type="button" onclick="logout_modal_profile.showModal()"
                        class="flex items-center gap-2 cursor-pointer w-full text-left font-medium text-error">
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
