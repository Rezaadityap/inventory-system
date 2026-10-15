<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private MidtransService $midtrans,
    ) {}

    public function handle(Request $request)
    {
        $payload = $request->all();

        if (! $this->midtrans->isValidSignature($payload)) {
            Log::warning('Midtrans webhook ditolak: signature tidak cocok', [
                'order_id' => $payload['order_id'] ?? null,
            ]);

            abort(403, 'Invalid signature');
        }

        Log::info('Midtrans webhook received', $payload);

        $invoice = $this->resolveInvoice($payload);

        // Signature sudah terbukti sah, jadi `transaction_status` di payload bisa
        // dipercaya langsung -- tidak perlu konfirmasi ulang ke Midtrans.
        if ($invoice && $this->midtrans->isPaid($payload)) {
            $this->invoiceService->markAsPaid($invoice);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Cari invoice yang dimaksud notifikasi. `order_id` yang kita kirim ke Midtrans
     * saat charge adalah `invoice_number`, jadi cukup dicocokkan langsung.
     */
    private function resolveInvoice(array $payload): ?Invoice
    {
        $orderId = $payload['order_id'] ?? null;

        return $orderId ? Invoice::where('invoice_number', $orderId)->first() : null;
    }
}
