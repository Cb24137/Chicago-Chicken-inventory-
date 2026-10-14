@extends('layouts.app')

@section('title', 'Add Storage Location')

@section('content')
    <x-page-header
        eyebrow="Food Storage & Inventory Location"
        title="Add Storage Location"
        description="Create a new freezer, refrigerator or dry storage area."
        :back="route('storage-locations.index')"
        back-label="Back to locations" />

    @include('partials.storage-form', [
        'storageLocation' => new \App\Models\StorageLocation(),
        'action' => route('storage-locations.store'),
        'method' => 'POST',
        'submitLabel' => 'Save Location',
    ])
@endsection
