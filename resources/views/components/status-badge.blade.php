@props(['status'])

@php
    // Purchase order status => [badge colour, icon]
    [$tone, $icon] = [
        'pending' => ['warning', 'clock'],
        'ordered' => ['info', 'purchase'],
        'received' => ['success', 'check-circle'],
        'cancelled' => ['neutral', 'close'],
    ][$status] ?? ['neutral', 'dot'];
@endphp

<span {{ $attributes->class(['ccc-badge', 'is-' . $tone]) }}>
    <x-icon :name="$icon" />
    {{ \App\Models\Purchase::STATUSES[$status] ?? ucfirst((string) $status) }}
</span>
