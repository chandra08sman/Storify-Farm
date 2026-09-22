<?php

namespace App\Notifications;

use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CapacityNearlyFullNotification extends Notification implements ShouldQueue
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
        $remaining = max(0, $capacity - $stock);

        return (new MailMessage)
            ->subject('Peringatan kapasitas hampir penuh: '.$this->product->name)
            ->greeting('Halo '.$notifiable->name.',')
            ->line('Kapasitas penyimpanan produk berikut tersisa 20% atau kurang.')
            ->line('Produk: '.$this->product->name)
            ->line('Stok: '.$stock.' kg dari kapasitas '.$capacity.' kg')
            ->line('Sisa kapasitas: '.$remaining.' kg')
            ->action('Buka Storify Farm', url('/'))
            ->line('Periksa kapasitas gudang sebelum menerima barang berikutnya.');
    }
}