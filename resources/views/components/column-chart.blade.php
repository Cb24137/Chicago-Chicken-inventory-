@props([
    // [['label' => 'May', 'full_label' => 'May 2026', 'value' => 120.5, 'emphasis' => true|false], ...]
    // Points with 'emphasis' => false are drawn lighter (context outside the selected period)
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
    $values = array_map(fn ($point) => (float) $point['value'], $points);
    $max = $values ? max($values) : 0;
    ['step' => $step, 'max' => $axisMax, 'ticks' => $tickCount] = ChartScale::for($max, $decimals);

    $exact = fn ($value) => $prefix . number_format($value, $decimals)
        . ($unit ? ' ' . \Illuminate\Support\Str::plural($unit, $value) : '');

    // Only the highest column gets a direct label; the rest use the
    // axis, the tooltip and the data table.
    $maxIndex = $max > 0 ? array_search($max, $values, true) : null;
@endphp

<figure class="ccc-chart">
    <div class="ccc-colchart">
        <div class="ccc-colchart-yaxis" aria-hidden="true">
            @for ($i = 0; $i <= $tickCount; $i++)
                <span style="bottom: {{ round($i / $tickCount * 100, 3) }}%">{{ ChartScale::short($i * $step) }}</span>
            @endfor
        </div>

        <div class="ccc-colchart-plot">
            @for ($i = 1; $i <= $tickCount; $i++)
                <span class="ccc-colchart-gridline" style="bottom: {{ round($i / $tickCount * 100, 3) }}%"></span>
            @endfor

            <div class="ccc-colchart-cols" role="group" aria-label="{{ $caption }}">
                @foreach ($points as $index => $point)
                    @php $height = $axisMax > 0 ? round($point['value'] / $axisMax * 100, 2) : 0; @endphp

                    <div class="ccc-colchart-col"
                         tabindex="0"
                         role="img"
                         aria-label="{{ $point['full_label'] }}: {{ $exact($point['value']) }}"
                         data-tip-value="{{ $exact($point['value']) }}"
                         data-tip-label="{{ $point['full_label'] }}">
                        <span class="ccc-colchart-bar {{ ($point['emphasis'] ?? true) ? '' : 'is-muted' }}" data-tip-anchor style="height: {{ $height }}%"></span>

                        @if ($index === $maxIndex)
                            <span class="ccc-colchart-value" style="bottom: {{ $height }}%">{{ $prefix }}{{ ChartScale::short($point['value']) }}</span>
                        @endif
                    </div>
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
