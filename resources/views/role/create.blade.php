<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('roles.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h2>Tambah Hak Akses</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="name" class="form-label required">Nama Hak Akses</label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}"
                        placeholder="Masukan Nama Hak Akses" required>
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
