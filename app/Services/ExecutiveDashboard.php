<?php

namespace App\Services;

use App\Models\StorageLocation;
use App\Models\User;
use App\Support\ReportingPeriod;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

// Everything on the administrator's executive dashboard for one reporting
// period. Every figure comes from MySQL (see BusinessMetrics for the rules).
class ExecutiveDashboard
{
    public function __construct(private BusinessMetrics $metrics)
    {
    }

    public function build(User $admin, ReportingPeriod $period): array
    {
        $previous = $period->previous();

        $inventory = $this->metrics->inventoryValue();
        $spent = $this->metrics->expenditure($period);
        $spentBefore = $this->metrics->expenditure($previous);
        $waste = $this->metrics->waste($period);
        $wasteBefore = $this->metrics->waste($previous);
        $awaiting = $this->metrics->awaitingDelivery();
        $suppliers = $this->metrics->suppliers($period);
        $alerts = $this->metrics->alerts();
        $supplierSpend = $this->metrics->supplierSpend($period);

        [$chartStart, $chartEnd] = $this->chartWindow($period);

        $kpis = [
            'inventoryValue' => $inventory,
            'expenditure' => $spent + [
                'previous' => $spentBefore['value'],
                'change' => BusinessMetrics::change($spent['value'], $spentBefore['value']),
            ],
            'wasteCost' => $waste + [
                'previous' => $wasteBefore['cost'],
                'previousRecords' => $wasteBefore['records'],
                'change' => BusinessMetrics::change($waste['cost'], $wasteBefore['cost']),
            ],
            'awaiting' => $awaiting,
            'suppliers' => $suppliers,
            'alerts' => $alerts,
        ];

        return [
            'period' => $period,
            'previous' => $previous,
            'kpis' => $kpis,
            'charts' => [
                'window' => $chartStart->format('M Y') . ' – ' . $chartEnd->format('M Y'),
                'expenditure' => $this->emphasise($this->metrics->monthlyExpenditure($chartStart, $chartEnd), $period),
                'wasteCost' => $this->emphasise($this->metrics->monthlyWasteCost($chartStart, $chartEnd), $period),
                'categoryValue' => $this->withShares(collect($inventory['byCategory'])
                    ->map(fn ($row, $category) => ['label' => $category, 'value' => $row['value']])
                    ->values()),
                'supplierSpend' => $this->supplierChart($supplierSpend),
                'supplierCount' => $supplierSpend->count(),
            ],
            'insights' => $this->insights($period, $previous, $kpis, $supplierSpend),
            'importantNotifications' => $this->importantNotifications($admin),
            'unreadCount' => $admin->unreadNotifications()->count(),
            'staffAccounts' => $this->staffAccounts(),
            'reportCards' => $this->reportCards($kpis),
        ];
    }

    // Trend charts show the selected period plus the months before it, so even
    // "This month" has a trend: the 12 months up to the end of the period, or
    // the whole period when it is longer (at most 24 months)
    private function chartWindow(ReportingPeriod $period): array
    {
        $start = $period->to->copy()->startOfMonth()->subMonths(11);

        if ($period->from->lt($start)) {
            $start = $period->from->copy()->startOfMonth();
        }

        return [$start, $period->to->copy()];
    }

    // Marks the chart months that belong to the selected period
    private function emphasise(array $points, ReportingPeriod $period): array
    {
        $first = $period->from->format('Y-m');
        $last = $period->to->format('Y-m');

        return array_map(
            fn ($point) => $point + ['emphasis' => $point['month'] >= $first && $point['month'] <= $last],
            $points
        );
    }

    // Adds each bar's share of the total, e.g. "42%"
    private function withShares(Collection $rows): array
    {
        $total = $rows->sum('value');

        return $rows->map(function ($row) use ($total) {
            $share = $total > 0 ? $row['value'] / $total * 100 : 0;

            return $row + ['note' => $total > 0 ? ($share > 0 && $share < 1 ? '<1%' : round($share) . '%') : null];
        })->all();
    }

    // The six largest suppliers, with the rest combined
    private function supplierChart(Collection $spend): array
    {
        $bars = $spend->take(6)->map(fn ($row) => ['label' => $row['name'], 'value' => $row['spent']]);
        $rest = $spend->slice(6);

        if ($rest->isNotEmpty()) {
            $bars->push(['label' => 'Other suppliers (' . $rest->count() . ')', 'value' => round($rest->sum('spent'), 2)]);
        }

        return $this->withShares($bars->values());
    }

    // Findings stated with their numbers and the data they come from
    private function insights(ReportingPeriod $period, ReportingPeriod $previous, array $kpis, Collection $supplierSpend): array
    {
        $money = fn (float $value) => 'RM ' . number_format($value, 2);
        $now = $period->label();
        $before = $previous->label();
        $alerts = $kpis['alerts'];
        $spent = $kpis['expenditure'];
        $waste = $kpis['wasteCost'];
        $awaiting = $kpis['awaiting'];
        $insights = [];

        // Replenishment
        $restock = $alerts['out'] + $alerts['low'];
        $items = $kpis['inventoryValue']['items'];
        $insights[] = match (true) {
            $restock > 0 => [
                'icon' => 'alert',
                'tone' => $alerts['out'] > 0 ? 'critical' : 'warning',
                'title' => 'Replenishment needed',
                'text' => $restock . ($restock === 1 ? ' item needs' : ' items need') . " replenishing: {$alerts['out']} out of stock and {$alerts['low']} at or below the minimum level.",
                'basis' => 'Current stock levels',
                'link' => route('inventory.index', ['stock' => 'restock']),
                'linkLabel' => 'View items',
            ],
            $items === 0 => [
                'icon' => 'inventory',
                'tone' => 'info',
                'title' => 'No inventory yet',
                'text' => 'No inventory items have been added yet.',
                'basis' => 'Current stock levels',
            ],
            default => [
                'icon' => 'check-circle',
                'tone' => 'good',
                'title' => 'Stock levels healthy',
                'text' => "All {$items} inventory " . Str::plural('item', $items) . ' are above their minimum level.',
                'basis' => 'Current stock levels',
            ],
        };

        // Purchase expenditure compared with the previous period
        $change = $spent['change'];
        $insights[] = [
            'icon' => 'cash',
            'tone' => 'info',
            'title' => 'Purchase expenditure',
            'text' => match (true) {
                $spent['value'] == 0 && $spent['previous'] == 0 => "No purchase orders were received in {$now} or in {$before}.",
                $change === null => $money($spent['value']) . " was spent in {$now}. Nothing was spent in {$before}, so there is no change to compare.",
                $change == 0 => $money($spent['value']) . " was spent in {$now}, the same as in {$before}.",
                default => $money($spent['value']) . " was spent in {$now}, " . ($change > 0 ? 'up ' : 'down ')
                    . number_format(abs($change), 1) . '% from ' . $money($spent['previous']) . " in {$before}.",
            },
            'basis' => 'Received purchase orders, by purchase date',
            'link' => route('reports.show', ['report' => 'purchases'] + $period->reportQuery()),
            'linkLabel' => 'Purchase report',
        ];

        // Largest supplier
        $top = $supplierSpend->first();
        $insights[] = [
            'icon' => 'supplier',
            'tone' => 'info',
            'title' => 'Largest supplier',
            'text' => $top
                ? $top['name'] . ' accounts for ' . number_format($spent['value'] > 0 ? $top['spent'] / $spent['value'] * 100 : 0, 1)
                    . "% of purchase expenditure in {$now} (" . $money($top['spent']) . ' of ' . $money($spent['value']) . ', '
                    . $top['orders'] . ' ' . Str::plural('order', $top['orders']) . '). '
                    . $supplierSpend->count() . ' ' . Str::plural('supplier', $supplierSpend->count()) . ' delivered received orders in this period.'
                : "No purchase orders were received in {$now}, so there is no supplier ranking for this period.",
            'basis' => 'Received purchase orders, by purchase date',
            'link' => route('reports.show', ['report' => 'suppliers'] + $period->reportQuery()),
            'linkLabel' => 'Supplier report',
        ];

        // Waste compared with the previous period
        $insights[] = [
            'icon' => 'waste',
            'tone' => match (true) {
                $waste['records'] === 0 && $waste['previousRecords'] === 0 => 'good',
                $waste['cost'] > $waste['previous'] => 'warning',
                $waste['cost'] < $waste['previous'] => 'good',
                default => 'info',
            },
            'title' => 'Waste',
            'text' => $waste['records'] === 0 && $waste['previousRecords'] === 0
                ? "No waste was recorded in {$now} or in {$before}."
                : $waste['records'] . ' waste ' . Str::plural('record', $waste['records']) . " in {$now} (estimated " . $money($waste['cost'])
                    . '), compared with ' . $waste['previousRecords'] . ' (estimated ' . $money($waste['previous']) . ") in {$before}.",
            'basis' => $waste['records'] > 0
                ? "Waste records by record date; cost estimated for {$waste['priced']} of {$waste['records']} records"
                : 'Waste records by record date',
            'link' => route('reports.show', ['report' => 'waste'] + $period->reportQuery()),
            'linkLabel' => 'Waste report',
        ];

        // Orders still to be delivered
        if ($awaiting['count'] > 0) {
            $days = $awaiting['oldestDays'];
            $age = $days === 0 ? 'today' : "{$days} " . Str::plural('day', $days) . ' ago';
            $insights[] = [
                'icon' => 'purchase',
                'tone' => 'info',
                'title' => 'Orders awaiting delivery',
                'text' => $awaiting['count'] . ' purchase ' . Str::plural('order', $awaiting['count']) . ' worth ' . $money($awaiting['value']) . ' '
                    . ($awaiting['count'] === 1 ? 'is' : 'are') . ' pending or ordered. The oldest, ' . $awaiting['oldest']->po_number . ", was placed {$age}.",
                'basis' => 'Pending and ordered purchase orders of any date',
                'link' => route('purchases.index'),
                'linkLabel' => 'View orders',
            ];
        } else {
            $insights[] = [
                'icon' => 'check-circle',
                'tone' => 'good',
                'title' => 'Orders awaiting delivery',
                'text' => 'No purchase orders are waiting for delivery.',
                'basis' => 'Pending and ordered purchase orders of any date',
            ];
        }

        // Expiry
        $expiryDays = (int) config('inventory.expiry_alert_days');
        $expiry = $alerts['expired'] + $alerts['expiring'];
        $insights[] = [
            'icon' => 'expiry',
            'tone' => match (true) {
                $alerts['expired'] > 0 => 'critical',
                $expiry > 0 => 'warning',
                $alerts['expiryTracked'] => 'good',
                default => 'info',
            },
            'title' => 'Expiry watch',
            'text' => match (true) {
                $expiry > 0 => "{$expiry} received " . Str::plural('delivery', $expiry) . ($expiry === 1 ? ' needs' : ' need') . " attention: {$alerts['expired']} expired in the last 7 days and "
                    . "{$alerts['expiring']} " . ($alerts['expiring'] === 1 ? 'expires' : 'expire') . " within {$expiryDays} days.",
                $alerts['expiryTracked'] => "No received deliveries expire within the next {$expiryDays} days.",
                default => 'No expiry dates have been recorded on deliveries yet. Enter them when receiving perishable orders so expiry can be monitored.',
            },
            'basis' => 'Expiry dates entered when deliveries are received',
            'link' => $expiry > 0 ? route('notifications.index', ['category' => 'expiry']) : null,
            'linkLabel' => 'Expiry alerts',
        ];

        return $insights;
    }

    // Unread first, then critical before warnings, newest first. Only
    // high-priority alerts and staff account changes are shown here.
    private function importantNotifications(User $admin): Collection
    {
        $rank = ['critical' => 0, 'warning' => 1];

        return $admin->notifications()->take(50)->get()
            ->filter(fn (DatabaseNotification $notification) => isset($rank[$notification->data['severity'] ?? ''])
                || ($notification->data['category'] ?? null) === 'staff')
            ->sortBy([
                fn ($a, $b) => ($a->read_at !== null) <=> ($b->read_at !== null),
                fn ($a, $b) => ($rank[$a->data['severity'] ?? ''] ?? 2) <=> ($rank[$b->data['severity'] ?? ''] ?? 2),
                fn ($a, $b) => $b->created_at <=> $a->created_at,
            ])
            ->take(6)
            ->values();
    }

    private function staffAccounts(): array
    {
        return [
            'staff' => User::where('role', 'staff')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'recent' => User::where('role', 'staff')->orderByDesc('created_at')->orderByDesc('id')->take(4)->get(['id', 'name', 'email', 'created_at']),
        ];
    }

    // One card per report, with a figure from the selected period where it applies
    private function reportCards(array $kpis): array
    {
        $money = fn (float $value) => 'RM ' . number_format($value, 2);
        $storageTotal = StorageLocation::count();
        $storageActive = StorageLocation::where('is_active', true)->count();

        return [
            [
                'key' => 'inventory', 'title' => 'Inventory Report', 'icon' => 'inventory', 'dated' => false,
                'description' => 'Stock levels, value and status of every item.',
                'figure' => $kpis['inventoryValue']['items'] . ' items · ' . $money($kpis['inventoryValue']['value']),
            ],
            [
                'key' => 'purchases', 'title' => 'Purchase Report', 'icon' => 'purchase', 'dated' => true,
                'description' => 'Orders, spending by supplier and most purchased products.',
                'figure' => $money($kpis['expenditure']['value']) . ' spent · ' . $kpis['expenditure']['orders'] . ' received ' . Str::plural('order', $kpis['expenditure']['orders']),
            ],
            [
                'key' => 'suppliers', 'title' => 'Supplier Report', 'icon' => 'supplier', 'dated' => true,
                'description' => 'Supplier contacts with their orders and spending.',
                'figure' => "{$kpis['suppliers']['active']} of {$kpis['suppliers']['total']} suppliers ordered from",
            ],
            [
                'key' => 'storage', 'title' => 'Storage Report', 'icon' => 'storage', 'dated' => false,
                'description' => 'Storage locations by type and status.',
                'figure' => "{$storageActive} of {$storageTotal} locations active",
            ],
            [
                'key' => 'waste', 'title' => 'Waste & Expiry Report', 'icon' => 'waste', 'dated' => true,
                'description' => 'Waste records, reasons and estimated cost.',
                'figure' => $kpis['wasteCost']['records'] . ' ' . Str::plural('record', $kpis['wasteCost']['records']) . ' · est. ' . $money($kpis['wasteCost']['cost']),
            ],
            [
                'key' => 'summary', 'title' => 'Operational Summary', 'icon' => 'chart', 'dated' => true,
                'description' => 'Key figures from every module, compared with the previous period.',
                'figure' => 'Management summary',
            ],
        ];
    }
}
