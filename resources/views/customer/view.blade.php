<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel">
        <div class="modal-header">
            <h2>Detail Customer</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-4">
            <div class="flex justify-center">
                <img x-show="viewData.company_photo" :src="viewData.company_photo" alt="Foto Perusahaan"
                    class="w-32 h-32 rounded-lg object-cover border border-gray-200">
                <div x-show="!viewData.company_photo"
                    class="w-32 h-32 rounded-lg border border-gray-200 flex items-center justify-center bg-gray-50 text-text-secondary">
                    <i data-lucide="image" class="w-8 h-8"></i>
                </div>
            </div>

            <div>
                <label class="form-label">Nama Customer</label>
                <p class="text-sm text-text-primary" x-text="viewData.customer_name"></p>
            </div>

            <div>
                <label class="form-label">Email</label>
                <p class="text-sm text-text-primary" x-text="viewData.email || '-'"></p>
            </div>

            <div>
                <label class="form-label">No. Hp</label>
                <p class="text-sm text-text-primary" x-text="viewData.phone_number"></p>
            </div>

            <div>
                <label class="form-label">Alamat Perusahaan</label>
                <p class="text-sm text-text-primary whitespace-pre-line" x-text="viewData.address"></p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
        </div>
    </div>
</div>
