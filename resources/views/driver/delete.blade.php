<div x-show="deleteModal" x-cloak x-transition.opacity @keydown.escape.window="deleteModal = false" class="modal-overlay">
    <div x-show="deleteModal" x-transition @click.outside="deleteModal = false" class="modal-panel">
        <form :action="`/drivers/${deleteData.id}`" method="POST">
            @csrf
            @method('DELETE')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-danger">
                    <i data-lucide="alert-triangle" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Hapus Pengemudi?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau hapus <span class="font-medium text-text-primary" x-text="deleteData.name"></span>?
                        Tindakan ini tidak bisa dibatalkan.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="deleteModal = false">Batal</button>
                <button type="submit" class="btn bg-danger text-white hover:bg-danger/90">
                    Ya, Hapus
                </button>
            </div>
        </form>
    </div>
</div>
