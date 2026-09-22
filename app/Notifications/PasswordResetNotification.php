<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $resetBy)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password akun Storify Farm direset')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Password akun Anda telah direset oleh '.$this->resetBy.'.')
            ->line('Jika Anda tidak meminta perubahan ini, segera hubungi admin workspace.');
    }
}