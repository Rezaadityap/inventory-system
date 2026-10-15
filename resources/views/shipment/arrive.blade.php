<div x-show="arriveModal" x-cloak x-transition.opacity @keydown.escape.window="arriveModal = false" class="modal-overlay">
    <div x-show="arriveModal" x-transition @click.outside="arriveModal = false" class="modal-panel">
        <form :action="`/shipments/${arriveData.id}/arrive`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-success">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Tandai Sampai di Lokasi?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Pengiriman <span class="font-medium text-text-primary" x-text="arriveData.shipment_number"></span>
                        akan ditandai <span class="font-medium">Sampai</span> di lokasi pelanggan.
                    </p>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="arriveModal = false">Batal</button>
                <button type="submit" class="btn bg-success text-white hover:bg-success/90">
                    Ya, Sampai
                </button>
            </div>
        </form>
    </div>
</div>
