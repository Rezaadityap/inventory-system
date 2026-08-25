<div x-show="createModal" x-cloak x-transition-opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel" x-data="{ items: [{ waste_id: '', qty: '', note_item: '' }] }"
        x-init="$nextTick(() => window.refreshIcons?.());
        $watch('items', () => $nextTick(() => window.refreshIcons?.()))">
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h2>Buat Transaksi</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="transaction_number" class="form-label">Nomor Transaksi</label>
                    <input type="text" id="transaction_number" class="form-input"
                        value="{{ $nextTransactionNumber }}" disabled>
                </div>

                <div>
                    <label for="note" class="form-label">Catatan</label>
                    <textarea name="note" id="note" cols="8" rows="3" class="form-input" placeholder="Masukkan Catatan">{{ old('note') }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-1" />
                </div>

                <div>
                    <label class="form-label required">Item Limbah</label>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="index">
                            <div
                                class="flex flex-col gap-2 p-3 border border-gray-200 rounded-lg sm:flex-row sm:items-start">
                                <select :name="`waste_id[${index}]`" class="form-input py-2.2" x-model="item.waste_id"
                                    required>
                                    <option value="">Pilih Limbah</option>
                                    @foreach ($wastes as $waste)
                                        <option value="{{ $waste->id }}">{{ $waste->code }} -
                                            {{ $waste->waste_name }}</option>
                                    @endforeach
                                </select>

                                <input type="number" :name="`qty[${index}]`" class="form-input sm:w-28"
                                    x-model="item.qty" min="1" placeholder="Qty" required>

                                <input type="text" :name="`note_item[${index}]`" class="form-input"
                                    x-model="item.note_item" placeholder="Catatan item (opsional)">

                                <button type="button"
                                    class="shrink-0 flex items-center justify-center w-10 h-10 rounded-lg text-danger hover:bg-danger/10 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
                                    :disabled="items.length === 1" @click="items.splice(index, 1)">
                                    <i data-lucide="trash" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </template>
                    </div>

                    <button type="button"
                        class="inline-flex items-center gap-1 mt-2 text-sm text-primary hover:underline cursor-pointer"
                        @click="items.push({ waste_id: '', qty: '', note_item: '' })">
                        <i data-lucide="plus" class="w-4 h-4 shrink-0"></i>
                        Tambah Item
                    </button>

                    <x-input-error :messages="$errors->get('waste_id')" class="mt-1" />
                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>
