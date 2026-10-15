<div x-show="shipModal" x-cloak x-transition.opacity @keydown.escape.window="shipModal = false" class="modal-overlay">
    <div x-show="shipModal" x-transition @click.outside="shipModal = false" class="modal-panel">
        <form :action="`/shipments/${shipData.id}/ship`" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="shipment_ref" :value="shipData.id">
            <input type="hidden" name="shipment_ref_number" :value="shipData.shipment_number">

            <div class="modal-header">
                <h2>Tandai Dikirim</h2>
                <span class="modal-close" @click="shipModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <p class="text-sm text-text-secondary">
                    Pengiriman <span class="font-medium text-text-primary" x-text="shipData.shipment_number"></span>
                    akan ditandai <span class="font-medium">Dikirim</span> dan dalam perjalanan ke pelanggan.
                </p>

                <div x-show="shipData.planned_shipped_at && shipData.planned_shipped_at !== '{{ now()->format('Y-m-d') }}'"
                    x-cloak class="rounded-md bg-warning/10 border border-warning/30 p-3 text-sm text-warning">
                    Rencana kirim untuk pengiriman ini adalah tanggal
                    <span class="font-medium" x-text="shipData.planned_shipped_at_label"></span>, bukan hari ini.
                    Estimasi tiba akan diperbarui mengikuti tanggal pengiriman aktual (hari ini). Lanjutkan?
                </div>

                <div>
                    <label for="ship_photo" class="form-label required">Foto Bukti Pengiriman</label>
                    <input type="file" name="photo" id="ship_photo" accept="image/*"
                        class="form-input cursor-pointer" required>
                    <p class="text-xs text-text-secondary mt-1">Format JPG, PNG, atau WEBP. Maksimal 2 MB.</p>
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="shipModal = false">Batal</button>
                <button type="submit" class="btn bg-success text-white hover:bg-success/90">
                    Ya, Kirim
                </button>
            </div>
        </form>
    </div>
</div>
