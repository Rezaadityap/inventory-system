<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel"
        x-data="{
            rupiah(n) { return 'Rp. ' + Number(n || 0).toLocaleString('id-ID'); },
        }">
        <div class="modal-header">
            <h2>Detail Invoice</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nomor Invoice</label>
                    <p class="text-sm text-text-primary" x-text="viewData.invoice_number"></p>
                </div>

                <div>
                    <label class="form-label">Nomor Transaksi</label>
                    <p class="text-sm text-text-primary" x-text="viewData.transaction_number || '-'"></p>
                </div>

                <div>
                    <label class="form-label">Customer</label>
                    <p class="text-sm text-text-primary" x-text="viewData.customer_name || '-'"></p>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <p class="text-sm text-text-primary" x-text="viewData.status"></p>
                </div>

                <div>
                    <label class="form-label">Jatuh Tempo</label>
                    <p class="text-sm text-text-primary" x-text="viewData.due_date || '-'"></p>
                </div>

                <div>
                    <label class="form-label">Dibayar Pada</label>
                    <p class="text-sm text-text-primary" x-text="viewData.paid_at || '-'"></p>
                </div>

            </div>

            <div>
                <label class="form-label">Item Transaksi</label>
                <div class="table-wrapper mt-1">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th>Limbah</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in viewData.items" :key="item.waste_name + item.qty">
                                <tr class="table-row">
                                    <td x-text="item.waste_name"></td>
                                    <td x-text="`${item.qty} ${item.unit}`"></td>
                                    <td x-text="rupiah(item.price)"></td>
                                    <td x-text="item.note_item || '-'"></td>
                                </tr>
                            </template>
                            <template x-if="!viewData.items || viewData.items.length === 0">
                                <tr>
                                    <td colspan="4" class="text-center text-text-secondary">Tidak ada item.</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="space-y-1 pt-3 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-text-secondary">
                    <span>Subtotal</span>
                    <span x-text="rupiah(viewData.subtotal)"></span>
                </div>
                <div class="flex items-center justify-between text-sm text-text-secondary" x-show="viewData.discount">
                    <span>Diskon</span>
                    <span x-text="'- ' + rupiah(viewData.discount)"></span>
                </div>
                <div class="flex items-center justify-between text-sm text-text-secondary">
                    <span>Pajak (PPN)</span>
                    <span x-text="rupiah(viewData.tax)"></span>
                </div>
                <div class="flex items-center justify-between font-semibold pt-1">
                    <span>Total Harga</span>
                    <span x-text="rupiah(viewData.total)"></span>
                </div>
            </div>

            <div class="pt-3 border-t border-gray-200" x-show="viewData.credit_notes?.length" x-cloak>
                <label class="form-label">Nota Kredit</label>
                <div class="space-y-1 mt-1">
                    <template x-for="cn in viewData.credit_notes" :key="cn.number">
                        <div class="flex items-center justify-between gap-2 text-sm">
                            <div class="min-w-0">
                                <span class="text-text-primary" x-text="cn.number"></span>
                                <span class="text-text-secondary" x-text="` · retur ${cn.retur_number ?? '-'}`"></span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-text-primary" x-text="'- ' + cn.total"></span>
                                <span class="table-badge"
                                    :class="cn.status === 'open' ? 'table-badge-warning' : 'table-badge-success'"
                                    x-text="cn.status_label"></span>
                            </div>
                        </div>
                    </template>
                </div>
                <p class="text-xs text-text-secondary mt-2">
                    Nota kredit berstatus "Dipotong dari Invoice" sudah tercermin pada total di atas.
                </p>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
        </div>
    </div>
</div>
