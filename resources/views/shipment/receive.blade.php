<div x-show="receiveModal" x-cloak x-transition.opacity @keydown.escape.window="receiveModal = false" class="modal-overlay">
    <div x-show="receiveModal" x-transition @click.outside="receiveModal = false" class="modal-panel">
        <form :action="`/shipments/${receiveData.id}/receive`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-success">
                    <i data-lucide="circle-check" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Konfirmasi Terima Paket?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Pengiriman <span class="font-medium text-text-primary" x-text="receiveData.shipment_number"></span>
                        akan ditandai <span class="font-medium">Diterima</span> dan transaksi menjadi
                        <span class="font-medium">Selesai</span>.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="receiveModal = false">Batal</button>
                <button type="submit" class="btn bg-success text-white hover:bg-success/90">
                    Ya, Terima
                </button>
            </div>
        </form>
    </div>
</div>
