<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel">
        <div class="modal-header">
            <h2>Detail Riwayat Stock</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="form-label">Kode Limbah</label>
                <p class="text-sm text-text-primary" x-text="viewData.code"></p>
            </div>

            <div>
                <label class="form-label">Nama Limbah</label>
                <p class="text-sm text-text-primary" x-text="viewData.waste_name"></p>
            </div>

            <div>
                <label class="form-label">Tipe</label>
                <p class="text-sm text-text-primary" x-text="viewData.type"></p>
            </div>

            <div>
                <label class="form-label">Sumber</label>
                <p class="text-sm text-text-primary" x-text="viewData.source"></p>
            </div>

            <div>
                <label class="form-label">Qty</label>
                <p class="text-sm text-text-primary" x-text="viewData.qty"></p>
            </div>

            <div>
                <label class="form-label">Nama Pengguna</label>
                <p class="text-sm text-text-primary" x-text="viewData.name"></p>
            </div>

            <div>
                <label class="form-label">Tanggal</label>
                <p class="text-sm text-text-primary" x-text="viewData.date"></p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
        </div>
    </div>
</div>
