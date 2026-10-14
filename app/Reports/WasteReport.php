<?php

namespace App\Reports;

use App\Models\WasteExpiryRecord;
use App\Support\MonthlyTotals;
use App\Support\WasteCostEstimator;
use Illuminate\Support\Collection;

// Waste and expiry records, quantities, estimated cost and trends for a period
class WasteReport
{
    public const REASONS = ['expired' => 'Expired', 'spoiled' => 'Spoiled', 'damaged' => 'Damaged', 'other' => 'Other'];

    public function build(ReportFilters $filters): array
    {
        $records = WasteExpiryRecord::query()
            ->whereDate('record_date', '>=', $filters->from->toDateString())
            ->whereDate('record_date', '<=', $filters->to->toDateString())
            ->when($filters->get('reason'), fn ($query, $reason) => $query->where('reason', $reason))
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get();

        $estimator = new WasteCostEstimator();
        $cost = $estimator->summarise($records);
        $byReason = $records->countBy('reason');

        $byUnit = $records->groupBy('unit')
            ->map(fn (Collection $rows, $unit) => [$unit, $rows->count(), round($rows->sum(fn ($row) => (float) $row->quantity), 2)])
            ->sortByDesc(fn ($row) => $row[1])
            ->values();

        return [
            'title' => 'Waste & Expiry Report',
            'period' => $filters->period(),
            'uses_dates' => true,
            'summary' => [
                ['label' => 'Waste records', 'value' => number_format($records->count()), 'meta' => 'Recorded in this period'],
                ['label' => 'Expired products', 'value' => number_format($byReason['expired'] ?? 0), 'meta' => 'Reason: expired', 'tone' => ($byReason['expired'] ?? 0) > 0 ? 'danger' : null],
                ['label' => 'Damaged or spoiled', 'value' => number_format(($byReason['damaged'] ?? 0) + ($byReason['spoiled'] ?? 0)), 'meta' => 'Reasons: damaged, spoiled'],
                ['label' => 'Estimated cost', 'value' => 'RM ' . number_format($cost['cost'], 2), 'meta' => "Priced {$cost['priced']} of {$cost['count']} records"],
            ],
            'charts' => [
                [
                    'type' => 'columns',
                    'title' => 'Waste records per month',
                    'points' => MonthlyTotals::series($records, 'record_date', fn (Collection $rows) => $rows->count(), $filters->from, $filters->to),
                    'unit' => 'record',
                    'label_heading' => 'Month',
                    'value_heading' => 'Records',
                ],
                [
                    'type' => 'bars',
                    'title' => 'Records by reason',
                    'items' => collect(self::REASONS)->map(fn ($label, $key) => ['label' => $label, 'value' => $byReason[$key] ?? 0])->values()->all(),
                    'unit' => 'record',
                    'label_heading' => 'Reason',
                    'value_heading' => 'Records',
                ],
            ],
            'tables' => [
                [
                    'title' => 'Waste quantities by unit',
                    'columns' => ['Unit' => 'text', 'Records' => 'number', 'Total quantity' => 'number'],
                    'rows' => $byUnit->all(),
                    'empty' => 'No waste recorded in this period.',
                ],
                [
                    'title' => 'Waste records',
                    'columns' => [
                        'Record date' => 'date', 'Item' => 'text', 'Quantity' => 'number', 'Unit' => 'text',
                        'Reason' => 'text', 'Expiry date' => 'date', 'Estimated cost (RM)' => 'money',
                    ],
                    'rows' => $records->map(fn (WasteExpiryRecord $record) => [
                        $record->record_date,
                        $record->item_name,
                        (float) $record->quantity,
                        $record->unit,
                        self::REASONS[$record->reason] ?? ucfirst($record->reason),
                        $record->expiry_date,
                        $estimator->estimate($record),
                    ])->all(),
                    'empty' => 'No waste records match these filters.',
                ],
            ],
            'notes' => [
                'Estimated cost = quantity × the current unit price of the inventory item with the same name, when the units match (g and mL are converted). Records without a match are not priced.',
            ],
        ];
    }
}
