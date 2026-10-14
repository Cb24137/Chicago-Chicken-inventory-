@use('App\Reports\ReportFormat')
@php
    // Bar colours: one brand colour, plus the status colours for stock levels
    $tones = ['good' => '#0ca30c', 'warning' => '#fab219', 'critical' => '#d03b3b'];
    $summaryTones = ['warning' => '#fab219', 'danger' => '#d03b3b'];
    $filtersText = $filterSummary
        ? collect($filterSummary)->map(fn ($value, $name) => "{$name}: {$value}")->implode(' · ')
        : 'None';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{{ $report['title'] }} | Chicago Chicken City</title>
    <style>
        @page { margin: 28px 32px 46px; }

        body {
            margin: 0;
            color: #4a2e1c;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.4;
        }

        table { border-collapse: collapse; }

        .footer {
            position: fixed;
            right: 0;
            bottom: -30px;
            left: 0;
            padding-top: 6px;
            border-top: 1px solid #ece2d7;
            color: #7a6352;
            font-size: 8px; /* 6pt, the size of the page number added after rendering */
        }

        .footer table { width: 100%; }

        .head { width: 100%; margin-bottom: 14px; border-bottom: 2px solid #df650a; }
        .head td { padding: 0 0 10px; vertical-align: middle; }
        .head .logo { width: 74px; }
        .head .logo img { height: 54px; }
        .company { color: #a34a07; font-size: 8px; font-weight: bold; letter-spacing: 1.5px; text-transform: uppercase; }
        .title { margin: 2px 0; color: #42210b; font-size: 18px; font-weight: bold; }
        .period { color: #7a6352; font-size: 9.5px; }
        .meta { width: 250px; color: #7a6352; font-size: 8px; text-align: right; }
        .meta strong { color: #42210b; }

        .summary { width: 100%; margin-bottom: 14px; }
        .summary td { width: 25%; padding: 0 8px 0 0; vertical-align: top; }
        .summary td:last-child { padding-right: 0; }
        .tile { padding: 8px 10px; border: 1px solid #ece2d7; border-left: 3px solid #df650a; background: #fffaf4; }
        .tile-label { color: #7a6352; font-size: 7.5px; font-weight: bold; text-transform: uppercase; letter-spacing: .5px; }
        .tile-value { margin: 2px 0; color: #42210b; font-size: 14px; font-weight: bold; }
        .tile-meta { color: #7a6352; font-size: 7.5px; }

        .charts { width: 100%; margin-bottom: 6px; }
        .charts > tbody > tr > td { padding: 0 12px 0 0; vertical-align: top; }
        .charts > tbody > tr > td:last-child { padding-right: 0; }
        .bars { width: 100%; }
        .bars td { padding: 2px 0; vertical-align: middle; }
        .bars .label { width: 30%; padding-right: 8px; color: #4a2e1c; }
        .bars .value { width: 22%; padding-left: 8px; color: #42210b; font-weight: bold; text-align: right; }
        .bar { height: 8px; background: #df650a; }

        h2 {
            margin: 14px 0 6px;
            color: #42210b;
            font-size: 11px;
        }

        h2 span { color: #7a6352; font-size: 8px; font-weight: normal; }

        .data { width: 100%; }
        .data thead { display: table-header-group; }
        .data tr { page-break-inside: avoid; }
        .data th {
            padding: 5px 6px;
            border-bottom: 1px solid #dccfc1;
            background: #fff1e2;
            color: #42210b;
            font-size: 8px;
            text-align: left;
        }
        .data td { padding: 4px 6px; border-bottom: 1px solid #f3ece5; }
        .data tr:nth-child(even) td { background: #fcfaf7; }
        .num { text-align: right !important; white-space: nowrap; }
        .nowrap { white-space: nowrap; }
        .empty { color: #7a6352; font-style: italic; }

        .notes { margin-top: 14px; padding: 8px 10px; border: 1px solid #ece2d7; background: #fffaf4; color: #5a3a27; font-size: 8px; }
        .notes strong { color: #42210b; }
        .notes ul { margin: 4px 0 0; padding-left: 14px; }
    </style>
</head>
<body>
    {{-- "Page X of Y" is added on the right by ReportController after rendering --}}
    <div class="footer">
        <table>
            <tr>
                <td>Chicago Chicken City · {{ $report['title'] }} · Generated {{ $generatedAt->format('d M Y, g:i A') }} · For internal use</td>
            </tr>
        </table>
    </div>

    <table class="head">
        <tr>
            <td class="logo"><img src="{{ public_path('images/chicago-logo-transparent.png') }}" alt="Chicago Chicken City"></td>
            <td>
                <div class="company">Chicago Chicken City</div>
                <div class="title">{{ $report['title'] }}</div>
                <div class="period">{{ $report['period'] }}</div>
            </td>
            <td class="meta">
                <strong>Generated:</strong> {{ $generatedAt->format('d M Y, g:i A') }}<br>
                <strong>Prepared by:</strong> {{ $generatedBy }}<br>
                <strong>Filters:</strong> {{ $filtersText }}
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            @foreach ($report['summary'] as $tile)
                <td>
                    <div class="tile" style="border-left-color: {{ $summaryTones[$tile['tone'] ?? ''] ?? '#df650a' }};">
                        <div class="tile-label">{{ $tile['label'] }}</div>
                        <div class="tile-value">{{ $tile['value'] }}</div>
                        <div class="tile-meta">{{ $tile['meta'] }}</div>
                    </div>
                </td>
            @endforeach
        </tr>
    </table>

    @if ($report['charts'])
        <table class="charts">
            <tr>
                @foreach ($report['charts'] as $chart)
                    @php
                        $rows = $chart['points'] ?? $chart['items'];
                        $max = max(array_merge([0], array_map(fn ($row) => (float) $row['value'], $rows)));
                        $prefix = $chart['prefix'] ?? '';
                        $decimals = $chart['decimals'] ?? 0;
                    @endphp
                    <td style="width: {{ round(100 / count($report['charts']), 2) }}%;">
                        <h2>{{ $chart['title'] }}</h2>

                        @if (! ReportFormat::chartHasData($chart))
                            <p class="empty">No data for the selected period and filters.</p>
                        @else
                            <table class="bars">
                                @foreach ($rows as $row)
                                    @php $width = $max > 0 ? round((float) $row['value'] / $max * 100, 2) : 0; @endphp
                                    <tr>
                                        <td class="label">{{ $row['full_label'] ?? $row['label'] }}</td>
                                        <td>
                                            @if ($width > 0)
                                                <div class="bar" style="width: {{ $width }}%; background: {{ $tones[$row['tone'] ?? ''] ?? '#df650a' }};"></div>
                                            @endif
                                        </td>
                                        <td class="value">{{ $prefix }}{{ number_format((float) $row['value'], $decimals) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        @endif
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    @foreach ($report['tables'] as $table)
        @php $formats = array_values($table['columns']); @endphp

        <h2>
            {{ $table['title'] }}
            <span>· {{ count($table['rows']) }} {{ \Illuminate\Support\Str::plural('row', count($table['rows'])) }}</span>
        </h2>

        @if (empty($table['rows']))
            <p class="empty">{{ $table['empty'] }}</p>
        @else
            <table class="data">
                <thead>
                    <tr>
                        @foreach ($table['columns'] as $heading => $format)
                            <th class="{{ ReportFormat::cellClass($format, forPdf: true) }}">{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($table['rows'] as $row)
                        <tr>
                            @foreach ($formats as $index => $format)
                                <td class="{{ ReportFormat::cellClass($format, forPdf: true) }}">{{ ReportFormat::cell($row[$index] ?? null, $format) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach

    @if ($report['notes'])
        <div class="notes">
            <strong>About this report</strong>
            <ul>
                @foreach ($report['notes'] as $note)
                    <li>{{ $note }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>
</html>
