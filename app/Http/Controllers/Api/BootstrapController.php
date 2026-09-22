<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\AppNotification;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class BootstrapController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $ownerId = $user->workspaceOwnerId();

        $payload = [
            'user' => $user->toPublicArray(),
            'products' => Product::where('owner_id', $ownerId)->orderBy('name')->get()->map->toPublicArray(),
            'batches' => Batch::whereHas('product', fn ($query) => $query->where('owner_id', $ownerId))
                ->orderBy('received_at')->get(['id', 'product_id', 'number', 'quantity', 'received_at']),
            'transactions' => Transaction::whereHas('product', fn ($query) => $query->where('owner_id', $ownerId))
                ->with(['product:id,name', 'batch:id,number', 'user:id,name'])
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->get()
                ->map->toPublicArray(),
            'notifications' => $user->isAdmin()
                ? AppNotification::where('owner_id', $ownerId)
                    ->with('user:id,name')
                    ->latest()
                    ->limit(30)
                    ->get()
                    ->map->toPublicArray()
                : [],
        ];

        if ($user->isAdmin()) {
            $payload['users'] = User::where('id', $user->id)
                ->orWhere('owner_id', $user->id)
                ->orderBy('name')
                ->get()
                ->map->toPublicArray();
        }

        return response()->json($payload);
    }
}
