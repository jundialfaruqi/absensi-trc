<div>
    {{-- ─── Page Header ───────────────────────────────────────────────────── --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-black uppercase">Buat Berita</h1>
            <p class="text-sm text-base-content/60 mt-1">Tulis berita atau informasi baru</p>
        </div>
        <div class="text-sm breadcrumbs text-base-content/60 hidden md:block">
            <ul>
                <li><a href="{{ route('dashboard') }}">{{ config('app.name') }}</a></li>
                <li>Overview</li>
                <li><a href="{{ route('berita') }}">Berita</a></li>
                <li>
                    <span class="text-base-content font-bold">Buat Berita</span>
                </li>
            </ul>
        </div>
    </div>

    <form x-data x-on:submit.prevent="@this.set('isi', document.querySelector('#quill-editor .ql-editor').innerHTML); @this.save()">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- ─── Main Content ───────────────────────────────────────────── --}}
            <div class="lg:col-span-2 space-y-6">

                {{-- Isi Berita (Summernote) --}}
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body">

                        {{-- Judul --}}
                        <div class="form-control mb-6">
                            <label class="label mb-1"><span
                                    class="label-text text-sm font-medium text-base-content">Judul
                                    Berita</span></label>
                            <input type="text" wire:model="judul"
                                class="input input-bordered w-full bg-base-100 placeholder:text-base-content/40"
                                placeholder="Masukkan judul berita..." />
                            @error('judul')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div wire:ignore>
                            <div id="quill-editor">{!! $isi !!}</div>
                        </div>
                        @error('isi')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- ─── Sidebar ───────────────────────────────────────────── --}}
            <div>
                <div class="card bg-base-100 shadow-sm">
                    <div class="card-body space-y-4">
                        {{-- Kategori --}}
                        <div class="form-control space-y-2">
                            <label class="label mb-1"><span
                                    class="label-text text-sm font-medium text-base-content">Kategori</span></label>

                            {{-- Pilih --}}
                            <div>
                                <label class="label-text text-xs text-base-content/60">Pilih dari yang sudah
                                    ada:</label>
                                <select wire:model.live="selectedKategori"
                                    class="select select-bordered select-sm w-full bg-base-100">
                                    <option value="">-- Pilih --</option>
                                    @foreach ($this->existingKategoris as $k)
                                        <option value="{{ $k }}">{{ $k }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Atau Ketik --}}
                            @if (!$selectedKategori)
                                <div>
                                    <label class="label-text text-xs text-base-content/60">Atau ketik kategori
                                        baru:</label>
                                    <input type="text" wire:model.live.debounce.300ms="newKategori"
                                        class="input input-bordered input-sm w-full bg-base-100 placeholder:text-base-content/40"
                                        placeholder="Ketik kategori..." />

                                    @if ($newKategori && in_array(trim($newKategori), $this->existingKategoris->toArray()))
                                        <div class="badge badge-info badge-xs mt-1">Kategori sudah ada</div>
                                    @endif
                                </div>
                            @endif

                            @error('kategori')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="divider my-0"></div>

                        {{-- Deskripsi --}}
                        <div class="form-control">
                            <label class="label mb-1"><span
                                    class="label-text text-sm font-medium text-base-content">Deskripsi
                                    Singkat</span></label>
                            <textarea wire:model="deskripsi"
                                class="textarea textarea-bordered w-full h-24 bg-base-100 placeholder:text-base-content/40"
                                placeholder="Ringkasan singkat berita (opsional)..."></textarea>
                            @error('deskripsi')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="divider my-0"></div>

                        {{-- Gambar --}}
                        <div class="form-control" x-data="{ isConverting: false, progress: 0 }">
                            <label class="label mb-1"><span
                                    class="label-text text-sm font-medium text-base-content">Gambar Cover</span></label>

                            <input type="file" accept="image/*"
                                class="file-input file-input-bordered w-full bg-base-100"
                                x-on:change="
                                    const file = $event.target.files[0];
                                    if (!file) return;

                                    isConverting = true;
                                    progress = 10;

                                    const reader = new FileReader();
                                    reader.readAsDataURL(file);
                                    reader.onload = function(e) {
                                        const img = new Image();
                                        img.src = e.target.result;
                                        img.onload = function() {
                                            const canvas = document.createElement('canvas');
                                            let width = img.width;
                                            let height = img.height;
                                            const max = 1200; // Batasi dimensi maks
                                            if (width > max || height > max) {
                                                if (width > height) {
                                                    height = Math.round(height * max / width);
                                                    width = max;
                                                } else {
                                                    width = Math.round(width * max / height);
                                                    height = max;
                                                }
                                            }
                                            canvas.width = width;
                                            canvas.height = height;
                                            const ctx = canvas.getContext('2d');
                                            ctx.drawImage(img, 0, 0, width, height);

                                            let quality = 0.8;
                                            function checkSize() {
                                                canvas.toBlob((blob) => {
                                                    if (blob.size > 70 * 1024 && quality > 0.1) {
                                                        quality -= 0.05;
                                                        checkSize();
                                                    } else {
                                                        progress = 50;
                                                        const webpFile = new File([blob], file.name.replace(/\.[^/.]+$/, '') + '.webp', { type: 'image/webp' });

                                                        @this.upload('gambar', webpFile, (uploadedUrl) => {
                                                            isConverting = false;
                                                            progress = 100;
                                                        }, () => {
                                                            isConverting = false;
                                                            alert('Gagal mengupload gambar.');
                                                        }, (event) => {
                                                            progress = 50 + Math.round(event.detail.progress / 2);
                                                        });
                                                    }
                                                }, 'image/webp', quality);
                                            }
                                            checkSize();
                                        };
                                    };
                                " />

                            <label class="label">
                                <span class="label-text-alt text-base-content/50">Otomatis convert ke WebP (Maks
                                    70KB).</span>
                            </label>

                            {{-- Feedback Loading Convert --}}
                            <div x-show="isConverting" class="mt-2 flex flex-col gap-1">
                                <div class="flex items-center gap-2 text-sm text-base-content/60">
                                    <span class="loading loading-spinner loading-xs"></span>
                                    <span>Memproses & mengompres gambar...</span>
                                </div>
                                <progress class="progress progress-neutral w-full" :value="progress"
                                    max="100"></progress>
                            </div>

                            @error('gambar')
                                <span class="text-error text-xs mt-1">{{ $message }}</span>
                            @enderror

                            @if ($gambar)
                                <div class="mt-2 rounded-xl overflow-hidden border-2 border-base-200">
                                    <img src="{{ $gambar->temporaryUrl() }}" class="w-full h-auto object-cover"
                                        alt="Preview">
                                </div>
                            @endif

                            <div wire:loading wire:target="gambar"
                                class="mt-2 flex items-center gap-2 text-sm text-base-content/60">
                                <span class="loading loading-spinner loading-xs"></span>
                                Mengupload ke server...
                            </div>
                        </div>

                        <div class="divider my-0"></div>

                        {{-- Actions --}}
                        <div class="grid grid-cols-2 gap-2">
                            <button type="submit" class="btn btn-neutral" wire:loading.attr="disabled">
                                <span wire:loading wire:target="save" class="loading loading-spinner loading-xs"></span>
                                Simpan
                            </button>
                            <a href="{{ route('berita') }}" wire:navigate class="btn btn-ghost">
                                Batal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    {{-- ─── Quill Assets ──────────────────────────────────────── --}}
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet" data-navigate-once />

    <style>
        #quill-editor {
            height: 350px;
            background-color: oklch(var(--b1));
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        .ql-toolbar.ql-snow {
            background-color: oklch(var(--b2));
            border-color: oklch(var(--bc) / 0.2) !important;
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        .ql-container.ql-snow {
            border-color: oklch(var(--bc) / 0.2) !important;
        }
    </style>

    <script>
        (function() {
            function loadScript(src) {
                return new Promise((resolve, reject) => {
                    if (document.querySelector(`script[src="${src}"]`)) {
                        resolve();
                        return;
                    }
                    const s = document.createElement('script');
                    s.src = src;
                    s.onload = resolve;
                    s.onerror = reject;
                    document.head.appendChild(s);
                });
            }

            async function initQuill() {
                const el = document.getElementById('quill-editor');
                if (!el) return;
                
                if (el.classList.contains('ql-container')) return;

                if (typeof Quill === 'undefined') {
                    await loadScript('https://cdn.jsdelivr.net/npm/quill@2/dist/quill.js');
                }

                const quill = new Quill('#quill-editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            [{ 'header': [1, 2, false] }],
                            ['bold', 'italic', 'underline', 'strike'],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            ['link', 'image'],
                            ['clean']
                        ]
                    }
                });
                
                quill.root.classList.add('prose', 'max-w-none');
            }

            setTimeout(initQuill, 100);

            document.addEventListener('livewire:navigated', () => {
                setTimeout(initQuill, 100);
            });
        })();
    </script>
</div>
