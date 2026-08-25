<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel">
        <div class="modal-header">
            <h2>Detail Transaksi</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nomor Transaksi</label>
                    <p class="text-sm text-text-primary" x-text="viewData.transaction_number"></p>
                </div>

                <div>
                    <label class="form-label">Customer</label>
                    <p class="text-sm text-text-primary" x-text="viewData.customer_name"></p>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <p class="text-sm text-text-primary" x-text="viewData.status"></p>
                </div>

                <div>
                    <label class="form-label">Tanggal Diminta</label>
                    <p class="text-sm text-text-primary" x-text="viewData.requested_at || '-'"></p>
                </div>
            </div>

            <div>
                <label class="form-label">Catatan</label>
                <p class="text-sm text-text-primary whitespace-pre-line" x-text="viewData.note || '-'"></p>
            </div>

            <div>
                <label class="form-label">Item</label>
                <div class="table-wrapper mt-1">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th>Limbah</th>
                                <th>Qty</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in viewData.items" :key="item.waste_name + item.qty">
                                <tr class="table-row">
                                    <td x-text="item.waste_name"></td>
                                    <td x-text="`${item.qty} ${item.unit}`"></td>
                                    <td x-text="item.note_item || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
            @role('admin')
                <button type="button" class="btn bg-success text-white hover:bg-success/90"
                    x-show="viewData.status === 'Pending'"
                    @click="viewModal = false; approveModal = true; approveData = {
                        id: viewData.id,
                        transaction_number: viewData.transaction_number,
                    }">
                    <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                    Approve
                </button>
            @endrole
        </div>
    </div>
</div>
