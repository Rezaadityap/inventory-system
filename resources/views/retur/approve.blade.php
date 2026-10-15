<div x-show="approveModal" x-cloak x-transition.opacity @keydown.escape.window="approveModal = false"
    class="modal-overlay">
    <div x-show="approveModal" x-transition @click.outside="approveModal = false" class="modal-panel">
        <form :action="`/returs/${approveData.id}/approve`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-success">
                    <i data-lucide="check" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Setujui Retur?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau menyetujui retur <span class="font-medium text-text-primary"
                            x-text="approveData.retur_number"></span>? Stock akan otomatis dikembalikan dan nota
                        kredit diterbitkan - dipotong langsung dari invoice kalau belum tersentuh pembayaran, kalau
                        sudah maka menunggu pengembalian dana.
                    </p>
                    <p class="text-sm text-text-secondary mt-1">
                        Pastikan foto bukti setiap item sudah diperiksa di detail retur.
                    </p>
                </div>

                <div class="w-full text-left">
                    <label class="form-label" for="approve-admin-note">Keterangan <span
                            class="text-text-secondary font-normal">(opsional)</span></label>
                    <textarea id="approve-admin-note" name="admin_note" rows="3" maxlength="1000" class="form-input"
                        x-model="approveData.admin_note" placeholder="Tulis keterangan persetujuan..."></textarea>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="approveModal = false">Batal</button>
                <button type="submit" class="btn bg-success text-white hover:bg-success/90">
                    Ya, Setujui
                </button>
            </div>
        </form>
    </div>
</div>
