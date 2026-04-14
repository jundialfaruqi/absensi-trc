<div>
    <dialog id="logout_modal_profile" class="modal modal-bottom sm:modal-middle backdrop-blur-sm">
        <div class="modal-box rounded-t-2xl rounded-bl-none sm:rounded-2xl">
            <h3 class="font-bold text-lg uppercase tracking-tight">Konfirmasi Logout</h3>
            <p class="py-4 text-base-content/70">Apakah Anda yakin ingin keluar dari aplikasi?</p>
            <div class="modal-action">
                <form method="dialog">
                    <button class="btn btn-ghost">Batal</button>
                </form>
                <button type="button" wire:click="logout" onclick="logout_modal_profile.close()"
                    class="btn btn-error text-white uppercase">
                    Logout
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    </dialog>
</div>
