<?php

namespace App\Reports;

use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Services\BusinessMetrics;
use App\Support\ReportingPeriod;
use Illuminate\Support\Str;

// One-page management summary of every module for a period, compared with
// the previous period. Uses the same calculations as the executive dashboard.
class OperationalSummaryReport
{
    public function __construct(private BusinessMetrics $metrics)
    {
    }

    public function build(ReportFilters $filters): array
    {
        $period = ReportingPeriod::between($filters->from, $filters->to);
        $previous = $period->previous();

        $inventory = $this->metrics->inventoryValue();
        $spent = $this->metrics->expenditure($period);
        $spentBefore = $this->metrics->expenditure($previous);
        $waste = $this->metrics->waste($period);
        $wasteBefore = $this->metrics->waste($previous);
        $suppliers = $this->metrics->suppliers($period);
        $suppliersBefore = $this->metrics->suppliers($previous);
        $awaiting = $this->metrics->awaitingDelivery();
        $alerts = $this->metrics->alerts();
        $statuses = $this->metrics->ordersByStatus($period);
        $supplierSpend = $this->metrics->supplierSpend($period);
        $byReason = $this->metrics->wasteByReason($period);

        $money = fn (float $value) => 'RM ' . number_format($value, 2);
        $change = function (float $current, float $before): string {
            $percent = BusinessMetrics::change($current, $before);

            return match (true) {
                $percent === null => $current > 0 ? 'New (none before)' : 'No change',
                $percent == 0 => 'No change',
                default => ($percent > 0 ? 'Up ' : 'Down ') . number_format(abs($percent), 1) . '%',
            };
        };

        return [
            'title' => 'Operational Summary Report',
            'period' => $filters->period(),
            'uses_dates' => true,
            'summary' => [
                ['label' => 'Inventory value', 'value' => $money($inventory['value']), 'meta' => $inventory['items'] . ' items in stock today'],
                ['label' => 'Purchase expenditure', 'value' => $money($spent['value']), 'meta' => $spent['orders'] . ' received ' . Str::plural('order', $spent['orders'])],
                ['label' => 'Awaiting delivery', 'value' => $money($awaiting['value']), 'meta' => $awaiting['count'] . ' pending or ordered ' . Str::plural('order', $awaiting['count'])],
                [
                    'label' => 'Estimated waste cost',
                    'value' => $money($waste['cost']),
                    'meta' => "Priced {$waste['priced']} of {$waste['records']} records",
                    'tone' => $waste['cost'] > $wasteBefore['cost'] ? 'warning' : null,
                ],
            ],
            'charts' => [
                [
                    'type' => 'bars',
                    'title' => 'Inventory value by category',
                    'items' => collect($inventory['byCategory'])->map(fn ($row, $category) => ['label' => $category, 'value' => $row['value']])->values()->all(),
                    'prefix' => 'RM ',
                    'decimals' => 2,
                    'label_heading' => 'Category',
                    'value_heading' => 'Value (RM)',
                ],
                [
                    'type' => 'bars',
                    'title' => 'Purchase expenditure by supplier',
                    'items' => $supplierSpend->take(8)->map(fn ($row) => ['label' => $row['name'], 'value' => $row['spent']])->all(),
                    'prefix' => 'RM ',
                    'decimals' => 2,
                    'label_heading' => 'Supplier',
                    'value_heading' => 'Spent (RM)',
                ],
            ],
            'tables' => [
                [
                    'title' => 'Key figures',
                    'columns' => [
                        'Measure' => 'text', 'This period' => 'text', 'Previous period' => 'text',
                        'Change' => 'text', 'How it is calculated' => 'text',
                    ],
                    'rows' => [
                        ['Purchase expenditure', $money($spent['value']), $money($spentBefore['value']), $change($spent['value'], $spentBefore['value']), 'Received orders, by purchase date'],
                        ['Received orders', (string) $spent['orders'], (string) $spentBefore['orders'], $change($spent['orders'], $spentBefore['orders']), 'Number of received purchase orders'],
                        ['Suppliers ordered from', (string) $suppliers['active'], (string) $suppliersBefore['active'], $change($suppliers['active'], $suppliersBefore['active']), 'Suppliers with orders that were not cancelled'],
                        ['Estimated waste cost', $money($waste['cost']), $money($wasteBefore['cost']), $change($waste['cost'], $wasteBefore['cost']), 'Quantity × current price of the matching inventory item'],
                        ['Waste records', (string) $waste['records'], (string) $wasteBefore['records'], $change($waste['records'], $wasteBefore['records']), 'By record date'],
                        ['Inventory value', $money($inventory['value']), '—', '—', 'Quantity × recorded unit price, today'],
                        ['Items needing replenishment', (string) ($alerts['out'] + $alerts['low']), '—', '—', "{$alerts['out']} out of stock, {$alerts['low']} at or below the minimum level, today"],
                        ['Orders awaiting delivery', $awaiting['count'] . ' (' . $money($awaiting['value']) . ')', '—', '—', 'Pending and ordered orders of any date'],
                        ['Deliveries expired or expiring', (string) ($alerts['expired'] + $alerts['expiring']), '—', '—', 'Received deliveries expired in the last 7 days or expiring within ' . config('inventory.expiry_alert_days') . ' days'],
                        ['Active storage locations', StorageLocation::where('is_active', true)->count() . ' of ' . StorageLocation::count(), '—', '—', 'Today'],
                    ],
                    'empty' => 'No figures available.',
                ],
                [
                    'title' => 'Purchase orders by status',
                    'columns' => ['Status' => 'text', 'Orders' => 'number', 'Value (RM)' => 'money', 'Counted as' => 'text'],
                    'rows' => collect($statuses)->map(fn ($row, $status) => [
                        Purchase::STATUSES[$status],
                        $row['orders'],
                        $row['value'],
                        match ($status) {
                            'received' => 'Expenditure',
                            'cancelled' => 'Not counted',
                            default => 'Awaiting delivery',
                        },
                    ])->values()->all(),
                    'empty' => 'No purchase orders in this period.',
                ],
                [
                    'title' => 'Inventory by category',
                    'columns' => ['Category' => 'text', 'Items' => 'number', 'Low or out of stock' => 'number', 'Value (RM)' => 'money', 'Share of value (%)' => 'number'],
                    'rows' => collect($inventory['byCategory'])->map(fn ($row, $category) => [
                        $category,
                        $row['items'],
                        $row['restock'],
                        $row['value'],
                        $inventory['value'] > 0 ? round($row['value'] / $inventory['value'] * 100, 1) : 0,
                    ])->values()->all(),
                    'empty' => 'No inventory items recorded.',
                ],
                [
                    'title' => 'Waste by reason',
                    'columns' => ['Reason' => 'text', 'Records' => 'number', 'Estimated cost (RM)' => 'money'],
                    'rows' => collect($byReason)->map(fn ($row, $reason) => [WasteReport::REASONS[$reason], $row['records'], $row['cost']])->values()->all(),
                    'empty' => 'No waste recorded in this period.',
                ],
            ],
            'notes' => [
                "Compared with {$previous->label()}: the same days before the selected period, so part months are compared like for like.",
                'Purchase expenditure counts received orders only. Pending and ordered orders are awaiting delivery, and cancelled orders are never counted.',
                'Inventory value, replenishment, expiry and storage figures describe today; they are not historical.',
                'Waste cost is an estimate from the current unit price of the inventory item with the same name; records without a match are not priced.',
            ],
        ];
    }
}
