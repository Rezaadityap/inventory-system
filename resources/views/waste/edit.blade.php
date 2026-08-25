<div x-show="editModal" x-cloak x-transition.opacity @keydown.escape.window="editModal = false" class="modal-overlay">
    <div x-show="editModal" x-transition @click.outside="editModal = false" class="modal-panel">
        <form :action="`/wastes/${editData.id}`" method="POST">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h2>Edit Limbah</h2>
                <span class="modal-close" @click="editModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label class="form-label required">Kode Limbah</label>
                    <input type="text" name="code" class="form-input" x-model="editData.code" required>
                </div>
                <div>
                    <label class="form-label required">Nama Limbah</label>
                    <input type="text" name="waste_name" class="form-input" x-model="editData.waste_name" required>
                </div>
                <div>
                    <label class="form-label required">Satuan</label>
                    <select name="unit" class="form-input" x-model="editData.unit" required>
                        <option value="">Pilih Satuan</option>
                        <option value="Gram">Gram</option>
                        <option value="Miligram">Miligram</option>
                        <option value="Kilogram">Kilogram</option>
                    </select>
                </div>
                <div>
                    <label class="form-label required">Kategori</label>
                    <select name="category_id" class="form-input" x-model="editData.category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item->id }}">{{ $item->category_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="editModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
