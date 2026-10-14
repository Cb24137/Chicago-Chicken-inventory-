<?php

namespace App\Reports;

use App\Models\InventoryItem;

// Current stock levels, value and status of every inventory item
class InventoryReport
{
    public function build(ReportFilters $filters): array
    {
        $items = InventoryItem::query()
            ->when($filters->get('category'), fn ($query, $category) => $query->where('category', $category))
            ->stockStatus($filters->get('stock_status'))
            ->orderBy('item_name')
            ->get();

        $levels = $items->map(fn (InventoryItem $item) => $item->stockLevel());
        $low = $levels->filter(fn ($level) => $level === 'low')->count();
        $out = $levels->filter(fn ($level) => $level === 'out')->count();
        $value = fn (InventoryItem $item) => round((float) $item->quantity * (float) $item->unit_price, 2);

        return [
            'title' => 'Inventory Report',
            'period' => 'Stock levels on ' . now()->format('d M Y, g:i A'),
            'uses_dates' => false,
            'summary' => [
                ['label' => 'Inventory items', 'value' => number_format($items->count()), 'meta' => 'Matching the selected filters'],
                ['label' => 'Stock value', 'value' => 'RM ' . number_format($items->sum($value), 2), 'meta' => 'Quantity × recorded unit price'],
                ['label' => 'Low stock', 'value' => number_format($low), 'meta' => 'At or below the minimum level', 'tone' => $low > 0 ? 'warning' : null],
                ['label' => 'Out of stock', 'value' => number_format($out), 'meta' => 'Nothing left', 'tone' => $out > 0 ? 'danger' : null],
            ],
            'charts' => [
                [
                    'type' => 'bars',
                    'title' => 'Items by category',
                    'items' => collect(InventoryItem::CATEGORIES)
                        ->map(fn ($category) => ['label' => $category, 'value' => $items->where('category', $category)->count()])
                        ->all(),
                    'unit' => 'item',
                    'label_heading' => 'Category',
                    'value_heading' => 'Items',
                ],
                [
                    'type' => 'bars',
                    'title' => 'Stock status',
                    'items' => [
                        ['label' => 'In stock', 'value' => $items->count() - $low - $out, 'tone' => 'good'],
                        ['label' => 'Low stock', 'value' => $low, 'tone' => 'warning'],
                        ['label' => 'Out of stock', 'value' => $out, 'tone' => 'critical'],
                    ],
                    'unit' => 'item',
                    'label_heading' => 'Stock status',
                    'value_heading' => 'Items',
                ],
            ],
            'tables' => [[
                'title' => 'Inventory items',
                'columns' => [
                    'Item' => 'text', 'Category' => 'text', 'Quantity' => 'number', 'Unit' => 'text',
                    'Minimum stock' => 'number', 'Unit price (RM)' => 'money', 'Stock value (RM)' => 'money', 'Status' => 'text',
                ],
                'rows' => $items->map(fn (InventoryItem $item) => [
                    $item->item_name,
                    $item->category,
                    (float) $item->quantity,
                    $item->unit,
                    (float) $item->minimum_stock,
                    (float) $item->unit_price,
                    $value($item),
                    InventoryItem::STOCK_STATUSES[$item->stockLevel() ?? 'in_stock'],
                ])->all(),
                'empty' => 'No inventory items match these filters.',
            ]],
            'notes' => [
                'Stock value is each item\'s quantity × the unit price recorded on the item.',
                'Low stock means at or below the minimum stock level; out of stock means a quantity of 0.',
            ],
        ];
    }
}
