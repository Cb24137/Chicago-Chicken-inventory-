@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
    <x-page-header
        eyebrow="Supplier Management"
        title="Edit Supplier"
        :description="'Update the contact details for ' . $supplier->name . '.'"
        :back="route('suppliers.index')"
        back-label="Back to suppliers" />

    @include('suppliers._form', [
        'action' => route('suppliers.update', $supplier),
        'method' => 'PUT',
        'submitLabel' => 'Update Supplier',
    ])
@endsection
