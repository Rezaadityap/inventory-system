<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Config;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token)
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
        $expire = Config::get('auth.passwords.' . Config::get('auth.defaults.passwords') . '.expire');

        $url = route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]);

        return (new MailMessage)
            ->subject('Reset Password - ' . config('app.name'))
            ->greeting("Halo {$notifiable->name},")
            ->line('Kami menerima permintaan untuk mengatur ulang password akun kamu.')
            ->line('Klik tombol di bawah ini untuk membuat password baru.')
            ->action('Reset Password', $url)
            ->line("Tautan ini akan kedaluwarsa dalam {$expire} menit.")
            ->line('Jika kamu tidak meminta reset password, abaikan email ini dan password kamu tetap aman.')
            ->salutation('Salam, ' . config('app.name'));
    }
}
