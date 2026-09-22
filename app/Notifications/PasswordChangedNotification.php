<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password akun Storify Farm diubah')
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Password akun Storify Farm Anda baru saja diubah.')
            ->line('Jika perubahan ini bukan dilakukan oleh Anda, segera hubungi admin workspace.');
    }
}