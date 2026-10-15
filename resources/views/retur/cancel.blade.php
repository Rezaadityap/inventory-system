<div x-show="cancelModal" x-cloak x-transition.opacity @keydown.escape.window="cancelModal = false" class="modal-overlay">
    <div x-show="cancelModal" x-transition @click.outside="cancelModal = false" class="modal-panel">
        <form :action="`/returs/${cancelData.id}/cancel`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-danger">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Batalkan Retur?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau membatalkan retur <span class="font-medium text-text-primary"
                            x-text="cancelData.retur_number"></span>? Retur yang sudah dibatalkan tidak bisa
                        dikembalikan.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="cancelModal = false">Kembali</button>
                <button type="submit" class="btn bg-danger text-white hover:bg-danger/90">
                    Ya, Batalkan
                </button>
            </div>
        </form>
    </div>
</div>
