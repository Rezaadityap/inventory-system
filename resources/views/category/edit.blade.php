<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/categories/${editData.id}`" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit Kategori</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label class="form-label required">Nama Kategori</label>
                    <input type="text" name="category_name" class="form-input" x-model="editData.category_name"
                        required>
                </div>

                <div>
                    <label class="form-label required">Deskripsi</label>
                    <textarea name="description" class="form-input" id="description" cols="8" rows="5"
                        x-model="editData.description"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
