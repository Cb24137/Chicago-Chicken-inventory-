@props([
    // Percentage change, or null when the comparison period had nothing to compare with
    'change' => null,
    // The comparison period, e.g. "1–14 Sep 2026"
    'against',
    // How a rise reads: 'neutral' (e.g. spending), 'bad' (e.g. waste) or 'good'
    'increase' => 'neutral',
])

@php
    $direction = $change === null || $change == 0 ? 'flat' : ($change > 0 ? 'up' : 'down');

    $tone = match (true) {
        $direction === 'flat' || $increase === 'neutral' => 'neutral',
        ($direction === 'up') === ($increase === 'good') => 'good',
        default => 'warning',
    };
@endphp

<span {{ $attributes->class(['ccc-delta', 'is-' . $tone]) }}>
    @if ($change === null)
        Nothing to compare in {{ $against }}
    @elseif ($direction === 'flat')
        No change vs {{ $against }}
    @else
        <x-icon :name="$direction === 'up' ? 'arrow-up' : 'arrow-down'" />
        <span class="ccc-visually-hidden">{{ $direction === 'up' ? 'Up' : 'Down' }}</span>
        {{ number_format(abs($change), 1) }}% vs {{ $against }}
    @endif
</span>
