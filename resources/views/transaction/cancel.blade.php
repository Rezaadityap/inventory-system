<div x-show="cancelModal" x-cloak x-transition.opacity @keydown.escape.window="cancelModal = false" class="modal-overlay">
    <div x-show="cancelModal" x-transition @click.outside="cancelModal = false" class="modal-panel">
        <form :action="`/transactions/${cancelData.id}/cancel`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-danger">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Batalkan Transaksi?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau membatalkan transaksi <span class="font-medium text-text-primary"
                            x-text="cancelData.transaction_number"></span>? Transaksi yang sudah dibatalkan tidak bisa
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
