@extends('layouts.app')

@section('title', 'Storage Locations')

@section('content')
    @php
        $isFiltered = $search !== '' || $type || $status;
    @endphp

    <x-page-header
        eyebrow="Food Storage & Inventory Location"
        title="Storage Locations"
        description="Manage food and beverage storage areas.">
        <x-slot:actions>
            <a href="{{ route('storage-locations.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Add Storage Location
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Totals for all locations (the search and filters below do not change them) --}}
    <div class="ccc-stat-grid is-four ccc-mb">
        <x-stat-card label="Total storage locations"
                     :value="number_format($summary['total'])"
                     icon="storage"
                     meta="Freezers, refrigerators and dry storage" />
        <x-stat-card label="Active locations"
                     :value="number_format($summary['active'])"
                     icon="check-circle"
                     tone="success"
                     meta="Currently in use" />
        <x-stat-card label="Inactive locations"
                     :value="number_format($summary['inactive'])"
                     icon="close"
                     meta="Not in use" />
        <x-stat-card label="Recorded items"
                     :value="number_format($summary['items'])"
                     icon="inventory"
                     :meta="'Item counts entered on ' . $summary['counted'] . ' of ' . $summary['total'] . ' ' . \Illuminate\Support\Str::plural('location', $summary['total'])" />
    </div>

    <section class="ccc-card ccc-card-clip" aria-label="Storage locations">
        <form method="GET" action="{{ route('storage-locations.index') }}" class="ccc-filters" role="search">
            <div class="ccc-field is-search">
                <label for="search" class="ccc-label">Search</label>
                <div class="ccc-input-icon-wrap">
                    <x-icon name="search" />
                    <input type="search" id="search" name="search" class="ccc-input"
                           value="{{ $search }}"
                           placeholder="Location name">
                </div>
            </div>

            <div class="ccc-field">
                <label for="type" class="ccc-label">Storage type</label>
                <select id="type" name="type" class="ccc-input">
                    <option value="">All types</option>
                    @foreach (['Freezer', 'Refrigerator', 'Dry Storage'] as $option)
                        <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-field">
                <label for="status" class="ccc-label">Status</label>
                <select id="status" name="status" class="ccc-input">
                    <option value="">Active and inactive</option>
                    <option value="active" @selected($status === 'active')>Active</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                </select>
            </div>

            <div class="ccc-filter-actions">
                <button type="submit" class="ccc-btn ccc-btn-primary">
                    <x-icon name="filter" /> Filter
                </button>
                @if ($isFiltered)
                    <a href="{{ route('storage-locations.index') }}" class="ccc-btn ccc-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($locations->isEmpty() && $isFiltered)
            <x-empty-state icon="search" title="No storage locations match your filters">
                Try a different name, storage type or status.
                <x-slot:action>
                    <a href="{{ route('storage-locations.index') }}" class="ccc-btn ccc-btn-secondary">Clear filters</a>
                </x-slot:action>
            </x-empty-state>
        @elseif ($locations->isEmpty())
            <x-empty-state icon="storage" title="No storage locations found">
                Add a freezer, refrigerator or dry storage area to get started.
                <x-slot:action>
                    <a href="{{ route('storage-locations.create') }}" class="ccc-btn ccc-btn-primary">
                        <x-icon name="plus" /> Add Storage Location
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table">
                    <thead>
                        <tr>
                            <th scope="col">Location Name</th>
                            <th scope="col">Storage Type</th>
                            <th scope="col">Description</th>
                            <th scope="col" class="is-num">Quantity</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($locations as $location)
                            <tr>
                                <td><span class="ccc-cell-title">{{ $location->name }}</span></td>
                                <td><span class="ccc-badge is-brand">{{ $location->type }}</span></td>
                                <td class="ccc-cell-wrap">{{ $location->description ?? '-' }}</td>
                                <td class="is-num">{{ $location->quantity !== null ? number_format($location->quantity) . ' ' . \Illuminate\Support\Str::plural('item', $location->quantity) : '-' }}</td>
                                <td>
                                    @if ($location->is_active)
                                        <span class="ccc-badge is-success"><x-icon name="check-circle" /> Active</span>
                                    @else
                                        <span class="ccc-badge is-neutral"><x-icon name="close" /> Inactive</span>
                                    @endif
                                </td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        <a href="{{ route('storage-locations.edit', $location) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                                            <x-icon name="edit" /> Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('storage-locations.destroy', $location) }}"
                                              class="ccc-inline-form"
                                              data-confirm="Delete this storage location?">
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

            {{ $locations->links() }}
        @endif
    </section>
@endsection
