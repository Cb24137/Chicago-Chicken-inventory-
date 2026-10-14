@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'back' => null,
    'backLabel' => 'Back',
])

<header class="ccc-page-header">
    <div>
        @if ($back)
            <a href="{{ $back }}" class="ccc-back-link">
                <x-icon name="arrow-left" />
                {{ $backLabel }}
            </a>
        @endif

        @if ($eyebrow)
            <p class="ccc-eyebrow">{{ $eyebrow }}</p>
        @endif

        <h1 class="ccc-page-title">{{ $title }}</h1>

        @if ($description)
            <p class="ccc-page-description">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="ccc-page-actions">
            {{ $actions }}
        </div>
    @endisset
</header>
