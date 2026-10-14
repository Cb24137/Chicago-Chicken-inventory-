@extends('layouts.app')

@section('title', 'Waste & Expiry Management')

@section('content')
    @php
        $isFiltered = request()->filled('search') || request()->filled('reason');
        $reasonTones = ['expired' => 'is-danger', 'spoiled' => 'is-warning', 'damaged' => 'is-warning', 'other' => 'is-neutral'];
    @endphp

    <x-page-header
        title="Waste & Expiry Management"
        description="Track expired, spoiled, damaged and wasted food items.">
        <x-slot:actions>
            <a href="{{ route('waste-expiry.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Record Waste
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="ccc-stat-grid is-four ccc-mb">
        <x-stat-card label="Total records" :value="number_format($totalRecords)" icon="waste" meta="All waste and expiry records" />
        <x-stat-card label="Expired items" :value="number_format($expiredCount)" icon="calendar" meta="Expiry date has passed" />
        <x-stat-card label="Spoiled records" :value="number_format($spoiledCount)" icon="alert" meta="Recorded as spoiled" />
        <x-stat-card label="Damaged records" :value="number_format($damagedCount)" icon="inventory" meta="Recorded as damaged" />
    </div>

    <section class="ccc-card ccc-card-clip" aria-label="Waste records">
        <form method="GET" action="{{ route('waste-expiry.index') }}" class="ccc-filters" role="search">
            <div class="ccc-field is-search">
                <label for="search" class="ccc-label">Search</label>
                <div class="ccc-input-icon-wrap">
                    <x-icon name="search" />
                    <input type="search" id="search" name="search" class="ccc-input"
                           placeholder="Search item name..."
                           value="{{ request('search') }}">
                </div>
            </div>

            <div class="ccc-field">
                <label for="reason" class="ccc-label">Reason</label>
                <select id="reason" name="reason" class="ccc-input">
                    <option value="">All reasons</option>
                    @foreach (['expired', 'spoiled', 'damaged', 'other'] as $reason)
                        <option value="{{ $reason }}" @selected(request('reason') === $reason)>{{ ucfirst($reason) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-filter-actions">
                <button type="submit" class="ccc-btn ccc-btn-primary">
                    <x-icon name="filter" /> Filter
                </button>
                <a href="{{ route('waste-expiry.index') }}" class="ccc-btn ccc-btn-secondary">Reset</a>
            </div>
        </form>

        @if ($records->isEmpty())
            @if ($isFiltered)
                <x-empty-state icon="search" title="No waste records match your filters">
                    Try a different item name or reason.
                </x-empty-state>
            @else
                <x-empty-state icon="waste" title="No waste records found">
                    Select "Record Waste" to add your first record.
                    <x-slot:action>
                        <a href="{{ route('waste-expiry.create') }}" class="ccc-btn ccc-btn-primary">
                            <x-icon name="plus" /> Record Waste
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @endif
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table">
                    <thead>
                        <tr>
                            <th scope="col">Item Name</th>
                            <th scope="col" class="is-num">Quantity</th>
                            <th scope="col">Reason</th>
                            <th scope="col">Expiry Date</th>
                            <th scope="col">Record Date</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $record)
                            <tr>
                                <td>
                                    <a href="{{ route('waste-expiry.show', $record) }}" class="ccc-cell-title">{{ $record->item_name }}</a>
                                </td>
                                <td class="is-num">{{ (float) $record->quantity }} {{ $record->unit }}</td>
                                <td>
                                    <span class="ccc-badge {{ $reasonTones[$record->reason] ?? 'is-neutral' }}">{{ ucfirst($record->reason) }}</span>
                                </td>
                                <td class="ccc-nowrap">{{ $record->expiry_date ? $record->expiry_date->format('d M Y') : '-' }}</td>
                                <td class="ccc-nowrap">{{ $record->record_date->format('d M Y') }}</td>
                                <td class="ccc-cell-wrap">{{ $record->description ?: '-' }}</td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        <a href="{{ route('waste-expiry.show', $record) }}" class="ccc-btn ccc-btn-secondary ccc-btn-sm">
                                            <x-icon name="eye" /> View
                                        </a>
                                        <a href="{{ route('waste-expiry.edit', $record) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                                            <x-icon name="edit" /> Edit
                                        </a>
                                        <form method="POST"
                                              action="{{ route('waste-expiry.destroy', $record) }}"
                                              class="ccc-inline-form"
                                              data-confirm="Delete this waste record?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ccc-btn ccc-btn-danger ccc-btn-sm">
                                                <x-icon name="trash" /> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $records->links() }}
        @endif
    </section>
@endsection
