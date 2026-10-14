<?php

namespace App\Reports;

use App\Models\Purchase;
use App\Support\MonthlyTotals;
use Illuminate\Support\Collection;

// Purchase orders, spending and the most purchased products for a period.
// Spent = received orders. Committed = pending + ordered. Cancelled orders
// are listed but never counted as spending.
class PurchaseReport
{
    public function build(ReportFilters $filters): array
    {
        $orders = Purchase::with('supplier')
            ->whereDate('purchase_date', '>=', $filters->from->toDateString())
            ->whereDate('purchase_date', '<=', $filters->to->toDateString())
            ->when($filters->get('supplier_id'), fn ($query, $supplierId) => $query->where('supplier_id', $supplierId))
            ->when($filters->get('status'), fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->get();

        $received = $orders->where('status', 'received');
        $open = $orders->whereIn('status', ['pending', 'ordered']);
        $active = $orders->where('status', '!=', 'cancelled');
        $counts = $orders->countBy('status');
        $total = fn (Collection $rows) => round($rows->sum(fn ($order) => (float) $order->total_cost), 2);

        $bySupplier = $active->groupBy('supplier_id')
            ->map(fn (Collection $rows) => [
                'name' => $rows->first()->supplier?->name ?? 'Unknown supplier',
                'orders' => $rows->count(),
                'received' => $rows->where('status', 'received')->count(),
                'spent' => $total($rows->where('status', 'received')),
                'committed' => $total($rows->whereIn('status', ['pending', 'ordered'])),
            ])
            ->sortByDesc('spent')
            ->values();

        $products = $active->groupBy(fn ($order) => mb_strtolower(trim($order->item_name)) . '|' . $order->unit)
            ->map(fn (Collection $rows) => [
                'name' => $rows->first()->item_name,
                'unit' => $rows->first()->unit,
                'orders' => $rows->count(),
                'quantity' => round($rows->sum(fn ($order) => (float) $order->quantity), 2),
                'spent' => $total($rows->where('status', 'received')),
            ])
            ->sortBy([['orders', 'desc'], ['spent', 'desc']])
            ->take(10)
            ->values();

        return [
            'title' => 'Purchase Report',
            'period' => $filters->period(),
            'uses_dates' => true,
            'summary' => [
                [
                    'label' => 'Purchase orders',
                    'value' => number_format($orders->count()),
                    'meta' => sprintf('%d pending · %d ordered · %d received', $counts['pending'] ?? 0, $counts['ordered'] ?? 0, $counts['received'] ?? 0),
                ],
                ['label' => 'Spent', 'value' => 'RM ' . number_format($total($received), 2), 'meta' => 'Received orders'],
                ['label' => 'Committed', 'value' => 'RM ' . number_format($total($open), 2), 'meta' => 'Pending and ordered orders'],
                ['label' => 'Cancelled orders', 'value' => number_format($counts['cancelled'] ?? 0), 'meta' => 'Not counted as spending'],
            ],
            'charts' => [
                [
                    'type' => 'columns',
                    'title' => 'Monthly purchasing (received orders)',
                    'points' => MonthlyTotals::series($received, 'purchase_date', $total, $filters->from, $filters->to),
                    'prefix' => 'RM ',
                    'decimals' => 2,
                    'label_heading' => 'Month',
                    'value_heading' => 'Spent (RM)',
                ],
                [
                    'type' => 'bars',
                    'title' => 'Spending by supplier',
                    'items' => $bySupplier->take(8)->map(fn ($row) => ['label' => $row['name'], 'value' => $row['spent']])->all(),
                    'prefix' => 'RM ',
                    'decimals' => 2,
                    'label_heading' => 'Supplier',
                    'value_heading' => 'Spent (RM)',
                ],
            ],
            'tables' => [
                [
                    'title' => 'Purchases by supplier',
                    'columns' => ['Supplier' => 'text', 'Orders' => 'number', 'Received' => 'number', 'Spent (RM)' => 'money', 'Committed (RM)' => 'money'],
                    'rows' => $bySupplier->map(fn ($row) => array_values($row))->all(),
                    'empty' => 'No purchase orders in this period.',
                ],
                [
                    'title' => 'Most frequently purchased products',
                    'columns' => ['Product' => 'text', 'Unit' => 'text', 'Orders' => 'number', 'Total quantity' => 'number', 'Spent (RM)' => 'money'],
                    'rows' => $products->map(fn ($row) => array_values($row))->all(),
                    'empty' => 'No purchase orders in this period.',
                ],
                [
                    'title' => 'Purchase orders',
                    'columns' => [
                        'PO number' => 'code', 'Purchase date' => 'date', 'Supplier' => 'text', 'Product' => 'text',
                        'Quantity' => 'number', 'Unit' => 'text', 'Unit price (RM)' => 'money', 'Total (RM)' => 'money', 'Status' => 'text',
                    ],
                    'rows' => $orders->map(fn (Purchase $order) => [
                        $order->po_number,
                        $order->purchase_date,
                        $order->supplier?->name ?? '—',
                        $order->item_name,
                        (float) $order->quantity,
                        $order->unit,
                        (float) $order->unit_price,
                        (float) $order->total_cost,
                        $order->statusLabel(),
                    ])->all(),
                    'empty' => 'No purchase orders match these filters.',
                ],
            ],
            'notes' => [
                'Spent counts received orders only. Committed is the value of pending and ordered orders.',
                'Cancelled orders are listed but never counted as spending.',
            ],
        ];
    }
}
