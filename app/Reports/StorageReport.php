<?php

namespace App\Reports;

use App\Models\StorageLocation;

// Storage locations by type and status
class StorageReport
{
    public const TYPES = ['Freezer', 'Refrigerator', 'Dry Storage'];

    public function build(ReportFilters $filters): array
    {
        $locations = StorageLocation::query()
            ->when($filters->get('type'), fn ($query, $type) => $query->where('type', $type))
            ->when($filters->get('location_status'), fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->orderBy('name')
            ->get();

        $active = $locations->where('is_active', true)->count();

        return [
            'title' => 'Storage Report',
            'period' => 'Storage locations on ' . now()->format('d M Y, g:i A'),
            'uses_dates' => false,
            'summary' => [
                ['label' => 'Storage locations', 'value' => number_format($locations->count()), 'meta' => 'Matching the selected filters'],
                ['label' => 'Active', 'value' => number_format($active), 'meta' => 'In use'],
                ['label' => 'Inactive', 'value' => number_format($locations->count() - $active), 'meta' => 'Not in use'],
                ['label' => 'Recorded quantity', 'value' => number_format((int) $locations->sum('quantity')), 'meta' => 'Items recorded on locations'],
            ],
            'charts' => [
                [
                    'type' => 'bars',
                    'title' => 'Locations by type',
                    'items' => collect(self::TYPES)
                        ->map(fn ($type) => ['label' => $type, 'value' => $locations->where('type', $type)->count()])
                        ->all(),
                    'unit' => 'location',
                    'label_heading' => 'Type',
                    'value_heading' => 'Locations',
                ],
            ],
            'tables' => [[
                'title' => 'Storage locations',
                'columns' => ['Location' => 'text', 'Type' => 'text', 'Status' => 'text', 'Recorded quantity' => 'number', 'Description' => 'text'],
                'rows' => $locations->map(fn (StorageLocation $location) => [
                    $location->name,
                    $location->type,
                    $location->is_active ? 'Active' : 'Inactive',
                    $location->quantity,
                    $location->description ?: '—',
                ])->all(),
                'empty' => 'No storage locations match these filters.',
            ]],
            'notes' => [
                'Recorded quantity is the number entered on each storage location. It is not linked to inventory stock levels.',
            ],
        ];
    }
}
