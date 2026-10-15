<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Console\Command;

class ExpireOverdueInvoices extends Command
{
    protected $signature = 'invoices:expire-overdue';

    protected $description = 'Tandai invoice yang lewat jatuh tempo dan belum dibayar sebagai kedaluwarsa.';

    public function handle(InvoiceService $invoiceService): int
    {
        $invoices = Invoice::query()
            ->where('status', 'unpaid')
            // Jatuh tempo dihitung sampai akhir harinya, jadi yang jatuh tempo hari
            // ini baru hangus besok.
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        if ($invoices->isEmpty()) {
            $this->info('Tidak ada invoice yang lewat jatuh tempo.');

            return self::SUCCESS;
        }

        foreach ($invoices as $invoice) {
            $invoiceService->markAsExpired($invoice);
            $this->line("  <fg=yellow>HANGUS</>  {$invoice->invoice_number} (jatuh tempo {$invoice->due_date->format('d M Y')})");
        }

        $this->newLine();
        $this->info("Selesai. {$invoices->count()} invoice ditandai kedaluwarsa.");

        return self::SUCCESS;
    }
}
