<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel">
        <div class="modal-header">
            <h2>Detail Retur</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Nomor Retur</label>
                    <p class="text-sm text-text-primary" x-text="viewData.retur_number"></p>
                </div>

                <div>
                    <label class="form-label">Nomor Transaksi</label>
                    <p class="text-sm text-text-primary" x-text="viewData.transaction_number"></p>
                </div>

                <div>
                    <label class="form-label">Dari</label>
                    <p class="text-sm text-text-primary" x-text="viewData.customer_name"></p>
                </div>

                <div>
                    <label class="form-label">Status</label>
                    <p class="text-sm text-text-primary" x-text="viewData.status"></p>
                </div>

                <div>
                    <label class="form-label">Catatan</label>
                    <p class="text-sm text-text-primary whitespace-pre-line" x-text="viewData.note || '-'"></p>
                </div>

                <div>
                    <label class="form-label">Tanggal Diajukan</label>
                    <p class="text-sm text-text-primary" x-text="viewData.requested_at || '-'"></p>
                </div>

                <div x-show="!['Ditolak', 'Dibatalkan'].includes(viewData.status)">
                    <label class="form-label">Tanggal Disetujui</label>
                    <p class="text-sm text-text-primary" x-text="viewData.approved_at || '-'"></p>
                </div>

                <div x-show="viewData.status === 'Ditolak'">
                    <label class="form-label">Tanggal Ditolak</label>
                    <p class="text-sm text-text-primary" x-text="viewData.rejected_at || '-'"></p>
                </div>

                <div x-show="viewData.status === 'Dibatalkan'">
                    <label class="form-label">Tanggal Dibatalkan</label>
                    <p class="text-sm text-text-primary" x-text="viewData.cancelled_at || '-'"></p>
                </div>

                <div x-show="viewData.admin_note">
                    <label class="form-label"
                        x-text="viewData.status === 'Ditolak' ? 'Alasan Penolakan' : 'Keterangan Admin'"></label>
                    <p class="text-sm text-text-primary whitespace-pre-line" x-text="viewData.admin_note"></p>
                </div>
            </div>

            <div>
                <label class="form-label">Item</label>
                <div class="table-wrapper mt-1">
                    <table class="table-base">
                        <thead class="table-head">
                            <tr>
                                <th>Limbah</th>
                                <th>Qty</th>
                                <th>Nilai</th>
                                <th>Catatan</th>
                                <th>Bukti</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="item in viewData.items" :key="item.waste_name + item.qty">
                                <tr class="table-row">
                                    <td x-text="item.waste_name"></td>
                                    <td x-text="`${item.qty} ${item.unit}`"></td>
                                    <td x-text="item.price"></td>
                                    <td x-text="item.note_item || '-'"></td>
                                    <td>
                                        <template x-if="item.photo_url">
                                            <a :href="item.photo_url" target="_blank" rel="noopener">
                                                <img :src="item.photo_url"
                                                    class="w-12 h-12 object-cover rounded-md border border-gray-200 hover:opacity-80">
                                            </a>
                                        </template>
                                        <template x-if="!item.photo_url">
                                            <span class="text-text-secondary">-</span>
                                        </template>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div x-show="viewData.credit_note" x-cloak>
                <label class="form-label">Nota Kredit</label>
                <div class="mt-1 rounded-lg border border-gray-200 p-3 space-y-1.5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-medium text-text-primary" x-text="viewData.credit_note?.number"></span>
                        <span class="table-badge"
                            :class="viewData.credit_note?.status === 'open' ? 'table-badge-warning' : 'table-badge-success'"
                            x-text="viewData.credit_note?.status_label"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-text-secondary">
                        <span>Subtotal</span>
                        <span x-text="viewData.credit_note?.subtotal"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-text-secondary">
                        <span>PPN</span>
                        <span x-text="viewData.credit_note?.tax"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm font-semibold text-text-primary border-t border-gray-200 pt-1.5">
                        <span>Total</span>
                        <span x-text="viewData.credit_note?.total"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-text-secondary"
                        x-show="viewData.credit_note?.replacement_number">
                        <span>Transaksi Pengganti</span>
                        <span class="font-medium text-text-primary"
                            x-text="`${viewData.credit_note?.replacement_number} (${viewData.credit_note?.replacement_status})`"></span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-text-secondary"
                        x-show="viewData.credit_note?.refund_proof_url">
                        <span>Bukti Transfer</span>
                        <a :href="viewData.credit_note?.refund_proof_url" target="_blank" rel="noopener">
                            <img :src="viewData.credit_note?.refund_proof_url"
                                class="w-12 h-12 object-cover rounded-md border border-gray-200 hover:opacity-80">
                        </a>
                    </div>
                    <p class="text-xs text-text-secondary" x-show="viewData.credit_note?.note"
                        x-text="viewData.credit_note?.note"></p>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
            @role('pelanggan')
                <button type="button" class="btn bg-danger text-white hover:bg-danger/90"
                    x-show="viewData.status === 'Menunggu'"
                    @click="viewModal = false; cancelModal = true; cancelData = {
                        id: viewData.id,
                        retur_number: viewData.retur_number,
                    }">
                    <i data-lucide="x" class="w-4 h-4 shrink-0"></i>
                    Batalkan
                </button>
            @endrole
            @role('admin')
                <button type="button" class="btn bg-success text-white hover:bg-success/90"
                    x-show="viewData.status === 'Disetujui' && viewData.credit_note?.status === 'open'" x-cloak
                    @click="viewModal = false; settleModal = true; settleData = {
                        id: viewData.id,
                        retur_number: viewData.retur_number,
                        credit_note_number: viewData.credit_note?.number,
                        total: viewData.credit_note?.total,
                        method: 'refund',
                        note: '',
                    }">
                    <i data-lucide="hand-coins" class="w-4 h-4 shrink-0"></i>
                    Selesaikan Nota Kredit
                </button>
                <button type="button" class="btn bg-danger text-white hover:bg-danger/90"
                    x-show="viewData.status === 'Menunggu'"
                    @click="viewModal = false; rejectModal = true; rejectData = {
                        id: viewData.id,
                        retur_number: viewData.retur_number,
                        admin_note: '',
                    }">
                    <i data-lucide="x" class="w-4 h-4 shrink-0"></i>
                    Reject
                </button>
                <button type="button" class="btn bg-success text-white hover:bg-success/90"
                    x-show="viewData.status === 'Menunggu'"
                    @click="viewModal = false; approveModal = true; approveData = {
                        id: viewData.id,
                        retur_number: viewData.retur_number,
                        admin_note: '',
                    }">
                    <i data-lucide="check" class="w-4 h-4 shrink-0"></i>
                    Approve
                </button>
            @endrole
        </div>
    </div>
</div>
