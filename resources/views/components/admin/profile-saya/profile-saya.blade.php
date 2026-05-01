<div x-data="profileHandler()">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-base-content">Profil Saya</h1>
            <p class="text-sm text-base-content/60 mt-1">Kelola informasi pribadi dan keamanan akun Anda</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/40">
            <ul>
                <li><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li>Profil Saya</li>
            </ul>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar: Photo & Summary -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card bg-base-100 border border-base-200 overflow-hidden group">
                <div class="h-24 bg-gradient-to-br from-primary via-primary/80 to-secondary relative">
                    <div
                        class="absolute inset-0 opacity-20 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')]">
                    </div>
                </div>
                <div class="px-6 pb-8 -mt-12 text-center">
                    <div class="relative inline-block group">
                        <div class="avatar relative z-10">
                            <div
                                class="w-32 h-32 rounded-3xl ring-4 ring-base-100 ring-offset-0 bg-base-200 overflow-hidden transition-transform duration-500 group-hover:scale-105">
                                @if ($foto)
                                    <img src="{{ $foto->temporaryUrl() }}" class="object-cover" />
                                @elseif($old_foto)
                                    <img src="{{ asset('storage/' . $old_foto) }}" class="object-cover" />
                                @else
                                    <div
                                        class="flex items-center justify-center h-full bg-primary/10 text-primary font-bold text-4xl">
                                        {{ substr($name, 0, 1) }}
                                    </div>
                                @endif

                                <!-- Loading Overlay -->
                                <div wire:loading wire:target="foto"
                                    class="absolute inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center">
                                    <span class="loading loading-spinner loading-md text-white"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Photo Actions -->
                        <template x-if="isEditing">
                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 flex items-center gap-2 z-20">
                                <label for="foto-input"
                                    class="btn btn-circle btn-sm btn-primary shadow-lg cursor-pointer hover:scale-110 transition-transform">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2" stroke="currentColor" class="size-4">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15a2.25 2.25 0 002.25-2.25V9.574c0-1.067-.75-1.994-1.802-2.169a48.324 48.324 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
                                    </svg>
                                    <input id="foto-input" type="file" class="hidden"
                                        accept="image/png,image/jpeg,image/jpg,image/webp" @change="handleFileSelect" />
                                </label>
 
                                @if($foto || $old_foto)
                                    <button type="button" wire:click="removeFoto"
                                        class="btn btn-circle btn-sm btn-error shadow-lg hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </template>
                    </div>

                    <div class="mt-4">
                        <h2 class="text-xl font-bold text-base-content">{{ $name }}</h2>
                        <p class="text-xs font-medium text-base-content/40 uppercase tracking-widest">
                            {{ auth()->user()->roles->pluck('name')->implode(', ') }}</p>
                    </div>

                    <div class="mt-6 flex flex-col gap-2">
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-2xl border border-base-200">
                            <div class="size-8 rounded-xl bg-primary/10 flex items-center justify-center text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                </svg>
                            </div>
                            <div class="text-left overflow-hidden">
                                <p class="text-[10px] text-base-content/40 uppercase font-black">Email</p>
                                <p class="text-xs font-bold truncate">{{ $email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-base-200/50 rounded-2xl border border-base-200">
                            <div
                                class="size-8 rounded-xl bg-secondary/10 flex items-center justify-center text-secondary">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
                                </svg>
                            </div>
                            <div class="text-left overflow-hidden">
                                <p class="text-[10px] text-base-content/40 uppercase font-black">Nomor HP</p>
                                <p class="text-xs font-bold truncate">{{ $nomor_hp ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info bg-info/5 border-info/20 text-xs rounded-2xl flex items-start gap-3 p-4">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="size-5 shrink-0 text-info">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div class="leading-relaxed text-base-content">
                    <span class="font-bold">Tips Keamanan:</span> Gunakan password minimal 8 karakter
                    dengan kombinasi
                    huruf besar, huruf kecil, angka, dan simbol untuk keamanan maksimal.
                </div>
            </div>
        </div>

        <!-- Main Form -->
        <div class="lg:col-span-2">
            <form wire:submit="updateProfile" class="space-y-6">
                <!-- Basic Info Card -->
                <div class="card bg-base-100 border border-base-200 overflow-hidden">
                    <div class="p-1 bg-gradient-to-r from-primary/20 via-transparent to-transparent"></div>
                    <div class="card-body p-6 md:p-8">
                        <div class="flex items-center justify-between mb-8">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h3 class="text-xl font-black text-base-content">Informasi Dasar</h3>
                                    <p class="text-[10px] text-base-content/60">
                                        Klik tombol Edit untuk mengubah data profil</p>
                                </div>
                            </div>
                            <button type="button" @click="isEditing = !isEditing" class="btn-sm btn"
                                :class="isEditing ? 'btn-error btn-ghost' : 'btn-ghost'">
                                <span x-text="isEditing ? 'Batal' : 'Edit'"></span>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 gap-6">
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-bold text-base-content/70">Nama Lengkap <span
                                        class="text-error">*</span></span>
                                <input type="text" wire:model="name" :disabled="!isEditing"
                                    placeholder="Masukkan nama lengkap"
                                    class="input input-bordered w-full focus:input-primary bg-base-200/30 @error('name') input-error @enderror disabled:bg-base-200 disabled:text-base-content/40" />
                                @error('name')
                                    <span class="text-error text-xs mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-bold text-base-content/70">Email <span
                                        class="text-error">*</span></span>
                                <input type="email" wire:model="email" :disabled="!isEditing"
                                    placeholder="contoh@domain.com"
                                    class="input input-bordered w-full focus:input-primary bg-base-200/30 @error('email') input-error @enderror disabled:bg-base-200 disabled:text-base-content/40" />
                                @error('email')
                                    <span class="text-error text-xs mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-2">
                                <span class="text-sm font-bold text-base-content/70">Nomor HP</span>
                                <input type="text" wire:model="nomor_hp" :disabled="!isEditing"
                                    placeholder="08xxxxxxxxxx"
                                    class="input input-bordered w-full focus:input-primary bg-base-200/30 @error('nomor_hp') input-error @enderror disabled:bg-base-200 disabled:text-base-content/40" />
                                @error('nomor_hp')
                                    <span class="text-error text-xs mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Card -->
                <div class="card bg-base-100 border border-base-200 overflow-hidden" x-show="isEditing" x-transition>
                    <div class="p-1 bg-gradient-to-r from-secondary/20 via-transparent to-transparent"></div>
                    <div class="card-body p-6 md:p-8">
                        <div class="flex items-center gap-3 mb-8">
                            <div>
                                <h3 class="text-xl font-black text-base-content">Ubah Password</h3>
                                <p class="text-[10px] text-base-content/60">
                                    Biarkan kosong jika tidak ingin mengubah</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="flex flex-col gap-2" x-data="{ show: false }">
                                <span class="text-sm font-bold text-base-content/70">Password Baru</span>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" wire:model="password"
                                        placeholder="********" autocomplete="new-password"
                                        class="input input-bordered w-full focus:input-primary bg-base-200/30 @error('password') input-error @enderror" />
                                    <button type="button" @click="show = !show"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-primary transition-colors">
                                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-5" x-cloak>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                                @error('password')
                                    <span class="text-error text-xs mt-1 font-medium">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="flex flex-col gap-2" x-data="{ show: false }">
                                <span class="text-sm font-bold text-base-content/70">Konfirmasi Password</span>
                                <div class="relative">
                                    <input :type="show ? 'text' : 'password'" wire:model="password_confirmation"
                                        placeholder="********" autocomplete="new-password"
                                        class="input input-bordered w-full focus:input-primary bg-base-200/30" />
                                    <button type="button" @click="show = !show"
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-base-content/40 hover:text-primary transition-colors">
                                        <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-5">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        </svg>
                                        <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none"
                                            viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                                            class="size-5" x-cloak>
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Bar -->
                <div class="flex items-center justify-end gap-4" x-show="isEditing" x-transition>
                    <button type="submit"
                        class="btn btn-primary px-10 shadow-xl shadow-primary/20 transition-all hover:scale-105 active:scale-95"
                        wire:loading.attr="disabled">
                        <span wire:loading wire:target="updateProfile"
                            class="loading loading-spinner loading-xs"></span>
                        <span wire:loading.remove wire:target="updateProfile">Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function profileHandler() {
            return {
                isEditing: false,
                async handleFileSelect(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Check extension
                    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Format file tidak didukung! Gunakan png, jpeg, jpg, atau webp.');
                        event.target.value = '';
                        return;
                    }

                    // Client-side compression if > 2MB
                    let finalFile = file;
                    if (file.size > 2 * 1024 * 1024) {
                        console.log('File too large, compressing client-side...');
                        finalFile = await this.compressImage(file);
                    }

                    // Upload to Livewire
                    @this.upload('foto', finalFile,
                        (uploadedName) => {
                            console.log('Upload success');
                            event.target.value = ''; // Reset input value so change event fires again next time
                        },
                        (error) => {
                            console.error('Upload error', error);
                        },
                        (event) => {
                            /* Progress */
                        }
                    );
                },

                async compressImage(file) {
                    return new Promise((resolve) => {
                        const reader = new FileReader();
                        reader.readAsDataURL(file);
                        reader.onload = (event) => {
                            const img = new Image();
                            img.src = event.target.result;
                            img.onload = () => {
                                const canvas = document.createElement('canvas');
                                let width = img.width;
                                let height = img.height;

                                // Resize if too huge
                                const MAX_WIDTH = 1200;
                                if (width > MAX_WIDTH) {
                                    height *= MAX_WIDTH / width;
                                    width = MAX_WIDTH;
                                }

                                canvas.width = width;
                                canvas.height = height;
                                const ctx = canvas.getContext('2d');
                                ctx.drawImage(img, 0, 0, width, height);

                                canvas.toBlob((blob) => {
                                    resolve(new File([blob], file.name, {
                                        type: 'image/jpeg'
                                    }));
                                }, 'image/jpeg', 0.8); // 80% quality
                            };
                        };
                    });
                }
            }
        }
    </script>
</div>
