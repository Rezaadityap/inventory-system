<?php

namespace App\Notifications;

use App\Models\Retur;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class NewReturNotification extends Notification
{
    use Queueable;

    public function __construct(public Retur $retur)
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
        $retur = $this->retur;

        return (new MailMessage)
            ->subject("Retur Baru - {$retur->retur_number}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Pelanggan telah mengajukan retur yang perlu ditinjau.")
            ->line("Nomor Retur: {$retur->retur_number}")
            ->line("Nomor Transaksi: {$retur->transaction?->transaction_number}")
            ->line("Pelanggan: {$retur->customer?->customer_name}")
            ->line("Diajukan pada: " . $retur->requested_at?->translatedFormat('d M Y H:i'))
            ->line('Item Retur:')
            ->line($this->itemsTable($retur))
            ->action('Lihat Detail Retur', route('returs.index'))
            ->line('Terima kasih.')
            ->salutation('Salam, ' . config('app.name'));
    }

    private function itemsTable(Retur $retur): HtmlString
    {
        $rows = '';

        foreach ($retur->items as $item) {
            $wasteName = $item->waste?->code ? "{$item->waste->code} - {$item->waste?->waste_name}" : $item->waste?->waste_name;

            $rows .= '<tr>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;">' . e($wasteName) . '</td>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;text-align:center;">' . e($item->qty . ' ' . $item->waste?->unit) . '</td>'
                . '<td style="padding:8px 12px;border:1px solid #e5e7eb;">' . e($item->note_item ?: '-') . '</td>'
                . '</tr>';
        }

        $table = '<table cellpadding="0" cellspacing="0" style="width:100%;border-collapse:collapse;font-size:13px;margin:8px 0 16px;">'
            . '<thead><tr style="background:#f3f4f6;">'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:left;">Limbah</th>'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:center;">Qty</th>'
            . '<th style="padding:8px 12px;border:1px solid #e5e7eb;text-align:left;">Catatan</th>'
            . '</tr></thead>'
            . '<tbody>' . $rows . '</tbody>'
            . '</table>';

        return new HtmlString($table);
    }

    public function toArray(object $notifiable): array
    {
        $retur = $this->retur;

        return [
            'title' => 'Retur Baru',
            'message' => "Retur {$retur->retur_number} dari {$retur->customer?->customer_name} menunggu persetujuan.",
            'url' => route('returs.index'),
            'retur_id' => $retur->id,
        ];
    }
}
