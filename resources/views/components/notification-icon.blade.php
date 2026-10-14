@props(['notification'])

@php
    $icon = [
        'stock' => 'alert',
        'expiry' => 'expiry',
        'purchase' => 'purchase',
        'waste' => 'waste',
        'staff' => 'staff',
    ][$notification->data['category'] ?? ''] ?? 'bell';

    $severity = $notification->data['severity'] ?? 'info';
@endphp

<span {{ $attributes->class(['ccc-note-icon', 'is-' . $severity]) }}>
    <x-icon :name="$icon" />
</span>
