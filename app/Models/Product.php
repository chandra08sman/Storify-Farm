<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'sku',
        'location',
        'capacity',
        'temperature',
        'humidity',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function batches()
    {
        return $this->hasMany(Batch::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function currentStock(): int
    {
        return (int) $this->batches()->sum('quantity');
    }

    public function capacityStatus(): array
    {
        $stock = $this->currentStock();
        $capacity = (int) $this->capacity;
        $usedPct = $capacity ? min(100, ($stock / $capacity) * 100) : 0;
        $remaining = $capacity ? max(0, 100 - $usedPct) : 100;

        if ($capacity && $stock >= $capacity) {
            return ['label' => 'Penuh', 'pct' => $usedPct, 'remaining' => 0];
        }
        if ($remaining <= 20) {
            return ['label' => 'Hampir Penuh', 'pct' => $usedPct, 'remaining' => $remaining];
        }

        return ['label' => 'Normal', 'pct' => $usedPct, 'remaining' => $remaining];
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'location' => $this->location,
            'capacity' => $this->capacity,
            'temperature' => $this->temperature,
            'humidity' => $this->humidity,
            'stock' => $this->currentStock(),
            'capacity_status' => $this->capacityStatus(),
        ];
    }
}
