<?php

namespace App\Services;

use App\Models\CreditNote;
use App\Models\Invoice;
use App\Models\Retur;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    public const TAX_RATE = 0.11;

    public function __construct(protected MidtransService $midtrans) {}

    /**
     * Buat invoice untuk sebuah transaksi. Belum ada Virtual Account di tahap ini
     * -- VA baru dibuat saat pelanggan memilih bank dan menekan bayar.
     *
     * Idempotent: transaksi yang sudah punya invoice tidak dibuat ulang.
     */
    public function generateFromTransaction(Transaction $transaction): Invoice
    {
        if ($transaction->invoice) {
            return $transaction->invoice;
        }

        $transaction->loadMissing('items');

        // `price` pada TransactionItem sudah total per baris (unit_price * qty).
        $subtotal = (int) $transaction->items->sum('price');
        $discount = 0;
        $tax = (int) round(($subtotal - $discount) * self::TAX_RATE);
        $total = $subtotal + $tax - $discount;

        return DB::transaction(fn () => Invoice::create([
            'transaction_id' => $transaction->id,
            'customer_id'    => $transaction->customer_id,
            'invoice_number' => $this->makeInvoiceNumber($transaction),
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'tax'            => $tax,
            'total'          => $total,
            'status'         => 'unpaid',
            'due_date'       => now()->addDays(7),
        ]));
    }

    /**
     * Buka sesi pembayaran Snap untuk invoice.
     *
     * Idempotent: satu invoice hanya punya satu sesi. `order_id` Midtrans tidak boleh
     * dipakai ulang, jadi token yang sudah ada dikembalikan apa adanya -- pelanggan
     * yang menutup popup bisa membukanya lagi dengan token yang sama.
     */
    public function startPayment(Invoice $invoice): Invoice
    {
        if (! $invoice->isPayable()) {
            return $invoice;
        }

        if ($invoice->snap_token) {
            return $invoice;
        }

        $invoice->loadMissing('customer');

        $session = $this->midtrans->createPaymentSession(
            // invoice_number sudah unik, jadi dipakai apa adanya sebagai order_id.
            orderId: $invoice->invoice_number,
            amount: (int) $invoice->total,
            customerName: $invoice->customer?->customer_name ?? 'Customer',
            // Sesi berlaku sampai akhir hari jatuh tempo -- bukan default Midtrans (24 jam).
            expiresAt: $invoice->due_date->copy()->endOfDay(),
        );

        $invoice->update([
            'snap_token' => $session['token'],
            'expired_at' => $session['expires_at'],
        ]);

        return $invoice->fresh();
    }

    /**
     * Tandai invoice lunas. Idempotent: invoice yang sudah tidak "unpaid" dibiarkan.
     */
    public function markAsPaid(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'unpaid') {
            return $invoice;
        }

        $invoice->update([
            'status'  => 'paid',
            'paid_at' => now(),
        ]);

        return $invoice->fresh();
    }

    /**
     * Samakan status invoice dengan status sebenarnya di Midtrans.
     *
     * Notifikasi bisa hilang (URL notifikasi belum terdaftar, tunnel mati, Midtrans gagal
     * kirim), jadi status di Midtrans yang dipakai sebagai sumber kebenaran saat kita
     * perlu memastikan.
     */
    public function syncPaymentStatus(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'unpaid' || ! $invoice->snap_token) {
            return $invoice;
        }

        if (! $this->midtrans->isTransactionPaid($invoice->invoice_number)) {
            return $invoice;
        }

        return $this->markAsPaid($invoice);
    }

    /**
     * Tandai invoice hangus karena lewat jatuh tempo. Idempotent.
     */
    public function markAsExpired(Invoice $invoice): Invoice
    {
        if ($invoice->status !== 'unpaid') {
            return $invoice;
        }

        $invoice->update(['status' => 'expired']);

        return $invoice->fresh();
    }

    public function createCreditNoteFromRetur(Retur $retur): CreditNote
    {
        $retur->loadMissing('items', 'transaction.invoice');

        $subtotal = (int) $retur->items->sum('price');
        $tax = (int) round($subtotal * self::TAX_RATE);
        $invoice = $retur->transaction?->invoice;

        return DB::transaction(function () use ($retur, $subtotal, $tax, $invoice) {
            $creditNote = CreditNote::create([
                'retur_id' => $retur->id,
                'invoice_id' => $invoice?->id,
                'customer_id' => $retur->customer_id,
                'credit_note_number' => $this->makeCreditNoteNumber($retur),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $subtotal + $tax,
                'status' => 'open',
                'created_by' => Auth::id(),
            ]);

            if ($this->canApplyToInvoice($invoice)) {
                $this->applyToInvoice($creditNote, $invoice);
            }

            return $creditNote->fresh();
        });
    }

    public function markCreditNoteRefunded(CreditNote $creditNote, ?string $note = null, ?string $proofPath = null): CreditNote
    {
        if ($creditNote->status !== 'open') {
            return $creditNote;
        }

        $creditNote->update([
            'status' => 'refunded',
            'refunded_at' => now(),
            'refund_proof_path' => $proofPath,
            'note' => $note ?: $creditNote->note,
        ]);

        return $creditNote->fresh();
    }

    public function markCreditNoteReplaced(CreditNote $creditNote, string $transactionNumber, ?string $note = null): CreditNote
    {
        if ($creditNote->status !== 'open') {
            return $creditNote;
        }

        $creditNote->update([
            'status' => 'replaced',
            'replaced_at' => now(),
            'note' => $note ?: "Diganti dengan pengiriman ulang barang lewat transaksi {$transactionNumber}.",
        ]);

        return $creditNote->fresh();
    }

    private function canApplyToInvoice(?Invoice $invoice): bool
    {
        return $invoice
            && $invoice->status === 'unpaid'
            && ! $invoice->snap_token;
    }

    private function applyToInvoice(CreditNote $creditNote, Invoice $invoice): void
    {
        $discount = (int) $invoice->discount;
        $subtotal = max(0, (int) $invoice->subtotal - (int) $creditNote->subtotal);
        $tax = (int) round(max(0, $subtotal - $discount) * self::TAX_RATE);
        $total = max(0, $subtotal + $tax - $discount);

        $invoice->update([
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'status' => $total === 0 ? 'cancelled' : $invoice->status,
        ]);

        $creditNote->update([
            'status' => 'applied',
            'applied_at' => now(),
            'note' => "Dipotong langsung dari invoice {$invoice->invoice_number}.",
        ]);
    }

    private function makeCreditNoteNumber(Retur $retur): string
    {
        return 'CN-' . now()->format('Ymd') . '-' . str_pad((string) $retur->id, 4, '0', STR_PAD_LEFT);
    }

    private function makeInvoiceNumber(Transaction $transaction): string
    {
        return 'INV-' . now()->format('Ymd') . '-' . str_pad((string) $transaction->id, 4, '0', STR_PAD_LEFT);
    }
}
