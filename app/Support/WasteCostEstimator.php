<?php

namespace App\Support;

use App\Models\InventoryItem;
use App\Models\WasteExpiryRecord;
use Illuminate\Support\Collection;

// Waste records have no cost of their own. This estimates it from the
// inventory item with the same name, at that item's current unit price,
// when the units can be converted. Other records are left unpriced.
class WasteCostEstimator
{
    // Waste unit => [inventory unit, multiplier]
    private const UNITS = [
        'kg' => ['kg', 1],
        'g' => ['kg', 0.001],
        'litre' => ['litre', 1],
        'ml' => ['litre', 0.001],
        'pcs' => ['pcs', 1],
    ];

    private Collection $items;

    public function __construct()
    {
        $this->items = InventoryItem::get(['item_name', 'unit', 'unit_price'])
            ->keyBy(fn (InventoryItem $item) => mb_strtolower(trim($item->item_name)));
    }

    // Estimated cost in RM, or null when no matching price exists
    public function estimate(WasteExpiryRecord $record): ?float
    {
        $item = $this->items->get(mb_strtolower(trim($record->item_name)));
        [$unit, $factor] = self::UNITS[$record->unit] ?? [null, null];

        if (! $item || $item->unit !== $unit) {
            return null;
        }

        return round((float) $record->quantity * $factor * (float) $item->unit_price, 2);
    }

    // ['cost' => total RM, 'priced' => records with a price, 'count' => all records]
    public function summarise(iterable $records): array
    {
        $cost = 0.0;
        $priced = 0;
        $count = 0;

        foreach ($records as $record) {
            $count++;
            $estimate = $this->estimate($record);

            if ($estimate !== null) {
                $cost += $estimate;
                $priced++;
            }
        }

        return ['cost' => round($cost, 2), 'priced' => $priced, 'count' => $count];
    }
}
