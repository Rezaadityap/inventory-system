<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel">
        <form action="{{ route('stocks.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h2>Tambah Stock</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="waste_id" class="form-label required">Limbah</label>
                    <select name="waste_id" id="waste_id" class="form-input" required>
                        <option value="">Pilih Limbah</option>
                        @foreach ($wastes as $waste)
                            <option value="{{ $waste->id }}"
                                {{ (string) old('waste_id') === (string) $waste->id ? 'selected' : '' }}>
                                {{ $waste->code }} - {{ $waste->waste_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('waste_id')" class="mt-1" />
                </div>
                <div>
                    <label for="type" class="form-label required">Tipe</label>
                    <select name="type" id="type" class="form-input" required>
                        <option value="">Pilih Tipe</option>
                        <option value="Masuk">Masuk</option>
                        <option value="Keluar">Keluar</option>
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>
                <div>
                    <label for="source" class="form-label required">Sumber</label>
                    <select name="source" id="source" class="form-input" required>
                        <option value="">Pilih Sumber</option>
                        <option value="Order" @selected(old('source') === 'Order')>Order</option>
                        <option value="Retur" @selected(old('source') === 'Retur')>Retur</option>
                        <option value="Manual" @selected(old('source', 'Manual') === 'Manual')>Manual</option>
                    </select>
                    <x-input-error :messages="$errors->get('source')" class="mt-1" />
                </div>
                <div>
                    <label for="qty" class="form-label required">Qty</label>
                    <input type="number" name="qty" id="qty" class="form-input" min="0"
                        value="{{ old('qty') }}" placeholder="Masukan Qty" required>
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
                <div>
                    <label for="remark" class="form-label">Keterangan</label>
                    <textarea name="remark" id="remark" cols="8" rows="3" class="form-input"
                        placeholder="Masukkan Keterangan">{{ old('remark') }}</textarea>
                    <x-input-error :messages="$errors->get('remark')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
