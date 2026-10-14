@extends('layouts.app')

@section('title', 'Waste Record Details')

@section('content')
    <x-page-header
        eyebrow="Waste & Expiry Management"
        title="Waste Record Details"
        description="Review the recorded waste or expiry information."
        :back="route('waste-expiry.index')"
        back-label="Back to waste records">
        <x-slot:actions>
            <a href="{{ route('waste-expiry.edit', $wasteExpiryRecord) }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="edit" /> Edit Record
            </a>
        </x-slot:actions>
    </x-page-header>

    <section class="ccc-card ccc-form-card" aria-labelledby="waste-record-title">
        <div class="ccc-card-header" style="padding-bottom: 14px;">
            <h2 id="waste-record-title" class="ccc-card-title">
                <x-icon name="waste" /> {{ $wasteExpiryRecord->item_name }}
            </h2>
            <span class="ccc-badge {{ $wasteExpiryRecord->reason === 'expired' ? 'is-danger' : ($wasteExpiryRecord->reason === 'other' ? 'is-neutral' : 'is-warning') }}">
                {{ ucfirst($wasteExpiryRecord->reason) }}
            </span>
        </div>

        <dl class="ccc-details">
            <div>
                <dt>Item name</dt>
                <dd>{{ $wasteExpiryRecord->item_name }}</dd>
            </div>
            <div>
                <dt>Quantity</dt>
                <dd>{{ number_format((float) $wasteExpiryRecord->quantity, 2) }} {{ $wasteExpiryRecord->unit }}</dd>
            </div>
            <div>
                <dt>Waste reason</dt>
                <dd>{{ ucfirst($wasteExpiryRecord->reason) }}</dd>
            </div>
            <div>
                <dt>Expiry date</dt>
                <dd>{{ $wasteExpiryRecord->expiry_date ? $wasteExpiryRecord->expiry_date->format('d M Y') : 'Not provided' }}</dd>
            </div>
            <div>
                <dt>Record date</dt>
                <dd>{{ $wasteExpiryRecord->record_date->format('d M Y') }}</dd>
            </div>
            <div>
                <dt>Recorded on</dt>
                <dd>{{ $wasteExpiryRecord->created_at?->format('d M Y, g:i A') ?? '—' }}</dd>
            </div>
            <div class="is-full">
                <dt>Description / remarks</dt>
                <dd class="is-plain">{{ $wasteExpiryRecord->description ?: 'No description provided.' }}</dd>
            </div>
        </dl>

        <div class="ccc-form-actions">
            <a href="{{ route('waste-expiry.index') }}" class="ccc-btn ccc-btn-secondary">Back</a>
            <a href="{{ route('waste-expiry.edit', $wasteExpiryRecord) }}" class="ccc-btn ccc-btn-primary">
                <x-icon name="edit" /> Edit Record
            </a>
        </div>
    </section>
@endsection
