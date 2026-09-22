<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Product;
use Illuminate\Database\Seeder;

class BatchSeeder extends Seeder
{
    public function run(): void
    {
        Product::all()->each(function (Product $product) {
            if ($product->batches()->exists()) {
                return;
            }

            $qty = (int) min($product->capacity * 0.4, 50000);
            $receivedAt = now()->subDays($product->id);

            Batch::create([
                'product_id' => $product->id,
                'number' => 'BRS'.$receivedAt->format('Ymd').$product->id,
                'quantity' => $qty,
                'received_at' => $receivedAt->toDateString(),
            ]);
        });
    }
}
