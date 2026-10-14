<?php

namespace App\Models;

use App\Services\AlertService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Purchase extends Model
{
    use HasFactory;

    // Purchase order statuses (stored value => label)
    public const STATUSES = [
        'pending' => 'Pending',
        'ordered' => 'Ordered',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    // Units of measurement (stored value => label)
    public const UNITS = [
        'kg' => 'Kilograms (kg)',
        'g' => 'Grams (g)',
        'litres' => 'Litres (L)',
        'ml' => 'Millilitres (mL)',
        'pieces' => 'Pieces',
        'boxes' => 'Boxes',
        'bottles' => 'Bottles',
    ];

    // Order units that can be added to each Module 1 inventory unit,
    // with the multiplier used to convert them (e.g. 500 g = 0.5 kg)
    public const INVENTORY_UNIT_FACTORS = [
        'kg' => ['kg' => 1, 'g' => 0.001],
        'litre' => ['litres' => 1, 'ml' => 0.001],
        'pcs' => ['pieces' => 1],
    ];

    protected $fillable = [
        'supplier_id',
        'inventory_item_id',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'total_cost',
        'purchase_date',
        'status',
        'notes',
        'expiry_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'stock_added_quantity' => 'decimal:2',
        'purchase_date' => 'date',
        'received_at' => 'datetime',
        'stock_added_at' => 'datetime',
        'expiry_date' => 'date',
        'expiry_alert_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Purchase $purchase) {
            // Remember when an order was marked as received
            if ($purchase->isDirty('status')) {
                $purchase->received_at = $purchase->status === 'received'
                    ? ($purchase->received_at ?? now())
                    : null;
            }

            // A new or changed expiry date gets a fresh expiry alert
            if ($purchase->isDirty('expiry_date')) {
                $purchase->expiry_alert_sent_at = null;
            }
        });

        // Give every new order a unique number, e.g. PO-2026-00012
        static::created(function (Purchase $purchase) {
            if (! $purchase->po_number) {
                $purchase->po_number = sprintf(
                    'PO-%s-%05d',
                    $purchase->created_at->format('Y'),
                    $purchase->id
                );
                $purchase->saveQuietly();
            }
        });
    }

    // Each purchase belongs to one supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Optional link to a Food & Beverage inventory item
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function scopeNotCancelled(Builder $query): Builder
    {
        return $query->where('status', '!=', 'cancelled');
    }

    // Pending or ordered: goods still to be received
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', ['pending', 'ordered']);
    }

    // Received deliveries that expired in the last 7 days or expire within
    // the alert window (config/inventory.php)
    public function scopeExpiryWatch(Builder $query): Builder
    {
        return $query->where('status', 'received')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', today()->subDays(7)->toDateString())
            ->whereDate('expiry_date', '<=', today()->addDays(config('inventory.expiry_alert_days'))->toDateString());
    }

    // Days until the delivery expires (negative once expired), or null
    public function daysUntilExpiry(): ?int
    {
        return $this->expiry_date
            ? (int) today()->diffInDays($this->expiry_date->copy()->startOfDay(), false)
            : null;
    }

    public static function calculateTotal($quantity, $unitPrice): float
    {
        return round((float) $quantity * (float) $unitPrice, 2);
    }

    // Convert an order quantity into an inventory item's unit.
    // Returns null when the two units cannot be converted.
    public static function convertToInventoryUnit(float $quantity, string $orderUnit, string $inventoryUnit): ?float
    {
        $factor = self::INVENTORY_UNIT_FACTORS[$inventoryUnit][$orderUnit] ?? null;

        return $factor === null ? null : round($quantity * $factor, 2);
    }

    // Order units that can be received into the given inventory unit
    public static function compatibleUnits(string $inventoryUnit): array
    {
        return array_keys(self::INVENTORY_UNIT_FACTORS[$inventoryUnit] ?? []);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function hasAddedStock(): bool
    {
        return $this->stock_added_at !== null;
    }

    public function canBeReceived(): bool
    {
        return in_array($this->status, ['pending', 'ordered'], true);
    }

    /**
     * Add this order's quantity to the linked inventory item.
     *
     * Runs only for received orders that are linked to an inventory item,
     * and only once per order. Returns the quantity added (in the
     * inventory item's unit), or null when no stock was added.
     */
    public function addReceivedStock(): ?float
    {
        if ($this->status !== 'received' || $this->hasAddedStock() || ! $this->inventoryItem) {
            return null;
        }

        $item = $this->inventoryItem;
        $quantity = self::convertToInventoryUnit((float) $this->quantity, $this->unit, $item->unit);

        if ($quantity === null) {
            return null;
        }

        return DB::transaction(function () use ($item, $quantity) {
            $now = now();

            // Claim the order first so it can never add stock twice,
            // even if "receive" is submitted twice at the same time.
            $claimed = static::whereKey($this->getKey())
                ->whereNull('stock_added_at')
                ->update([
                    'stock_added_at' => $now,
                    'stock_added_quantity' => $quantity,
                ]);

            if ($claimed === 0) {
                return null;
            }

            InventoryItem::whereKey($item->getKey())->increment('quantity', $quantity);

            // Restocked items get their low-stock alert re-armed
            app(AlertService::class)->checkStock($item->fresh());

            $this->forceFill([
                'stock_added_at' => $now,
                'stock_added_quantity' => $quantity,
            ])->syncOriginal();

            ActivityLog::record(
                'inventory',
                'received',
                $item,
                sprintf(
                    'Received %s %s of “%s” into stock from %s',
                    rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.'),
                    $item->unit,
                    $item->item_name,
                    $this->po_number
                )
            );

            return $quantity;
        });
    }
}
