<div x-show="logoutModal" x-cloak x-transition.opacity @keydown.escape.window="logoutModal = false" class="modal-overlay">
    <div x-show="logoutModal" x-transition @click.outside="logoutModal = false" class="modal-panel">
        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-warning">
                    <i data-lucide="log-out" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Keluar dari akun?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu akan keluar dari sesi saat ini dan perlu login kembali.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="logoutModal = false">Batal</button>
                <button type="submit" class="btn bg-danger text-white hover:bg-danger/90">
                    Ya, Keluar
                </button>
            </div>
        </form>
    </div>
</div>
