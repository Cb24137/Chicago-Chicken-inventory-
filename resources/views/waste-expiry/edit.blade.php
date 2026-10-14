@extends('layouts.app')

@section('title', 'Edit Waste Record')

@section('content')
    <x-page-header
        eyebrow="Waste & Expiry Management"
        title="Edit Waste Record"
        description="Update the waste and expiry information."
        :back="route('waste-expiry.show', $wasteExpiryRecord)"
        back-label="Back to record details" />

    @include('waste-expiry._form', [
        'action' => route('waste-expiry.update', $wasteExpiryRecord),
        'method' => 'PUT',
        'submitLabel' => 'Update Waste Record',
    ])
@endsection
