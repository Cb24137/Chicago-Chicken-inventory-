@extends('layouts.app')

@section('title', 'Edit ' . $purchase->po_number)

@section('content')
    <x-page-header
        eyebrow="Purchase Management"
        :title="'Edit ' . $purchase->po_number"
        description="Update this purchase order. The total cost is recalculated when the quantity or unit price changes."
        :back="route('purchases.show', $purchase)"
        back-label="Back to order details" />

    @include('purchases._form', [
        'action' => route('purchases.update', $purchase),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
