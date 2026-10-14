<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\WasteExpiryRecord;
use App\Support\MonthlyTotals;
use App\Support\ReportingPeriod;
use App\Support\WasteCostEstimator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

// Business figures for the executive dashboard and the Operational Summary
// report, calculated from MySQL on every request:
//   Inventory value    = quantity x recorded unit price (current stock, no history)
//   Expenditure        = received purchase orders, by purchase date
//   Awaiting delivery  = pending + ordered orders; cancelled orders never count
//   Waste cost         = estimate from matching inventory prices (WasteCostEstimator)
class BusinessMetrics
{
    private ?WasteCostEstimator $estimator = null;

    // ['value' => RM, 'items' => n, 'byCategory' => [category => ['items', 'value', 'restock']]]
    public function inventoryValue(): array
    {
        $rows = InventoryItem::query()
            ->selectRaw('category, COUNT(*) AS items, COALESCE(SUM(quantity * unit_price), 0) AS value')
            ->selectRaw('SUM(CASE WHEN quantity <= minimum_stock THEN 1 ELSE 0 END) AS restock')
            ->groupBy('category')
            ->get()
            ->keyBy('category');

        $byCategory = collect(InventoryItem::CATEGORIES)->mapWithKeys(fn ($category) => [$category => [
            'items' => (int) ($rows[$category]->items ?? 0),
            'value' => round((float) ($rows[$category]->value ?? 0), 2),
            'restock' => (int) ($rows[$category]->restock ?? 0),
        ]]);

        return [
            'value' => round($byCategory->sum('value'), 2),
            'items' => $byCategory->sum('items'),
            'byCategory' => $byCategory->all(),
        ];
    }

    // Received orders dated within the period: ['value' => RM, 'orders' => n]
    public function expenditure(ReportingPeriod $period): array
    {
        $row = $this->purchasesIn($period)
            ->where('status', 'received')
            ->selectRaw('COUNT(*) AS orders, COALESCE(SUM(total_cost), 0) AS value')
            ->first();

        return ['value' => round((float) $row->value, 2), 'orders' => (int) $row->orders];
    }

    // Orders dated within the period, by status: [status => ['orders' => n, 'value' => RM]]
    public function ordersByStatus(ReportingPeriod $period): array
    {
        $rows = $this->purchasesIn($period)
            ->selectRaw('status, COUNT(*) AS orders, COALESCE(SUM(total_cost), 0) AS value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return collect(array_keys(Purchase::STATUSES))->mapWithKeys(fn ($status) => [$status => [
            'orders' => (int) ($rows[$status]->orders ?? 0),
            'value' => round((float) ($rows[$status]->value ?? 0), 2),
        ]])->all();
    }

    // Pending and ordered orders of any date (goods still to be received)
    public function awaitingDelivery(): array
    {
        $oldest = Purchase::open()->orderBy('purchase_date')->orderBy('id')->first();

        return [
            'count' => Purchase::open()->count(),
            'value' => round((float) Purchase::open()->sum('total_cost'), 2),
            'oldest' => $oldest,
            'oldestDays' => $oldest ? (int) $oldest->purchase_date->diffInDays(today()) : null,
        ];
    }

    // Waste recorded within the period: ['records' => n, 'cost' => RM, 'priced' => n]
    public function waste(ReportingPeriod $period): array
    {
        $records = WasteExpiryRecord::query()
            ->whereDate('record_date', '>=', $period->from->toDateString())
            ->whereDate('record_date', '<=', $period->to->toDateString())
            ->get();

        $summary = $this->estimator()->summarise($records);

        return ['records' => $summary['count'], 'cost' => $summary['cost'], 'priced' => $summary['priced']];
    }

    // Waste records within the period by reason: [reason => ['records' => n, 'cost' => RM]]
    public function wasteByReason(ReportingPeriod $period): array
    {
        $records = WasteExpiryRecord::query()
            ->whereDate('record_date', '>=', $period->from->toDateString())
            ->whereDate('record_date', '<=', $period->to->toDateString())
            ->get()
            ->groupBy('reason');

        return collect(['expired', 'spoiled', 'damaged', 'other'])->mapWithKeys(fn ($reason) => [$reason => [
            'records' => $records->get($reason, collect())->count(),
            'cost' => $this->estimator()->summarise($records->get($reason, collect()))['cost'],
        ]])->all();
    }

    // Registered suppliers, and those with non-cancelled orders in the period
    public function suppliers(ReportingPeriod $period): array
    {
        return [
            'total' => Supplier::count(),
            'active' => $this->purchasesIn($period)->where('status', '!=', 'cancelled')->distinct()->count('supplier_id'),
        ];
    }

    // Received spend per supplier in the period, largest first:
    // [['name', 'orders', 'spent'], ...]
    public function supplierSpend(ReportingPeriod $period): Collection
    {
        $rows = $this->purchasesIn($period)
            ->where('status', 'received')
            ->selectRaw('supplier_id, COUNT(*) AS orders, SUM(total_cost) AS spent')
            ->groupBy('supplier_id')
            ->get();

        $names = Supplier::whereIn('id', $rows->pluck('supplier_id'))->pluck('name', 'id');

        return $rows
            ->map(fn ($row) => [
                'name' => $names[$row->supplier_id] ?? 'Unknown supplier',
                'orders' => (int) $row->orders,
                'spent' => round((float) $row->spent, 2),
            ])
            ->sortBy([['spent', 'desc'], ['name', 'asc']])
            ->values();
    }

    // Stock and expiry problems right now
    public function alerts(): array
    {
        $stock = InventoryItem::query()
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END), 0) AS out_of_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN quantity > 0 AND quantity <= minimum_stock THEN 1 ELSE 0 END), 0) AS low')
            ->first();

        $deliveries = Purchase::expiryWatch()->get();
        $expired = $deliveries->filter(fn (Purchase $purchase) => $purchase->daysUntilExpiry() < 0)->count();

        $out = (int) $stock->out_of_stock;
        $low = (int) $stock->low;

        return [
            'out' => $out,
            'low' => $low,
            'expired' => $expired,
            'expiring' => $deliveries->count() - $expired,
            'critical' => $out + $expired,
            'total' => $out + $low + $deliveries->count(),
            // False until someone records an expiry date on a delivery
            'expiryTracked' => Purchase::whereNotNull('expiry_date')->exists(),
        ];
    }

    // Received spend per month (chart points)
    public function monthlyExpenditure(Carbon $start, Carbon $end): array
    {
        $orders = Purchase::where('status', 'received')
            ->whereDate('purchase_date', '>=', $start->toDateString())
            ->whereDate('purchase_date', '<=', $end->toDateString())
            ->get(['purchase_date', 'total_cost']);

        return MonthlyTotals::series(
            $orders,
            'purchase_date',
            fn (Collection $rows) => round($rows->sum(fn ($row) => (float) $row->total_cost), 2),
            $start,
            $end
        );
    }

    // Estimated waste cost per month (chart points)
    public function monthlyWasteCost(Carbon $start, Carbon $end): array
    {
        $records = WasteExpiryRecord::query()
            ->whereDate('record_date', '>=', $start->toDateString())
            ->whereDate('record_date', '<=', $end->toDateString())
            ->get();

        return MonthlyTotals::series(
            $records,
            'record_date',
            fn (Collection $rows) => $this->estimator()->summarise($rows)['cost'],
            $start,
            $end
        );
    }

    // Percentage change, or null when there is nothing to compare with
    public static function change(float $current, float $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }

    private function purchasesIn(ReportingPeriod $period): Builder
    {
        return Purchase::query()
            ->whereDate('purchase_date', '>=', $period->from->toDateString())
            ->whereDate('purchase_date', '<=', $period->to->toDateString());
    }

    private function estimator(): WasteCostEstimator
    {
        return $this->estimator ??= new WasteCostEstimator();
    }
}
