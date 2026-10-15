<?php

namespace App\Notifications;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ShipmentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(public Shipment $shipment) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $shipment = $this->shipment;

        return (new MailMessage)
            ->subject("Paket Diterima - {$shipment->shipment_number}")
            ->greeting("Halo {$notifiable->name},")
            ->line("Pelanggan telah mengonfirmasi penerimaan paket untuk pengiriman {$shipment->shipment_number}.")
            ->line("Nomor Transaksi: {$shipment->transaction?->transaction_number}")
            ->line("Pelanggan: {$shipment->transaction?->customer?->customer_name}")
            ->line("Diterima pada: " . $shipment->received_at?->translatedFormat('d M Y H:i'))
            ->action('Lihat Detail Pengiriman', route('shipments.index'))
            ->line('Terima kasih.')
            ->salutation('Salam, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        $shipment = $this->shipment;

        return [
            'title' => 'Paket Diterima',
            'message' => "Pengiriman {$shipment->shipment_number} telah dikonfirmasi diterima oleh pelanggan.",
            'url' => route('shipments.index'),
            'shipment_id' => $shipment->id,
        ];
    }
}
