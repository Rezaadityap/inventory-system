<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/shipments/${editData.id}`" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit Pengiriman</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <input type="hidden" name="transaction_id" :value="editData.transaction_id">

                <div>
                    <label class="form-label">Transaksi</label>
                    <input type="text" class="form-input bg-gray-50" x-model="editData.transaction_number" disabled>
                </div>

                <div>
                    <label class="form-label required">Pengemudi</label>
                    <select name="driver_id" class="form-input" x-model="editData.driver_id" required>
                        <option value="">Pilih Pengemudi</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}">{{ $driver->user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label required">Plat Nomor</label>
                    <input type="text" name="vehicle_number" class="form-input" maxlength="20"
                        x-model="editData.vehicle_number" required>
                </div>

                <div>
                    <label class="form-label required">Status</label>
                    <select name="status" class="form-input" x-model="editData.status" required>
                        <option value="Dikirim">Dikirim</option>
                        <option value="Sampai">Sampai</option>
                        <option value="Diterima">Diterima</option>
                    </select>
                    <p class="text-xs text-text-secondary mt-1">
                        <span class="font-medium">Diterima</span> akan menandai transaksi menjadi
                        <span class="font-medium">Selesai</span>.
                    </p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
