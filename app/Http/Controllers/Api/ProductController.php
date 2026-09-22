<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        $nextNumber = (int) (Product::max('id') ?? 0) + 1;
        $sku = 'SF-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
        while (Product::withTrashed()->where('sku', $sku)->exists()) {
            $nextNumber++;
            $sku = 'SF-'.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
        }

        $product = Product::create([
            ...$data,
            'owner_id' => $request->user()->workspaceOwnerId(),
            'sku' => $sku,
            'temperature' => $data['temperature'] ?? '20–25°C',
            'humidity' => $data['humidity'] ?? '60–70%',
        ]);

        return response()->json([
            'ok' => true,
            'message' => 'Produk berhasil ditambahkan (SKU '.$sku.')',
            'product' => $product->toPublicArray(),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        abort_unless($product->owner_id === $request->user()->workspaceOwnerId(), 403);

        $data = $request->validated();

        $product->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Produk berhasil diperbarui.',
            'product' => $product->fresh()->toPublicArray(),
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        abort_unless($product->owner_id === $request->user()->workspaceOwnerId(), 403);

        $product->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Produk diarsipkan. Riwayat transaksinya tetap tersimpan.',
        ]);
    }
}
