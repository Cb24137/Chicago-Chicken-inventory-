@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')
    @php
        $isFiltered = collect($filters)->filter(fn ($value) => filled($value))->isNotEmpty();
    @endphp

    <x-page-header
        eyebrow="Purchase Management"
        title="Purchase Orders"
        description="Create, track and receive purchase orders from your suppliers.">
        <x-slot:actions>
            <a href="{{ route('suppliers.index') }}" class="ccc-btn ccc-btn-secondary">
                <x-icon name="supplier" /> Suppliers
            </a>
            <a href="{{ route('purchases.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Create Purchase Order
            </a>
        </x-slot:actions>
    </x-page-header>

    <div class="ccc-stat-grid is-four ccc-mb">
        <x-stat-card label="Total orders" :value="number_format($summary['total'])" icon="purchase" meta="All purchase orders" />
        <x-stat-card label="Open orders" :value="number_format($summary['open'])" icon="clock" meta="Pending or ordered" />
        <x-stat-card label="Received" :value="number_format($summary['received'])" icon="receive" tone="success" meta="Goods confirmed received" />
        <x-stat-card label="Total spending" :value="'RM ' . number_format($summary['spending'], 2)" icon="cash" meta="Excluding cancelled orders" />
    </div>

    <section class="ccc-card ccc-card-clip" aria-label="Purchase orders">
        <form method="GET" action="{{ route('purchases.index') }}" class="ccc-filters" role="search">
            <div class="ccc-field is-search">
                <label for="search" class="ccc-label">Search</label>
                <div class="ccc-input-icon-wrap">
                    <x-icon name="search" />
                    <input type="search" id="search" name="search" class="ccc-input"
                           value="{{ $filters['search'] }}"
                           placeholder="PO number, product or supplier">
                </div>
            </div>

            <div class="ccc-field">
                <label for="status" class="ccc-label">Status</label>
                <select id="status" name="status" class="ccc-input">
                    <option value="">All statuses</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-field">
                <label for="supplier_id" class="ccc-label">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="ccc-input">
                    <option value="">All suppliers</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected($filters['supplierId'] === $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="ccc-field is-date">
                <label for="from" class="ccc-label">From date</label>
                <input type="date" id="from" name="from" class="ccc-input" value="{{ $filters['from'] }}">
            </div>

            <div class="ccc-field is-date">
                <label for="to" class="ccc-label">To date</label>
                <input type="date" id="to" name="to" class="ccc-input" value="{{ $filters['to'] }}">
            </div>

            <div class="ccc-filter-actions">
                <button type="submit" class="ccc-btn ccc-btn-primary">
                    <x-icon name="filter" /> Filter
                </button>
                @if ($isFiltered)
                    <a href="{{ route('purchases.index') }}" class="ccc-btn ccc-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($purchases->isEmpty())
            @if ($isFiltered)
                <x-empty-state icon="search" title="No purchase orders match your filters">
                    Try a different search term, status, supplier or date range.
                    <x-slot:action>
                        <a href="{{ route('purchases.index') }}" class="ccc-btn ccc-btn-secondary">Clear filters</a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <x-empty-state icon="purchase" title="No purchase orders yet">
                    Create your first purchase order to record what you buy from suppliers.
                    <x-slot:action>
                        <a href="{{ route('purchases.create') }}" class="ccc-btn ccc-btn-primary">
                            <x-icon name="plus" /> Create Purchase Order
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @endif
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table is-compact">
                    <thead>
                        <tr>
                            <th scope="col">PO Number / Date</th>
                            <th scope="col">Supplier</th>
                            <th scope="col">Product</th>
                            <th scope="col" class="is-num">Quantity</th>
                            <th scope="col" class="is-num">Total Cost (RM)</th>
                            <th scope="col">Status</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($purchases as $purchase)
                            <tr>
                                <td>
                                    <a href="{{ route('purchases.show', $purchase) }}" class="ccc-cell-title ccc-nowrap">
                                        {{ $purchase->po_number }}
                                    </a>
                                    <span class="ccc-cell-sub ccc-nowrap">{{ $purchase->purchase_date->format('d M Y') }}</span>
                                </td>
                                <td class="ccc-cell-min">{{ $purchase->supplier?->name ?? '—' }}</td>
                                <td class="ccc-cell-min">
                                    <span class="ccc-cell-title">{{ $purchase->item_name }}</span>
                                    @if ($purchase->inventoryItem)
                                        <span class="ccc-cell-sub">Linked to inventory</span>
                                    @endif
                                </td>
                                <td class="is-num">{{ (float) $purchase->quantity }} {{ $purchase->unit }}</td>
                                <td class="is-num">
                                    <strong>{{ number_format((float) $purchase->total_cost, 2) }}</strong>
                                    <span class="ccc-cell-sub">@ {{ number_format((float) $purchase->unit_price, 2) }} / {{ $purchase->unit }}</span>
                                </td>
                                <td><x-status-badge :status="$purchase->status" /></td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        <a href="{{ route('purchases.show', $purchase) }}"
                                           class="ccc-btn ccc-btn-secondary ccc-btn-sm ccc-btn-icon"
                                           title="View {{ $purchase->po_number }}">
                                            <x-icon name="eye" />
                                            <span class="ccc-visually-hidden">View {{ $purchase->po_number }}</span>
                                        </a>
                                        <a href="{{ route('purchases.edit', $purchase) }}"
                                           class="ccc-btn ccc-btn-soft ccc-btn-sm ccc-btn-icon"
                                           title="Edit {{ $purchase->po_number }}">
                                            <x-icon name="edit" />
                                            <span class="ccc-visually-hidden">Edit {{ $purchase->po_number }}</span>
                                        </a>

                                        @if ($purchase->canBeReceived())
                                            {{-- Opens the receive panel, where the delivery's expiry date can be entered --}}
                                            <a href="{{ route('purchases.show', $purchase) }}#receive"
                                               class="ccc-btn ccc-btn-success ccc-btn-sm ccc-btn-icon"
                                               title="Receive {{ $purchase->po_number }}">
                                                <x-icon name="receive" />
                                                <span class="ccc-visually-hidden">Receive {{ $purchase->po_number }}</span>
                                            </a>
                                        @endif

                                        @unless ($purchase->hasAddedStock())
                                            <form method="POST"
                                                  action="{{ route('purchases.destroy', $purchase) }}"
                                                  class="ccc-inline-form"
                                                  data-confirm="Delete purchase order {{ $purchase->po_number }}? This cannot be undone.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="ccc-btn ccc-btn-danger ccc-btn-sm ccc-btn-icon"
                                                        title="Delete {{ $purchase->po_number }}">
                                                    <x-icon name="trash" />
                                                    <span class="ccc-visually-hidden">Delete {{ $purchase->po_number }}</span>
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $purchases->links() }}
        @endif
    </section>
@endsection
