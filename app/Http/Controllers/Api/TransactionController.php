<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInboundTransactionRequest;
use App\Http\Requests\StoreOutboundTransactionRequest;
use App\Models\Batch;
use App\Models\Product;
use App\Models\Transaction;
use App\Notifications\CapacityNearlyFullNotification;
use App\Notifications\LowStockNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function storeIn(StoreInboundTransactionRequest $request)
    {
        $data = $request->validated();

        $product = Product::where('owner_id', $request->user()->workspaceOwnerId())
            ->findOrFail($data['product_id']);
        $currentStock = $product->currentStock();

        if ($product->capacity && $currentStock >= $product->capacity) {
            return response()->json([
                'ok' => false,
                'message' => 'Kapasitas penyimpanan penuh. Tidak tersedia ruang penyimpanan yang tersisa.',
            ], 422);
        }

        if ($product->capacity && ($currentStock + $data['quantity']) > $product->capacity) {
            $sisa = max(0, $product->capacity - $currentStock);

            return response()->json([
                'ok' => false,
                'message' => 'Kapasitas tidak mencukupi. Sisa ruang hanya '.number_format($sisa, 0, ',', '.').' kg.',
            ], 422);
        }

        $transaction = DB::transaction(function () use ($request, $product, $data) {
            $batch = Batch::create([
                'product_id' => $product->id,
                'number' => $data['batch'],
                'quantity' => $data['quantity'],
                'received_at' => $data['received_at'],
                'user_id' => $request->user()->id,
            ]);

            return Transaction::create([
                'type' => 'in',
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'quantity' => $data['quantity'],
                'location' => $product->location,
                'user_id' => $request->user()->id,
                'notes' => null,
            ]);
        });

        $this->notifyIfLowStock($product, $request);
        $this->notifyIfCapacityNearlyFull($product, $currentStock, $request);

        return response()->json([
            'ok' => true,
            'message' => 'Barang masuk berhasil dicatat.',
            'transaction' => $transaction->load(['product', 'batch', 'user'])->toPublicArray(),
            'product' => $product->fresh()->toPublicArray(),
        ], 201);
    }

    public function storeOut(StoreOutboundTransactionRequest $request)
    {
        $data = $request->validated();

        $product = Product::where('owner_id', $request->user()->workspaceOwnerId())
            ->findOrFail($data['product_id']);
        $remaining = $data['quantity'];

        $pool = $product->batches()
            ->where('quantity', '>', 0)
            ->orderBy('received_at')
            ->orderBy('id')
            ->get();

        if ($pool->sum('quantity') < $remaining) {
            return response()->json(['ok' => false, 'message' => 'Stok tidak mencukupi.'], 422);
        }

        $created = DB::transaction(function () use (&$remaining, $pool, $product, $request) {
            $created = [];
            foreach ($pool as $batch) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, $batch->quantity);
                $batch->decrement('quantity', $take);

                $created[] = Transaction::create([
                    'type' => 'out',
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => $take,
                    'location' => $product->location,
                    'user_id' => $request->user()->id,
                    'notes' => 'FIFO otomatis',
                ]);

                $remaining -= $take;
            }

            return $created;
        });

        $this->notifyIfLowStock($product, $request);

        return response()->json([
            'ok' => true,
            'message' => 'Barang keluar berhasil dicatat dengan FIFO.',
            'transactions' => collect($created)->map->toPublicArray(),
            'product' => $product->fresh()->toPublicArray(),
        ], 201);
    }

    private function notifyIfLowStock(Product $product, Request $request): void
    {
        $product = $product->fresh();
        $capacity = (int) $product->capacity;

        if (!$capacity || $product->currentStock() >= $capacity * 0.2) {
            return;
        }

        $owner = $request->user()->workspaceOwnerId() === $request->user()->id
            ? $request->user()
            : $request->user()->newQuery()->find($product->owner_id);

        if ($owner?->email) {
            $owner->notify(new LowStockNotification($product));
        }
    }

    private function notifyIfCapacityNearlyFull(Product $product, int $previousStock, Request $request): void
    {
        $product = $product->fresh();
        $capacity = (int) $product->capacity;

        if (!$capacity || $previousStock >= $capacity * 0.8 || $product->currentStock() < $capacity * 0.8) {
            return;
        }

        $owner = $request->user()->workspaceOwnerId() === $request->user()->id
            ? $request->user()
            : $request->user()->newQuery()->find($product->owner_id);

        if ($owner?->email) {
            $owner->notify(new CapacityNearlyFullNotification($product));
        }
    }
}
