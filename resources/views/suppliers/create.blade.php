@extends('layouts.app')

@section('title', 'Add Supplier')

@section('content')
    <x-page-header
        eyebrow="Supplier Management"
        title="Add New Supplier"
        description="Enter supplier information below."
        :back="route('suppliers.index')"
        back-label="Back to suppliers" />

    @include('suppliers._form', [
        'supplier' => new \App\Models\Supplier(),
        'action' => route('suppliers.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Supplier',
    ])
@endsection
