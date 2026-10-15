<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewTransactionNotification extends Notification
{
    use Queueable;

    public function __construct(public Transaction $transaction)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $transaction = $this->transaction;

        return (new MailMessage)
            ->subject("Pesanan Baru - {$transaction->transaction_number}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Pelanggan telah mengajukan transaksi baru yang perlu ditinjau.")
            ->line("Nomor Transaksi: {$transaction->transaction_number}")
            ->line("Pelanggan: {$transaction->customer?->customer_name}")
            ->line("Diajukan pada: " . $transaction->requested_at?->translatedFormat('d M Y H:i'))
            ->line('Item Pesanan:')
            ->line($this->itemsTable($transaction))
            ->line("Total Harga: {$transaction->formattedPrice}")
            ->action('Lihat Detail Transaksi', route('transactions.index'))
            ->line('Terima kasih.')
            ->salutation('Salam, ' . config('app.name'));
    }

    private function itemsTable(Transaction $transaction): HtmlString
    {
        $rows = '';

        foreach ($transaction->items as $item) {
            $wasteName = $item->waste?->code ? "{$item->waste->code} - {$item->waste?->waste_name}" : $item->waste?->waste_name;
            $itemPrice = 'Rp. ' . number_format($item->price, 0, ',', '.');

            $rows .= '<tr>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;">' . e($wasteName) . '</td>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;text-align:center;">' . e($item->qty . ' ' . $item->waste?->unit) . '</td>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;text-align:right;">' . e($itemPrice) . '</td>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;">' . e($item->note_item ?: '-') . '</td>'
                . '</tr>';
        }

        $table = '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:13px;margin:8px 0 16px;">'
            . '<thead><tr style="background:#f3f4f6;">'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:left;">Limbah</th>'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:center;">Qty</th>'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:right;">Harga</th>'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:left;">Catatan</th>'
            . '</tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';

        return new HtmlString($table);
    }

    public function toArray(object $notifiable): array
    {
        $transaction = $this->transaction;

        return [
            'title' => 'Pesanan Baru',
            'message' => "Transaksi {$transaction->transaction_number} dari {$transaction->customer?->customer_name} menunggu persetujuan.",
            'url' => route('transactions.index'),
            'transaction_id' => $transaction->id,
        ];
    }
}
