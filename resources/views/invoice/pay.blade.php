@php
    $snapJsUrl = config('services.midtrans.is_production')
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp

<script src="{{ $snapJsUrl }}" data-client-key="{{ config('services.midtrans.client_key') }}"></script>

<div x-show="payModal" x-cloak x-transition.opacity @keydown.escape.window="payModal = false" class="modal-overlay">
    <div x-show="payModal" x-transition @click.outside="payModal = false" class="modal-panel" x-data="{
        loading: false,
        error: null,
        rupiah(n) { return 'Rp. ' + Number(n || 0).toLocaleString('id-ID'); },
    
        /** Minta token sesi ke server. Mengembalikan token, atau null kalau gagal. */
        async requestToken() {
            if (this.loading) return null;
            this.loading = true;
            this.error = null;
    
            try {
                const res = await fetch(`{{ url('/invoices') }}/${this.payData.id}/pay`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                });
    
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal membuka pembayaran.');
    
                if (!window.snap) throw new Error('Pembayaran gagal dimuat. Periksa koneksi lalu coba lagi.');
    
                return data.invoice.snap_token;
            } catch (err) {
                this.error = err.message;
                return null;
            } finally {
                this.loading = false;
            }
        },
    
        openSnap(token) {
            // Status invoice tidak diubah di sini -- yang menentukan lunas adalah
            // notifikasi Midtrans ke webhook. Tabel cukup di-refresh supaya
            // perubahan yang sudah masuk ikut terlihat.
            const refresh = () => Livewire.dispatch('invoice-updated');
    
            window.snap.pay(token, {
                onSuccess: refresh,
                onPending: refresh,
                onClose: refresh,
                onError: refresh,
            });
        },
    }"
        x-init="$watch('payModal', (open) => { if (open) error = null })">
        <div class="modal-header">
            <h2>Bayar Invoice</h2>
            <span class="modal-close" @click="payModal = false">&times;</span>
        </div>

        <div class="modal-body space-y-4">
            <div class="flex items-center justify-between text-sm">
                <span class="text-text-secondary">Nomor Invoice</span>
                <span class="font-medium text-text-primary" x-text="payData.invoice_number"></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-text-secondary">Total Tagihan</span>
                <span class="font-semibold text-text-primary" x-text="rupiah(payData.total)"></span>
            </div>
            <div class="flex items-center justify-between text-sm">
                <span class="text-text-secondary">Jatuh Tempo</span>
                <span class="font-medium text-text-primary" x-text="payData.due_date || '-'"></span>
            </div>

            <p class="text-xs text-danger" x-show="error" x-text="error"></p>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn" @click="payModal = false">Batal</button>
            <button type="button" class="btn btn-primary" :disabled="loading"
                @click="requestToken().then((token) => { if (token) { payModal = false; openSnap(token) } })"
                x-text="loading ? 'Memproses...' : 'Bayar Sekarang'"></button>
        </div>
    </div>
</div>
