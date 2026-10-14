@props([
    // [['label' => 'Food', 'value' => 12, 'tone' => null|'good'|'warning'|'critical', 'note' => '42%'], ...]
    // 'note' (optional) is shown after the value, e.g. a share of the total
    'items' => [],
    'caption' => 'Chart',
    'unit' => null,
    'prefix' => '',
    'decimals' => 0,
    'categoryHeading' => 'Category',
    'valueHeading' => 'Value',
])

@php
    $max = max(array_merge([0], array_map(fn ($item) => (float) $item['value'], $items)));

    $value = fn ($amount) => $prefix . number_format((float) $amount, $decimals);
    $text = fn ($amount) => $value($amount)
        . ($unit ? ' ' . \Illuminate\Support\Str::plural($unit, $amount) : '');

    // Every bar starts on the same baseline, and the longest bar leaves
    // room for the widest value label after it (in character widths)
    $labelRoom = max(array_merge([1], array_map(
        fn ($item) => mb_strlen($value($item['value'])) + (isset($item['note']) ? mb_strlen($item['note']) + 2 : 0),
        $items
    ))) + 2;
@endphp

<figure class="ccc-chart">
    <div class="ccc-barlist" role="group" aria-label="{{ $caption }}" style="--ccc-bar-room: {{ $labelRoom }}ch">
        @foreach ($items as $item)
            @php $tone = $item['tone'] ?? null; @endphp

            <div class="ccc-barlist-row">
                <span class="ccc-barlist-label">{{ $item['label'] }}</span>

                <div class="ccc-barlist-track"
                     tabindex="0"
                     role="img"
                     aria-label="{{ $item['label'] }}: {{ $text($item['value']) }}{{ isset($item['note']) ? ' (' . $item['note'] . ')' : '' }}"
                     data-tip-value="{{ $text($item['value']) }}{{ isset($item['note']) ? ' · ' . $item['note'] : '' }}"
                     data-tip-label="{{ $item['label'] }}">
                    @if ((float) $item['value'] > 0)
                        <span class="ccc-barlist-bar {{ $tone ? 'is-' . $tone : '' }}"
                              data-tip-anchor
                              style="--ccc-bar-ratio: {{ round((float) $item['value'] / $max, 4) }}"></span>
                    @endif
                    <span class="ccc-barlist-value">{{ $value($item['value']) }}</span>
                    @isset($item['note'])
                        <span class="ccc-barlist-note">{{ $item['note'] }}</span>
                    @endisset
                </div>
            </div>
        @endforeach
    </div>

    <details class="ccc-chart-table">
        <summary>View data table</summary>
        <table>
            <thead>
                <tr>
                    <th scope="col">{{ $categoryHeading }}</th>
                    <th scope="col">{{ $valueHeading }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item['label'] }}</td>
                        <td>{{ $text($item['value']) }}{{ isset($item['note']) ? ' (' . $item['note'] . ')' : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </details>
</figure>
