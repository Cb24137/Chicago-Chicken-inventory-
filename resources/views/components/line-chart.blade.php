@props([
    // [['label' => 'May', 'full_label' => 'May 2026', 'value' => 12.5, 'emphasis' => true|false], ...]
    // When some points have 'emphasis' => false, the emphasised months are shaded
    'points' => [],
    'caption' => 'Chart',
    'prefix' => '',
    'unit' => null,
    'decimals' => 0,
    'periodHeading' => 'Month',
    'valueHeading' => 'Value',
])

@use('App\Support\ChartScale')

@php
    $count = count($points);
    $values = array_map(fn ($point) => (float) $point['value'], $points);
    $max = $values ? max($values) : 0;
    ['step' => $step, 'max' => $axisMax, 'ticks' => $tickCount] = ChartScale::for($max, $decimals);

    $exact = fn ($value) => $prefix . number_format($value, $decimals)
        . ($unit ? ' ' . \Illuminate\Support\Str::plural($unit, $value) : '');

    // Each point sits in the middle of an equal-width slot (as the columns do)
    $x = fn (int $index) => $count > 0 ? round(($index + 0.5) / $count * 100, 3) : 0;
    $y = fn (float $value) => $axisMax > 0 ? round($value / $axisMax * 100, 3) : 0;

    $line = collect($points)->map(fn ($point, $index) => $x($index) . ',' . (100 - $y((float) $point['value'])))->implode(' ');
    $area = $count > 1 ? $x(0) . ',100 ' . $line . ' ' . $x($count - 1) . ',100' : '';

    // Shade the selected period when the chart also shows months around it
    $emphasised = array_keys(array_filter($points, fn ($point) => $point['emphasis'] ?? true));
    $band = $emphasised && count($emphasised) < $count
        ? ['left' => round(min($emphasised) / $count * 100, 3), 'width' => round((max($emphasised) - min($emphasised) + 1) / $count * 100, 3)]
        : null;

    // Only the highest point gets a direct label
    $maxIndex = $max > 0 ? array_search($max, $values, true) : null;
@endphp

<figure class="ccc-chart">
    <div class="ccc-colchart ccc-linechart">
        <div class="ccc-colchart-yaxis" aria-hidden="true">
            @for ($i = 0; $i <= $tickCount; $i++)
                <span style="bottom: {{ round($i / $tickCount * 100, 3) }}%">{{ ChartScale::short($i * $step) }}</span>
            @endfor
        </div>

        <div class="ccc-colchart-plot">
            @if ($band)
                <span class="ccc-chart-band" style="left: {{ $band['left'] }}%; width: {{ $band['width'] }}%" aria-hidden="true"></span>
            @endif

            @for ($i = 1; $i <= $tickCount; $i++)
                <span class="ccc-colchart-gridline" style="bottom: {{ round($i / $tickCount * 100, 3) }}%"></span>
            @endfor

            @if ($count > 1)
                <svg class="ccc-linechart-svg" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true" focusable="false">
                    <polygon class="ccc-linechart-area" points="{{ $area }}" />
                    <polyline class="ccc-linechart-line" points="{{ $line }}" vector-effect="non-scaling-stroke" />
                </svg>
            @endif

            <div class="ccc-linechart-points" role="group" aria-label="{{ $caption }}">
                @foreach ($points as $index => $point)
                    @php $bottom = $y((float) $point['value']); @endphp

                    <span class="ccc-linechart-point {{ ($point['emphasis'] ?? true) ? '' : 'is-muted' }}"
                          tabindex="0"
                          role="img"
                          aria-label="{{ $point['full_label'] }}: {{ $exact($point['value']) }}"
                          data-tip-value="{{ $exact($point['value']) }}"
                          data-tip-label="{{ $point['full_label'] }}"
                          style="left: {{ $x($index) }}%; bottom: {{ $bottom }}%">
                        <span class="ccc-linechart-dot" data-tip-anchor></span>
                    </span>

                    @if ($index === $maxIndex)
                        <span class="ccc-linechart-value" style="left: {{ $x($index) }}%; bottom: {{ $bottom }}%" aria-hidden="true">{{ $prefix }}{{ ChartScale::short($point['value']) }}</span>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="ccc-colchart-xaxis" aria-hidden="true">
            @foreach ($points as $point)
                <span>{{ $point['label'] }}</span>
            @endforeach
        </div>
    </div>

    <details class="ccc-chart-table">
        <summary>View data table</summary>
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ $periodHeading }}</th>
                    <th scope="col">{{ $valueHeading }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($points as $point)
                    <tr>
                        <td>{{ $point['full_label'] }}</td>
                        <td>{{ $exact($point['value']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </details>
</figure>
