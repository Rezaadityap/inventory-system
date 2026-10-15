<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class SyncInvoicePayments extends Command
{
    protected $signature = 'invoices:sync-payments {invoice? : Nomor invoice tertentu, mis. INV-20260904-0006}';

    protected $description = 'Cek ke Midtrans apakah invoice yang belum lunas ternyata sudah dibayar, lalu samakan statusnya.';

    public function handle(InvoiceService $invoiceService): int
    {
        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            ->whereNotNull('snap_token')
            ->when($this->argument('invoice'), fn($q, $number) => $q->where('invoice_number', $number))
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Tidak ada invoice yang perlu dicek.');

            return self::SUCCESS;
        }

        $paid = 0;

        foreach ($invoices as $invoice) {
            $synced = $invoiceService->syncPaymentStatus($invoice);

            if ($synced->status === 'paid') {
                $paid++;
                $this->line("  <fg=green>LUNAS</>  {$invoice->invoice_number}");
            } else {
                $this->line("  <fg=gray>belum</>  {$invoice->invoice_number}");
            }
        }

        $this->newLine();
        $this->info("Selesai. {$invoices->count()} invoice dicek, {$paid} ditandai lunas.");

        return self::SUCCESS;
    }
}
