<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    public const CATEGORIES = ['Food', 'Beverage', 'Packaging', 'Other'];

    // Stock status filter value => label
    public const STOCK_STATUSES = [
        'in_stock' => 'In stock',
        'restock' => 'Low or out of stock',
        'low' => 'Low stock',
        'out' => 'Out of stock',
    ];

    protected $fillable = [
        'item_name',
        'category',
        'unit',
        'quantity',
        'minimum_stock',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    // Purchase orders (Module 2) placed for this item
    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    // Items at or below their minimum stock level (includes out of stock)
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('quantity', '<=', 'minimum_stock');
    }

    // Filter by one of the STOCK_STATUSES keys
    public function scopeStockStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'restock' => $query->lowStock(),
            'out' => $query->where('quantity', '<=', 0),
            'low' => $query->lowStock()->where('quantity', '>', 0),
            'in_stock' => $query->whereColumn('quantity', '>', 'minimum_stock'),
            default => $query,
        };
    }

    // 'out' when nothing is left, 'low' at or below the minimum, otherwise null
    public function stockLevel(): ?string
    {
        if ((float) $this->quantity <= 0) {
            return 'out';
        }

        return (float) $this->quantity <= (float) $this->minimum_stock ? 'low' : null;
    }
}
