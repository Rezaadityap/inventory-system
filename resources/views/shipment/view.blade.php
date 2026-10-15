<div x-show="viewModal" x-cloak x-transition.opacity @keydown.escape.window="viewModal = false" class="modal-overlay">
    <div x-show="viewModal" x-transition @click.outside="viewModal = false" class="modal-panel" x-data="{
        detail: {},
        loading: false,
    }"
        x-effect="if (viewModal && viewData.id) {
            loading = true;
            fetch(`/shipments/${viewData.id}`, { headers: { Accept: 'application/json' } })
                .then(r => r.json())
                .then(d => {
                    detail = d;
                    loading = false;
                    $nextTick(() => window.refreshIcons && window.refreshIcons());
                })
                .catch(() => loading = false);
        }">
        <div class="modal-header">
            <h2>Detail Pengiriman</h2>
            <span class="modal-close" @click="viewModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-5">
            <p x-show="loading" class="text-sm text-text-secondary">Memuat...</p>

            <div x-show="!loading" class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Nomor Resi</label>
                        <p class="text-sm text-text-primary" x-text="detail.shipment_number || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Nomor Transaksi</label>
                        <p class="text-sm text-text-primary" x-text="detail.transaction_number || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Pelanggan</label>
                        <p class="text-sm text-text-primary" x-text="detail.customer_name || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Status</label>
                        <p class="text-sm text-text-primary" x-text="detail.status || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Pengemudi</label>
                        <p class="text-sm text-text-primary" x-text="detail.driver_name || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Plat Nomor</label>
                        <p class="text-sm text-text-primary" x-text="detail.vehicle_number || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Rencana Dikirim</label>
                        <p class="text-sm text-text-primary" x-text="detail.planned_shipped_at || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Dikirim Oleh</label>
                        <p class="text-sm text-text-primary" x-text="detail.shipped_by || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Waktu Dikirim</label>
                        <p class="text-sm text-text-primary" x-text="detail.shipped_at || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Estimasi Tiba</label>
                        <p class="text-sm text-text-primary" x-text="detail.estimated_arrival || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Waktu Tiba</label>
                        <p class="text-sm text-text-primary" x-text="detail.arrived_at || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Diterima Oleh</label>
                        <p class="text-sm text-text-primary" x-text="detail.received_by || '-'"></p>
                    </div>
                    <div>
                        <label class="form-label">Waktu Diterima</label>
                        <p class="text-sm text-text-primary" x-text="detail.received_at || '-'"></p>
                    </div>
                </div>

                @role('pelanggan')
                    <div>
                        <label class="form-label">Lacak Paket</label>
                        <ol class="mt-3">
                            <template x-for="(step, index) in (detail.timeline || [])" :key="index">
                                <li class="flex gap-3">
                                    <div class="flex flex-col items-center">
                                        <span class="mt-0.5 w-3 h-3 rounded-full border-2 shrink-0"
                                            :class="step.done ? 'bg-primary border-primary' : 'bg-white border-gray-300'"></span>
                                        <span class="w-px flex-1 my-1" :class="step.done ? 'bg-primary' : 'bg-gray-200'"
                                            x-show="index < (detail.timeline.length - 1)"></span>
                                    </div>
                                    <div class="flex-1 pb-5">
                                        <p class="text-sm font-medium"
                                            :class="step.done ? 'text-text-primary' : 'text-text-secondary'"
                                            x-text="step.label"></p>
                                        <p class="text-xs text-text-secondary mt-0.5" x-text="step.description"></p>
                                        <p class="text-xs mt-1"
                                            :class="step.done ? 'text-text-primary' : 'text-text-secondary'">
                                            <span x-text="step.at || 'Menunggu'"></span>
                                            <template x-if="step.by && step.done">
                                                <span x-text="` • ${step.by}`"></span>
                                            </template>
                                        </p>
                                        <template x-if="step.proof">
                                            <a :href="step.proof" target="_blank" rel="noopener"
                                                class="inline-flex items-center gap-1 text-xs text-primary hover:underline mt-1">
                                                <i data-lucide="image" class="w-3.5 h-3.5"></i>
                                                Bukti Pengiriman
                                            </a>
                                        </template>
                                    </div>
                                </li>
                            </template>
                        </ol>
                    </div>

                    <div>
                        <label class="form-label">Item Dikirim</label>
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
                                    <template x-for="(item, index) in (detail.items || [])" :key="index">
                                        <tr class="table-row">
                                            <td x-text="item.code ? `${item.code} - ${item.waste_name}` : item.waste_name">
                                            </td>
                                            <td x-text="`${item.qty} ${item.unit || ''}`"></td>
                                            <td x-text="item.note_item || '-'"></td>
                                        </tr>
                                    </template>
                                    <tr x-show="!(detail.items || []).length">
                                        <td colspan="3" class="px-4 py-4 text-center text-text-secondary">
                                            Tidak ada item.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endrole
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="viewModal = false">Tutup</button>
        </div>
    </div>
</div>
