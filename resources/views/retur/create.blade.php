<div x-show="createModal" x-cloak x-transition.opacity @keydown.escape.window="createModal = false" class="modal-overlay">
    <div x-show="createModal" x-transition @click.outside="createModal = false" class="modal-panel"
        style="max-width: 56rem;" x-data="{
            transactionsData: @js($eligibleTransactions->map(fn($t) => [
                'id' => $t->id,
                'transaction_number' => $t->transaction_number,
                'items' => $t->items->map(fn($i) => [
                    'id' => $i->id,
                    'waste_name' => $i->waste->waste_name,
                    'unit' => $i->waste->unit,
                    'qty' => $i->qty,
                ]),
            ])),
            selectedTransactionId: '',
            items: [],
            onTransactionChange() {
                const t = this.transactionsData.find(t => t.id == this.selectedTransactionId);
                this.items = t ? t.items.map(i => ({ transaction_item_id: i.id, waste_name: i.waste_name, unit: i.unit, max_qty: i.qty, included: false, qty: '', note_item: '', preview: '' })) : [];
            },
        }" x-init="$nextTick(() => window.refreshIcons?.());
        $watch('items', () => $nextTick(() => window.refreshIcons?.()))">
        <form action="{{ route('returs.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-header">
                <h2>Ajukan Retur</h2>
                <span class="modal-close" @click="createModal = false">&times;</span>
            </div>

            <div class="modal-body space-y-4">
                <div>
                    <label for="retur_number" class="form-label">Nomor Retur</label>
                    <input type="text" id="retur_number" class="form-input" value="{{ $nextReturNumber }}" disabled>
                </div>

                <div>
                    <label for="transaction_id" class="form-label required">Transaksi</label>
                    <select name="transaction_id" id="transaction_id" class="form-input" x-model="selectedTransactionId"
                        @change="onTransactionChange()" required>
                        <option value="">Pilih Transaksi</option>
                        @foreach ($eligibleTransactions as $transaction)
                            <option value="{{ $transaction->id }}">{{ $transaction->transaction_number }} -
                                {{ optional($transaction->for_date)->translatedFormat('d F Y') }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('transaction_id')" class="mt-1" />
                    <p class="text-xs text-text-secondary mt-1" x-show="transactionsData.length === 0">
                        Belum ada transaksi selesai yang bisa diretur.
                    </p>
                </div>

                <div>
                    <label for="note" class="form-label">Catatan</label>
                    <textarea name="note" id="note" cols="8" rows="3" class="form-input" placeholder="Masukkan Catatan">{{ old('note') }}</textarea>
                    <x-input-error :messages="$errors->get('note')" class="mt-1" />
                </div>

                <div x-show="items.length > 0">
                    <label class="form-label required">Item yang Diretur</label>

                    <div class="space-y-3">
                        <template x-for="(item, index) in items" :key="item.transaction_item_id">
                            <div class="flex flex-col gap-2 p-3 border border-gray-200 rounded-lg">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start">
                                    <label class="flex items-center gap-2 sm:w-48 shrink-0">
                                        <input type="checkbox" x-model="item.included"
                                            @change="if (!item.included) item.preview = ''">
                                        <span class="text-sm"
                                            x-text="`${item.waste_name} (maks ${item.max_qty} ${item.unit})`"></span>
                                    </label>

                                    <input type="number" :name="`qty[${index}]`" class="form-input sm:w-28"
                                        x-model="item.qty" :max="item.max_qty" min="1" placeholder="Qty"
                                        :disabled="!item.included" :required="item.included">

                                    <input type="text" :name="`note_item[${index}]`" class="form-input"
                                        x-model="item.note_item" placeholder="Catatan item (opsional)"
                                        :disabled="!item.included">

                                    <input type="hidden" :name="`transaction_item_id[${index}]`"
                                        :value="item.transaction_item_id" :disabled="!item.included">
                                </div>

                                <div x-show="item.included" x-cloak>
                                    <label class="form-label required text-xs">Foto Bukti Kondisi Limbah</label>
                                    <input type="file" :name="`photo[${index}]`" accept="image/*"
                                        class="form-input cursor-pointer" :disabled="!item.included"
                                        :required="item.included"
                                        @change="item.preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : ''">
                                    <p class="text-xs text-text-secondary mt-1">Format JPG, PNG, atau WEBP. Maksimal 2
                                        MB.</p>

                                    <template x-if="item.preview">
                                        <img :src="item.preview"
                                            class="mt-2 w-20 h-20 object-cover rounded-md border border-gray-200">
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <x-input-error :messages="$errors->get('qty')" class="mt-1" />
                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn" @click="createModal = false">Batal</button>
                <button type="submit" class="btn btn-primary" :disabled="!items.some(i => i.included)">Ajukan</button>
            </div>
        </form>
    </div>
</div>
