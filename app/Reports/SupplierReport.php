<?php

namespace App\Reports;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Collection;

// Supplier contacts with their purchase orders and spending for a period
class SupplierReport
{
    public function build(ReportFilters $filters): array
    {
        $orders = Purchase::notCancelled()
            ->whereDate('purchase_date', '>=', $filters->from->toDateString())
            ->whereDate('purchase_date', '<=', $filters->to->toDateString())
            ->get(['supplier_id', 'status', 'total_cost', 'purchase_date'])
            ->groupBy('supplier_id');

        $total = fn (Collection $rows) => round($rows->sum(fn ($order) => (float) $order->total_cost), 2);

        $rows = Supplier::orderBy('name')->get()->map(function (Supplier $supplier) use ($orders, $total) {
            $supplierOrders = $orders->get($supplier->id, collect());

            return [
                'name' => $supplier->name,
                'phone' => $supplier->phone ?: '—',
                'email' => $supplier->email ?: '—',
                'products' => $supplier->products_supplied ?: '—',
                'orders' => $supplierOrders->count(),
                'spent' => $total($supplierOrders->where('status', 'received')),
                'last_order' => $supplierOrders->max('purchase_date'),
            ];
        })->sortByDesc('spent')->values();

        $used = $rows->where('orders', '>', 0);

        return [
            'title' => 'Supplier Report',
            'period' => $filters->period(),
            'uses_dates' => true,
            'summary' => [
                ['label' => 'Suppliers', 'value' => number_format($rows->count()), 'meta' => 'Registered suppliers'],
                ['label' => 'Ordered from', 'value' => number_format($used->count()), 'meta' => 'Suppliers with orders in this period'],
                ['label' => 'Purchase orders', 'value' => number_format($used->sum('orders')), 'meta' => 'Excluding cancelled orders'],
                ['label' => 'Spent', 'value' => 'RM ' . number_format($rows->sum('spent'), 2), 'meta' => 'Received orders in this period'],
            ],
            'charts' => [
                [
                    'type' => 'bars',
                    'title' => 'Spending by supplier',
                    'items' => $used->take(8)->map(fn ($row) => ['label' => $row['name'], 'value' => $row['spent']])->values()->all(),
                    'prefix' => 'RM ',
                    'decimals' => 2,
                    'label_heading' => 'Supplier',
                    'value_heading' => 'Spent (RM)',
                ],
            ],
            'tables' => [[
                'title' => 'Suppliers',
                'columns' => [
                    'Supplier' => 'text', 'Phone' => 'text', 'Email' => 'text', 'Products supplied' => 'text',
                    'Orders' => 'number', 'Spent (RM)' => 'money', 'Last order' => 'date',
                ],
                'rows' => $rows->map(fn ($row) => array_values($row))->all(),
                'empty' => 'No suppliers registered yet.',
            ]],
            'notes' => [
                'Orders and spending only count purchase orders dated within the selected period. Cancelled orders are excluded.',
            ],
        ];
    }
}
