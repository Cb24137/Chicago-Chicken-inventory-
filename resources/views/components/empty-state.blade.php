@props([
    'title',
    'icon' => 'inventory',
    'compact' => false,
])

<div {{ $attributes->class(['ccc-empty', 'is-compact' => $compact]) }}>
    <span class="ccc-empty-icon">
        <x-icon :name="$icon" />
    </span>

    <h3>{{ $title }}</h3>

    @if ($slot->isNotEmpty())
        <p>{{ $slot }}</p>
    @endif

    @isset($action)
        <div class="ccc-empty-action">{{ $action }}</div>
    @endisset
</div>
