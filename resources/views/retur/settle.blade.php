<div x-show="settleModal" x-cloak x-transition.opacity @keydown.escape.window="settleModal = false" class="modal-overlay">
    <div x-show="settleModal" x-transition @click.outside="settleModal = false" class="modal-panel">
        <form :action="`/returs/${settleData.id}/settle`" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="settle_ref[id]" :value="settleData.id">
            <input type="hidden" name="settle_ref[retur_number]" :value="settleData.retur_number">
            <input type="hidden" name="settle_ref[credit_note_number]" :value="settleData.credit_note_number">
            <input type="hidden" name="settle_ref[total]" :value="settleData.total">

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-success">
                    <i data-lucide="hand-coins" class="w-6 h-6" x-show="settleData.method !== 'replace'"></i>
                    <i data-lucide="truck" class="w-6 h-6" x-show="settleData.method === 'replace'"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Selesaikan Nota Kredit</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Nota kredit <span class="font-medium text-text-primary"
                            x-text="settleData.credit_note_number"></span> sebesar
                        <span class="font-medium text-text-primary" x-text="settleData.total"></span>
                        untuk retur <span class="font-medium text-text-primary" x-text="settleData.retur_number"></span>.
                    </p>
                </div>

                <div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-2 text-left">
                    <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer"
                        :class="settleData.method === 'refund' ? 'border-success bg-success/5' : 'border-gray-200'">
                        <input type="radio" name="method" value="refund" class="mt-1" x-model="settleData.method">
                        <span>
                            <span class="block text-sm font-medium text-text-primary">Kembalikan Dana</span>
                            <span class="block text-xs text-text-secondary">Dana sudah ditransfer ke pelanggan.</span>
                        </span>
                    </label>
                    <label class="flex items-start gap-2 rounded-lg border p-3 cursor-pointer"
                        :class="settleData.method === 'replace' ? 'border-success bg-success/5' : 'border-gray-200'">
                        <input type="radio" name="method" value="replace" class="mt-1" x-model="settleData.method">
                        <span>
                            <span class="block text-sm font-medium text-text-primary">Kirim Ulang Barang</span>
                            <span class="block text-xs text-text-secondary">Buat transaksi pengganti sesuai item & qty retur.</span>
                        </span>
                    </label>
                </div>

                <p class="w-full text-left text-xs text-text-secondary" x-show="settleData.method === 'replace'">
                    Transaksi pengganti langsung berstatus Disetujui dan muncul di menu Pengiriman. Stok berkurang saat
                    pengiriman dibuat, dan tidak ada invoice baru yang diterbitkan.
                </p>

                <div class="w-full text-left" x-show="settleData.method === 'refund'">
                    <label class="form-label required" for="refund-proof">Bukti Transfer</label>
                    <input type="file" id="refund-proof" name="refund_proof" accept="image/jpeg,image/png,image/webp"
                        class="form-input" :required="settleData.method === 'refund'" :disabled="settleData.method !== 'refund'">
                    <p class="text-xs text-text-secondary mt-1">Screenshot bukti transfer. JPG, PNG, atau WEBP, maksimal 2 MB.</p>
                    <x-input-error :messages="$errors->get('refund_proof')" class="mt-1" />
                </div>

                <div class="w-full text-left">
                    <label class="form-label" for="settle-note">Keterangan <span
                            class="text-text-secondary font-normal">(opsional)</span></label>
                    <textarea id="settle-note" name="note" rows="3" maxlength="1000" class="form-input"
                        x-model="settleData.note"
                        :placeholder="settleData.method === 'replace' ? 'Contoh: kirim bersama jadwal minggu depan' : 'Contoh: transfer BCA 19 September 2026'"></textarea>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="settleModal = false">Batal</button>
                <button type="submit" class="btn bg-success text-white hover:bg-success/90"
                    x-text="settleData.method === 'replace' ? 'Ya, Kirim Ulang' : 'Ya, Sudah Dikembalikan'">
                </button>
            </div>
        </form>
    </div>
</div>
