<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h2>Tambah Kategori</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="name" class="form-label required">Nama Kategori</label>
                    <input type="text" name="category_name" id="category_name" class="form-input"
                        value="{{ old('category_name') }}" placeholder="Masukan Kategori" required>
                    <x-input-error :messages="$errors->get('category_name')" class="mt-1" />
                </div>

                <div>
                    <label for="name" class="form-label required">Deskripsi</label>
                    <textarea name="description" id="description" cols="8" rows="5" class="form-input"
                        placeholder="Masukkan Deskripsi">{{ old('description') }}</textarea>
                    <x-input-error :messages="$errors->get('description')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
