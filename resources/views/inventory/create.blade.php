@extends('layouts.app')

@section('title', 'Add Inventory')

@section('content')
    <x-page-header
        eyebrow="Food & Beverage Inventory"
        title="Add Inventory Item"
        description="Enter the details of a new food or beverage item."
        :back="route('inventory.index')"
        back-label="Back to inventory" />

    @include('inventory._form', [
        'inventoryItem' => new \App\Models\InventoryItem(),
        'action' => route('inventory.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Inventory',
    ])
@endsection
