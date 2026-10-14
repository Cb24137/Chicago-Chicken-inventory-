@extends('layouts.app')

@section('title', 'Supplier Management')

@section('content')
    <x-page-header
        title="Supplier Management"
        description="Manage supplier contact details and the products they supply. Quantities and costs are recorded on purchase orders.">
        <x-slot:actions>
            <a href="{{ route('purchases.index') }}" class="ccc-btn ccc-btn-secondary">
                <x-icon name="purchase" /> Purchase Orders
            </a>
            <a href="{{ route('suppliers.create') }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="plus" /> Add Supplier
            </a>
        </x-slot:actions>
    </x-page-header>

    {{-- Totals for all suppliers (the search below does not change them) --}}
    <div class="ccc-stat-grid is-four ccc-mb">
        <x-stat-card label="Total suppliers"
                     :value="number_format($summary['suppliers'])"
                     icon="supplier"
                     meta="Registered supplier contacts" />
        <x-stat-card label="Total purchase orders"
                     :value="number_format($summary['orders'])"
                     icon="purchase"
                     :href="route('purchases.index')"
                     meta="Orders linked to these suppliers" />
        <x-stat-card label="Suppliers with orders"
                     :value="number_format($summary['withOrders'])"
                     icon="receive"
                     :meta="($summary['suppliers'] - $summary['withOrders']) . ' without any orders yet'" />
        <x-stat-card label="Total order value"
                     :value="'RM ' . number_format($summary['orderValue'], 2)"
                     icon="cash"
                     meta="Orders not cancelled · order value, not money paid" />
    </div>

    <section class="ccc-card ccc-card-clip" aria-label="Suppliers">
        <form method="GET" action="{{ route('suppliers.index') }}" class="ccc-filters" role="search">
            <div class="ccc-field is-search">
                <label for="search" class="ccc-label">Search suppliers</label>
                <div class="ccc-input-icon-wrap">
                    <x-icon name="search" />
                    <input type="search" id="search" name="search" class="ccc-input"
                           value="{{ $search }}"
                           placeholder="Name, phone, email or product">
                </div>
            </div>

            <div class="ccc-filter-actions">
                <button type="submit" class="ccc-btn ccc-btn-primary">
                    <x-icon name="search" /> Search
                </button>
                @if ($search !== '')
                    <a href="{{ route('suppliers.index') }}" class="ccc-btn ccc-btn-secondary">Reset</a>
                @endif
            </div>
        </form>

        @if ($suppliers->isEmpty())
            @if ($search !== '')
                <x-empty-state icon="search" title="No suppliers match your search">
                    Try another name, phone number, email or product.
                </x-empty-state>
            @else
                <x-empty-state icon="supplier" title="No suppliers found">
                    Add your first supplier to start creating purchase orders.
                    <x-slot:action>
                        <a href="{{ route('suppliers.create') }}" class="ccc-btn ccc-btn-primary">
                            <x-icon name="plus" /> Add Supplier
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @endif
        @else
            <div class="ccc-table-wrap">
                <table class="ccc-table">
                    <thead>
                        <tr>
                            <th scope="col">Supplier Name</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Email Address</th>
                            <th scope="col">Address</th>
                            <th scope="col">Products Supplied</th>
                            <th scope="col" class="is-actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($suppliers as $supplier)
                            <tr>
                                <td>
                                    <span class="ccc-cell-title">{{ $supplier->name }}</span>
                                    <a href="{{ route('purchases.index', ['supplier_id' => $supplier->id]) }}" class="ccc-cell-sub ccc-text-link">
                                        {{ $supplier->purchases_count }} purchase {{ \Illuminate\Support\Str::plural('order', $supplier->purchases_count) }}
                                    </a>
                                </td>
                                <td class="ccc-nowrap">{{ $supplier->phone ?: '—' }}</td>
                                <td>{{ $supplier->email ?: '—' }}</td>
                                <td class="ccc-cell-wrap">{{ $supplier->address ?: '—' }}</td>
                                <td class="ccc-cell-wrap">{{ $supplier->products_supplied ?: '—' }}</td>
                                <td class="is-actions">
                                    <div class="ccc-table-actions">
                                        <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}"
                                           class="ccc-btn ccc-btn-secondary ccc-btn-sm">
                                            <x-icon name="plus" /> New Order
                                        </a>
                                        <a href="{{ route('suppliers.edit', $supplier) }}" class="ccc-btn ccc-btn-soft ccc-btn-sm">
                                            <x-icon name="edit" /> Edit
                                        </a>

                                        @if ($supplier->purchases_count > 0)
                                            <button type="button" class="ccc-btn ccc-btn-danger ccc-btn-sm" disabled
                                                    title="Suppliers with purchase orders cannot be deleted">
                                                <x-icon name="trash" /> Delete
                                            </button>
                                        @else
                                            <form method="POST"
                                                  action="{{ route('suppliers.destroy', $supplier) }}"
                                                  class="ccc-inline-form"
                                                  data-confirm="Are you sure you want to delete {{ $supplier->name }}?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ccc-btn ccc-btn-danger ccc-btn-sm">
                                                    <x-icon name="trash" /> Delete
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $suppliers->links() }}
        @endif
    </section>
@endsection
