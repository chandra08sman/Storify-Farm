<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Product $product)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $stock = $this->product->currentStock();
        $capacity = (int) $this->product->capacity;

        return (new MailMessage)
            ->subject('Peringatan stok rendah: '.$this->product->name)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Stok produk berikut sudah berada di bawah 20% kapasitas penyimpanan.')
            ->line('Produk: '.$this->product->name)
            ->line('Stok saat ini: '.$stock.' kg dari kapasitas '.$capacity.' kg')
            ->action('Buka Storify Farm', url('/'))
            ->line('Segera lakukan pengadaan atau pengecekan stok.');
    }
}