@extends('layouts.app')

@section('title', 'Edit Inventory')

@section('content')
    <x-page-header
        eyebrow="Food & Beverage Inventory"
        title="Edit Inventory Item"
        description="Update food or beverage inventory details."
        :back="route('inventory.index')"
        back-label="Back to inventory" />

    @include('inventory._form', [
        'action' => route('inventory.update', $inventoryItem->id),
        'method' => 'PUT',
        'submitLabel' => 'Update Inventory',
    ])
@endsection
