<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/customers/${editData.id}`" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit Customer</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label class="form-label required">Nama Customer</label>
                    <input type="text" name="customer_name" class="form-input" x-model="editData.customer_name"
                        required>
                </div>

                <div>
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" x-model="editData.email">
                </div>

                <div>
                    <label class="form-label required">No. Hp</label>
                    <input type="number" name="phone_number" class="form-input" x-model="editData.phone_number"
                        required>
                </div>

                <div>
                    <label class="form-label">Foto Perusahaan (kosongkan jika tidak diganti)</label>
                    <input type="file" name="company_photo" class="form-input" accept="image/*">
                </div>

                <div>
                    <label class="form-label required">Alamat Perusahaan</label>
                    <textarea name="address" id="address" cols="8" rows="3" class="form-input" x-model="editData.address"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
