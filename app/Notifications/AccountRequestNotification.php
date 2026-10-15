<?php

namespace App\Notifications;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public User $applicant, public Customer $customer)
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
        $applicant = $this->applicant;
        $customer = $this->customer;

        return (new MailMessage)
            ->subject('Permintaan Akun Baru - ' . $customer->customer_name)
            ->greeting("Halo {$notifiable->name},")
            ->line('Ada permintaan akun baru yang menunggu persetujuan.')
            ->line("Role: {$applicant->role?->name}")
            ->line("Nama Customer: {$customer->customer_name}")
            ->line("Nama PIC: {$applicant->name}")
            ->line("Username: {$applicant->username}")
            ->line("Email: {$applicant->email}")
            ->line("No. HP: {$customer->phone_number}")
            ->line("Alamat: {$customer->address}")
            ->action('Tinjau di Halaman Pengguna', route('user.index'))
            ->line('Akun belum aktif sampai kamu mengaktifkannya.')
            ->salutation('Salam, ' . config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan Akun Baru',
            'message' => "{$this->customer->customer_name} ({$this->applicant->email}) mengajukan akun pelanggan dan menunggu persetujuan.",
            'url' => route('user.index'),
            'user_id' => $this->applicant->id,
        ];
    }
}
