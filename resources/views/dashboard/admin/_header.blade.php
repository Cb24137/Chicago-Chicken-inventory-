{{-- Section 1: executive overview and the reporting period --}}
@php
    $isCustom = $period->key === 'custom';
@endphp

<section class="ccc-exec-hero" aria-labelledby="exec-title">
    <div class="ccc-exec-hero-text">
        <p class="ccc-exec-eyebrow">Executive dashboard</p>
        <h1 id="exec-title">Chicago Chicken City — Management Overview</h1>
        <p>Monitor purchasing expenses, inventory performance, supplier activity, and operational efficiency.</p>
        <p class="ccc-exec-date"><x-icon name="calendar" /> {{ now()->format('l, j F Y') }}</p>
    </div>

    <div class="ccc-exec-period">
        <p class="ccc-exec-period-title" id="period-title">Reporting period</p>

        <div class="ccc-period-tabs" role="group" aria-labelledby="period-title">
            @foreach (\App\Support\ReportingPeriod::PRESETS as $key => $label)
                <a href="{{ route('dashboard', ['period' => $key]) }}"
                   class="ccc-period-tab {{ $period->key === $key ? 'is-active' : '' }}"
                   @if ($period->key === $key) aria-current="true" @endif>
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <details class="ccc-period-custom" @if ($isCustom || $errors->hasAny(['period', 'from', 'to'])) open @endif>
            <summary class="ccc-period-tab {{ $isCustom ? 'is-active' : '' }}">
                <x-icon name="calendar" /> Custom range
            </summary>

            <form method="GET" action="{{ route('dashboard') }}" class="ccc-period-form">
                <input type="hidden" name="period" value="custom">

                <div>
                    <label for="period-from">From</label>
                    <input type="date" id="period-from" name="from" required
                           value="{{ old('from', $period->from->toDateString()) }}">
                </div>

                <div>
                    <label for="period-to">To</label>
                    <input type="date" id="period-to" name="to" required
                           value="{{ old('to', $period->to->toDateString()) }}">
                </div>

                <button type="submit" class="ccc-btn ccc-btn-primary ccc-btn-sm">Apply</button>
            </form>
        </details>

        <p class="ccc-exec-range">
            <strong>{{ $period->name() }} · {{ $period->label() }}</strong>
            <span>Compared with {{ $previous->label() }}</span>
        </p>
    </div>
</section>
