<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/drivers/${editData.id}`" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit Pengemudi</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label class="form-label required">Nama Pengemudi</label>
                    <input type="text" name="name" class="form-input" x-model="editData.name" required>
                </div>

                <div>
                    <label class="form-label required">Username</label>
                    <input type="text" name="username" class="form-input" x-model="editData.username" required>
                </div>

                <div>
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-input" x-model="editData.email" required>
                </div>

                <div>
                    <label class="form-label required">No. Hp</label>
                    <input type="text" inputmode="numeric" name="phone_number" class="form-input" maxlength="13"
                        x-model="editData.phone_number" required>
                </div>

                <div>
                    <label class="form-label required">SIM</label>
                    <input type="text" inputmode="numeric" name="driver_license" class="form-input" maxlength="16"
                        x-model="editData.driver_license" required>
                </div>

                <div>
                    <label class="form-label required">Status</label>
                    <select name="available_status" class="form-input" x-model="editData.available_status" required>
                        <option value="Tersedia">Tersedia</option>
                        <option value="Sibuk">Sibuk</option>
                        <option value="Libur">Libur</option>
                    </select>
                </div>

                <div>
                    <label class="form-label">Foto (kosongkan jika tidak diganti)</label>
                    <input type="file" name="photo" class="form-input cursor-pointer" accept="image/*">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
