@extends('layouts.app')

@section('title', 'Inventory Management')

@section('content')
    @php
        $isFiltered = $search !== '' || $category || $stock;
    @endphp

    <x-page-header
        title="Food & Beverage Inventory"
        description="Manage food ingredients, beverages, packaging and stock levels.">
        <x-slot:actions>
            <a href="{{ route('inventory.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Add Inventory
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="ccc-stat-grid ccc-mb">
        <x-stat-card label="Inventory items" :value="number_format($summary['items'])" icon="inventory" meta="Food, beverage, packaging and other supplies" />
        <x-stat-card label="Low stock items"
                     :value="number_format($summary['low'])"
                     icon="alert"
                     :href="route('inventory.index', ['stock' => 'restock'])"
                     :tone="$summary['low'] > 0 ? 'warning' : 'success'"
                     :meta="$summary['low'] > 0 ? 'At or below minimum stock (including out of stock)' : 'All items above minimum stock'" />
        <x-stat-card label="Stock value" :value="'RM ' . number_format($summary['value'], 2)" icon="cash" meta="Quantity × unit price" />
    </div>

    <section class="ccc-card ccc-card-clip" aria-label="Inventory items">
        <form method="GET" action="{{ route('inventory.index') }}" class="ccc-filters" role="search">
            <div class="ccc-field is-search">
                <label for="search" class="ccc-label">Search</label>
                <div class="ccc-input-icon-wrap">
                    <x-icon name="search" />
                    <input type="search" id="search" name="search" class="ccc-input"
                           value="{{ $search }}"
                           placeholder="Item name">
                </div>
            </div>

            <div class="ccc-field">
                <label for="category" class="ccc-label">Category</label>
                <select id="category" name="category" class="ccc-input">
                    <option value="">All categories</option>
                    @foreach (\App\Models\InventoryItem::CATEGORIES as $option)
                        <option value="{{ $option }}" @selected($category === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-field">
                <label for="stock" class="ccc-label">Stock status</label>
                <select id="stock" name="stock" class="ccc-input">
                    <option value="">All stock levels</option>
                    @foreach (\App\Models\InventoryItem::STOCK_STATUSES as $value => $label)
                        <option value="{{ $value }}" @selected($stock === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-filter-actions">
                <button type="submit" class="ccc-btn ccc-btn-primary">
                    <x-icon name="filter" /> Filter
                </button>
                @if ($isFiltered)
                    <a href="{{ route('inventory.index') }}" class="ccc-btn ccc-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($inventoryItems->isEmpty())
            @if ($isFiltered)
                <x-empty-state icon="search" title="No inventory items match your filters">
                    Try a different name, category or stock status.
                    <x-slot:action>
                        <a href="{{ route('inventory.index') }}" class="ccc-btn ccc-btn-secondary">Clear filters</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="inventory" title="No inventory items found">
                    Add your first food or beverage item to start tracking stock.
                    <x-slot:action>
                        <a href="{{ route('inventory.create') }}" class="ccc-btn ccc-btn-primary">
                            <x-icon name="plus" /> Add Inventory
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
                            <th scope="col">Category</th>
                            <th scope="col" class="is-num">Quantity</th>
                            <th scope="col" class="is-num">Minimum Stock</th>
                            <th scope="col" class="is-num">Unit Price (RM)</th>
                            <th scope="col">Stock Status</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($inventoryItems as $item)
                            @php $level = $item->stockLevel(); @endphp
                            <tr>
                                <td><span class="ccc-cell-title">{{ $item->item_name }}</span></td>
                                <td><span class="ccc-badge is-brand">{{ $item->category }}</span></td>
                                <td class="is-num">{{ (float) $item->quantity }} {{ $item->unit }}</td>
                                <td class="is-num">{{ (float) $item->minimum_stock }} {{ $item->unit }}</td>
                                <td class="is-num">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>
                                    @if ($level === 'out')
                                        <span class="ccc-badge is-danger"><x-icon name="alert" /> Out of Stock</span>
                                    @elseif ($level === 'low')
                                        <span class="ccc-badge is-warning"><x-icon name="alert" /> Low Stock</span>
                                    @else
                                        <span class="ccc-badge is-success"><x-icon name="check-circle" /> In Stock</span>
                                    @endif
                                </td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        @if ($level)
                                            <a href="{{ route('purchases.create', ['inventory_item_id' => $item->id]) }}"
                                               class="ccc-btn ccc-btn-secondary ccc-btn-sm"
                                               title="Create a purchase order for {{ $item->item_name }}">
                                                <x-icon name="purchase" /> Reorder
                                            </a>
                                        @endif

                                        <a href="{{ route('inventory.edit', $item->id) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                                            <x-icon name="edit" /> Edit
                                        </a>

                                        <form method="POST"
                                              action="{{ route('inventory.destroy', $item->id) }}"
                                              class="ccc-inline-form"
                                              data-confirm="Are you sure you want to delete this inventory item?">
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

            @if ($isFiltered)
                <div class="ccc-card-footer">
                    Showing {{ $inventoryItems->count() }} of {{ $summary['items'] }} inventory items.
                </div>
            @endif
        @endif
    </section>
@endsection
