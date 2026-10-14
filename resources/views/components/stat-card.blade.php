@props([
    'label',
    'value',
    'icon',
    'meta' => null,
    'href' => null,
    'tone' => null,
    'flag' => null,
])

@php $tag = $href ? 'a' : 'div'; @endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['ccc-stat', 'is-' . $tone => $tone]) }}>
    <span class="ccc-stat-icon">
        <x-icon :name="$icon" />
    </span>

    <span class="ccc-stat-body">
        <span class="ccc-stat-label">{{ $label }}</span>
        <span class="ccc-stat-value">{{ $value }}</span>

        @if ($meta)
            <span class="ccc-stat-meta">{{ $meta }}</span>
        @endif

        @if ($flag)
            <span class="ccc-stat-flag">
                <x-icon name="alert" />
                {{ $flag }}
            </span>
        @endif
    </span>
</{{ $tag }}>
