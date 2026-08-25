<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('wastes.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Tambah Limbah</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="code" class="form-label required">Kode Limbah</label>
                    <input type="text" name="code" id="code" class="form-input" value="{{ old('code') }}"
                        placeholder="Masukan Kode Limbah" required>
                    <x-input-error :messages="$errors->get('code')" class="mt-1" />
                </div>
                <div>
                    <label for="waste_name" class="form-label required">Nama Limbah</label>
                    <input type="text" name="waste_name" id="waste_name" class="form-input"
                        value="{{ old('waste_name') }}" placeholder="Masukan Nama Limbah" required>
                    <x-input-error :messages="$errors->get('waste_name')" class="mt-1" />
                </div>
                <div>
                    <label for="unit" class="form-label required">Satuan</label>
                    <select name="unit" id="unit" class="form-input">
                        <option value="">Pilih Satuan</option>
                        <option value="Gram">Gram</option>
                        <option value="Miligram">Miligram</option>
                        <option value="Kilogram">Kilogram</option>
                    </select>
                    <x-input-error :messages="$errors->get('unit')" class="mt-1" />
                </div>
                <div>
                    <label for="category_id" class="form-label required">Kategori</label>
                    <select name="category_id" id="category_id" class="form-input" required>
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $item)
                            <option value="{{ $item->id }}"
                                {{ (string) old('category_id') === (string) $item->id ? 'selected' : '' }}>
                                {{ $item->category_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('category_id')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
