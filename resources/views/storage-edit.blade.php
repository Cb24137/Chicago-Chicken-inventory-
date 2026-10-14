@extends('layouts.app')

@section('title', 'Edit Storage Location')

@section('content')
    <x-page-header
        eyebrow="Food Storage & Inventory Location"
        title="Edit Storage Location"
        description="Update storage location information."
        :back="route('storage-locations.index')"
        back-label="Back to locations" />

    @include('partials.storage-form', [
        'action' => route('storage-locations.update', $storageLocation),
        'method' => 'PUT',
        'submitLabel' => 'Save Changes',
    ])
@endsection
