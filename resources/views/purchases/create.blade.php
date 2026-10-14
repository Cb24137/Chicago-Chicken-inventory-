@extends('layouts.app')

@section('title', 'Create Purchase Order')

@section('content')
    <x-page-header
        eyebrow="Purchase Management"
        title="Create Purchase Order"
        description="Order stock from a supplier. A purchase order number is generated automatically when you save."
        :back="route('purchases.index')"
        back-label="Back to purchase orders" />

    @if ($suppliers->isEmpty())
        <div class="ccc-card ccc-form-card">
            <x-empty-state icon="supplier" title="Add a supplier first">
                Every purchase order must be linked to a registered supplier.
                <x-slot:action>
                    <a href="{{ route('suppliers.create') }}" class="ccc-btn ccc-btn-primary">
                        <x-icon name="plus" /> Add Supplier
                    </a>
                </x-slot:action>
            </x-empty-state>
        </div>
    @else
        @include('purchases._form', [
            'action' => route('purchases.store'),
            'method' => 'POST',
            'submitLabel' => 'Create Purchase Order',
        ])
    @endif
@endsection
