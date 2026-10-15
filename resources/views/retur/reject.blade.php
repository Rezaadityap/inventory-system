<div x-show="rejectModal" x-cloak x-transition.opacity @keydown.escape.window="rejectModal = false" class="modal-overlay">
    <div x-show="rejectModal" x-transition @click.outside="rejectModal = false" class="modal-panel">
        <form :action="`/returs/${rejectData.id}/reject`" method="POST">
            @csrf
            @method('PATCH')

            <div class="modal-body flex flex-col items-center text-center gap-3 pt-6">
                <span class="modal-icon-danger">
                    <i data-lucide="x" class="w-6 h-6"></i>
                </span>

                <div>
                    <p class="font-display font-semibold text-text-primary">Tolak Retur?</p>
                    <p class="text-sm text-text-secondary mt-1">
                        Kamu yakin mau menolak retur <span class="font-medium text-text-primary"
                            x-text="rejectData.retur_number"></span>? Retur yang sudah ditolak tidak bisa
                        diproses lagi.
                    </p>
                </div>

                <div class="w-full text-left">
                    <label class="form-label" for="reject-admin-note">Alasan Penolakan <span
                            class="text-text-secondary font-normal">(opsional)</span></label>
                    <textarea id="reject-admin-note" name="admin_note" rows="3" maxlength="1000" class="form-input"
                        x-model="rejectData.admin_note" placeholder="Tulis alasan penolakan..."></textarea>
                </div>
            </div>

            <div class="modal-footer justify-center">
                <button type="button" class="btn" @click="rejectModal = false">Kembali</button>
                <button type="submit" class="btn bg-danger text-white hover:bg-danger/90">
                    Ya, Tolak
                </button>
            </div>
        </form>
    </div>
</div>
