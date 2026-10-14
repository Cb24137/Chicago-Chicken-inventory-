@extends('layouts.app')

@section('title', 'Add Waste Record')

@section('content')
    <x-page-header
        eyebrow="Waste & Expiry Management"
        title="Add Waste Record"
        description="Record expired, spoiled, damaged, or wasted inventory."
        :back="route('waste-expiry.index')"
        back-label="Back to waste records" />

    @include('waste-expiry._form', [
        'wasteExpiryRecord' => $wasteExpiryRecord ?? new \App\Models\WasteExpiryRecord(),
        'action' => route('waste-expiry.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Waste Record',
    ])
@endsection
