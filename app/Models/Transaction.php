<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'type',
        'product_id',
        'batch_id',
        'quantity',
        'location',
        'user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'product_id' => $this->product_id,
            'product_name' => $this->product?->name,
            'batch_id' => $this->batch_id,
            'batch_number' => $this->batch?->number,
            'quantity' => $this->quantity,
            'location' => $this->location,
            'user_id' => $this->user_id,
            'user_name' => $this->user?->name,
            'notes' => $this->notes,
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
