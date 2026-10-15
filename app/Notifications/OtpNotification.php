<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;

class OtpNotification extends Notification
{
    use Queueable;

    public function __construct(public string $code, public int $expiresInMinutes)
    {
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Kode OTP Login - ' . config('app.name'))
            ->greeting("Halo {$notifiable->name},")
            ->line('Gunakan kode berikut untuk menyelesaikan proses login kamu:')
            ->line(new HtmlString(
                '<p style="font-size:28px;font-weight:700;letter-spacing:8px;margin:16px 0;text-align:center;">'
                . e($this->code) . '</p>'
            ))
            ->line("Kode ini berlaku selama {$this->expiresInMinutes} menit.")
            ->line('Jika kamu tidak mencoba login, abaikan email ini dan segera ganti password kamu.')
            ->salutation('Salam, ' . config('app.name'));
    }
}
