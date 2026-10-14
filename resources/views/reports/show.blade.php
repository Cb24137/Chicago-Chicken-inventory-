@extends('layouts.app')

@use('App\Reports\ReportFormat')

@section('title', $report['title'])

@section('content')
    @php
        // Date-free reports (inventory, storage) show the current state, so their links drop from/to
        $query = $report['uses_dates']
            ? $filters->query()
            : \Illuminate\Support\Arr::except($filters->query(), ['from', 'to']);
        $tabQuery = request()->only(['from', 'to']);
        $isFiltered = count(request()->query()) > 0;
    @endphp

    <div class="ccc-no-print">
        <x-page-header
            eyebrow="Reports & Analytics"
            :title="$report['title']"
            description="Preview a report, then export it as CSV (opens in Excel) or PDF, or print it. Figures are calculated from the live database each time.">
            <x-slot:actions>
                <a href="{{ route('reports.export', ['report' => $reportKey, 'format' => 'csv'] + $query) }}"
                   class="ccc-btn ccc-btn-secondary"
                   data-loading-text="Preparing CSV…">
                    <x-icon name="download" /> <span data-label>Export CSV</span>
                </a>
                <a href="{{ route('reports.export', ['report' => $reportKey, 'format' => 'pdf'] + $query) }}"
                   class="ccc-btn ccc-btn-secondary"
                   data-loading-text="Preparing PDF…">
                    <x-icon name="file" /> <span data-label>Export PDF</span>
                </a>
                <button type="button" class="ccc-btn ccc-btn-primary" data-print>
                    <x-icon name="printer" /> Print
                </button>
            </x-slot:actions>
        </x-page-header>

        {{-- ========== REPORT TABS ========== --}}
        <div class="ccc-tabs-row">
            <nav class="ccc-tabs" aria-label="Reports">
                @foreach ($reports as $key => $label)
                    <a href="{{ route('reports.show', ['report' => $key] + $tabQuery) }}"
                       class="ccc-tab {{ $key === $reportKey ? 'is-active' : '' }}"
                       @if ($key === $reportKey) aria-current="page" @endif>
                        {{ $label }}
                    </a>
                @endforeach
            </nav>

            @unless (auth()->user()->isAdmin())
                <p class="ccc-tabs-note">
                    <x-icon name="lock" /> Purchase, supplier and operational summary reports are available to administrators.
                </p>
            @endunless
        </div>

        {{-- ========== FILTERS ========== --}}
        <section class="ccc-card ccc-card-clip ccc-mb" aria-labelledby="report-filters-title">
            <h2 id="report-filters-title" class="ccc-visually-hidden">Report filters</h2>

            @if ($report['uses_dates'])
                <div class="ccc-presets">
                    <span class="ccc-presets-label"><x-icon name="calendar" /> Quick ranges</span>
                    @foreach ($presets as $label => [$start, $end])
                        @php $isActive = $filters->from->isSameDay($start) && $filters->to->isSameDay($end); @endphp
                        <a href="{{ route('reports.show', ['report' => $reportKey] + array_merge($query, ['from' => $start->toDateString(), 'to' => $end->toDateString()])) }}"
                           class="ccc-pill {{ $isActive ? 'is-active' : '' }}"
                           @if ($isActive) aria-current="true" @endif>
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
            @endif

            <form method="GET" action="{{ route('reports.show', $reportKey) }}" class="ccc-filters">
                @if ($report['uses_dates'])
                    <div class="ccc-field is-date">
                        <label for="from" class="ccc-label">From date</label>
                        <input type="date" id="from" name="from" required
                               value="{{ $filters->from->toDateString() }}"
                               class="ccc-input @error('from') is-invalid @enderror">
                        <x-field-error name="from" />
                    </div>

                    <div class="ccc-field is-date">
                        <label for="to" class="ccc-label">To date</label>
                        <input type="date" id="to" name="to" required
                               value="{{ $filters->to->toDateString() }}"
                               class="ccc-input @error('to') is-invalid @enderror">
                        <x-field-error name="to" />
                    </div>
                @endif

                @switch($reportKey)
                    @case('inventory')
                        <div class="ccc-field">
                            <label for="category" class="ccc-label">Category</label>
                            <select id="category" name="category" class="ccc-input">
                                <option value="">All categories</option>
                                @foreach (\App\Models\InventoryItem::CATEGORIES as $option)
                                    <option value="{{ $option }}" @selected($filters->get('category') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ccc-field">
                            <label for="stock_status" class="ccc-label">Stock status</label>
                            <select id="stock_status" name="stock_status" class="ccc-input">
                                <option value="">All stock levels</option>
                                @foreach (\App\Models\InventoryItem::STOCK_STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($filters->get('stock_status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @break

                    @case('purchases')
                        <div class="ccc-field">
                            <label for="supplier_id" class="ccc-label">Supplier</label>
                            <select id="supplier_id" name="supplier_id" class="ccc-input">
                                <option value="">All suppliers</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" @selected((int) $filters->get('supplier_id') === $supplier->id)>{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ccc-field">
                            <label for="status" class="ccc-label">Order status</label>
                            <select id="status" name="status" class="ccc-input">
                                <option value="">All statuses</option>
                                @foreach (\App\Models\Purchase::STATUSES as $value => $label)
                                    <option value="{{ $value }}" @selected($filters->get('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @break

                    @case('storage')
                        <div class="ccc-field">
                            <label for="type" class="ccc-label">Storage type</label>
                            <select id="type" name="type" class="ccc-input">
                                <option value="">All types</option>
                                @foreach (\App\Reports\StorageReport::TYPES as $option)
                                    <option value="{{ $option }}" @selected($filters->get('type') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="ccc-field">
                            <label for="location_status" class="ccc-label">Status</label>
                            <select id="location_status" name="location_status" class="ccc-input">
                                <option value="">Active and inactive</option>
                                <option value="active" @selected($filters->get('location_status') === 'active')>Active</option>
                                <option value="inactive" @selected($filters->get('location_status') === 'inactive')>Inactive</option>
                            </select>
                        </div>
                        @break

                    @case('waste')
                        <div class="ccc-field">
                            <label for="reason" class="ccc-label">Waste reason</label>
                            <select id="reason" name="reason" class="ccc-input">
                                <option value="">All reasons</option>
                                @foreach (\App\Reports\WasteReport::REASONS as $value => $label)
                                    <option value="{{ $value }}" @selected($filters->get('reason') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        @break
                @endswitch

                <div class="ccc-filter-actions">
                    <button type="submit" class="ccc-btn ccc-btn-primary">
                        <x-icon name="filter" /> Apply filters
                    </button>
                    @if ($isFiltered)
                        <a href="{{ route('reports.show', $reportKey) }}" class="ccc-btn ccc-btn-secondary">Reset</a>
                    @endif
                </div>
            </form>
        </section>
    </div>

    {{-- ========== REPORT (this part is printed) ========== --}}
    <article class="ccc-card ccc-report" aria-labelledby="report-title">
        <header class="ccc-report-head">
            <img src="{{ asset('images/chicago-logo-transparent.png') }}" alt="Chicago Chicken City" class="ccc-report-logo">

            <div class="ccc-report-heading">
                <p class="ccc-report-company">Chicago Chicken City · Food &amp; Beverage Inventory</p>
                <h2 id="report-title" class="ccc-report-title">{{ $report['title'] }}</h2>
                <p class="ccc-report-period"><x-icon name="calendar" /> {{ $report['period'] }}</p>
            </div>

            <dl class="ccc-report-meta">
                <div>
                    <dt>Generated</dt>
                    <dd>{{ $generatedAt->format('d M Y, g:i A') }}</dd>
                </div>
                <div>
                    <dt>Prepared by</dt>
                    <dd>{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt>Filters</dt>
                    <dd>
                        {{ $filterSummary
                            ? collect($filterSummary)->map(fn ($value, $name) => "{$name}: {$value}")->implode(' · ')
                            : 'None' }}
                    </dd>
                </div>
            </dl>
        </header>

        <div class="ccc-report-body">
            <div class="ccc-report-summary">
                @foreach ($report['summary'] as $tile)
                    <div class="ccc-report-tile {{ ! empty($tile['tone']) ? 'is-' . $tile['tone'] : '' }}">
                        <span class="ccc-report-tile-label">{{ $tile['label'] }}</span>
                        <strong class="ccc-report-tile-value">{{ $tile['value'] }}</strong>
                        <span class="ccc-report-tile-meta">{{ $tile['meta'] }}</span>
                    </div>
                @endforeach
            </div>

            @if ($report['charts'])
                <div class="ccc-report-charts {{ count($report['charts']) === 1 ? 'is-single' : '' }}">
                    @foreach ($report['charts'] as $chart)
                        <section class="ccc-report-section">
                            <h3 class="ccc-report-section-title">{{ $chart['title'] }}</h3>

                            @if (! ReportFormat::chartHasData($chart))
                                <p class="ccc-muted">No data for the selected period and filters.</p>
                            @elseif ($chart['type'] === 'columns')
                                <x-column-chart
                                    :points="$chart['points']"
                                    :caption="$chart['title']"
                                    :prefix="$chart['prefix'] ?? ''"
                                    :unit="$chart['unit'] ?? null"
                                    :decimals="$chart['decimals'] ?? 0"
                                    :period-heading="$chart['label_heading'] ?? 'Month'"
                                    :value-heading="$chart['value_heading'] ?? 'Value'" />
                            @else
                                <x-bar-chart
                                    :items="$chart['items']"
                                    :caption="$chart['title']"
                                    :prefix="$chart['prefix'] ?? ''"
                                    :unit="$chart['unit'] ?? null"
                                    :decimals="$chart['decimals'] ?? 0"
                                    :category-heading="$chart['label_heading'] ?? 'Category'"
                                    :value-heading="$chart['value_heading'] ?? 'Value'" />
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif

            @foreach ($report['tables'] as $table)
                @php $formats = array_values($table['columns']); @endphp

                <section class="ccc-report-section">
                    <div class="ccc-report-section-head">
                        <h3 class="ccc-report-section-title">{{ $table['title'] }}</h3>
                        <span class="ccc-muted">{{ count($table['rows']) }} {{ \Illuminate\Support\Str::plural('row', count($table['rows'])) }}</span>
                    </div>

                    @if (empty($table['rows']))
                        <p class="ccc-muted">{{ $table['empty'] }}</p>
                    @else
                        <div class="ccc-table-wrap">
                            <table class="ccc-table is-compact ccc-report-table">
                                <thead>
                                    <tr>
                                        @foreach ($table['columns'] as $heading => $format)
                                            <th scope="col" class="{{ ReportFormat::cellClass($format) }}">{{ $heading }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($table['rows'] as $row)
                                        <tr>
                                            @foreach ($formats as $index => $format)
                                                <td class="{{ ReportFormat::cellClass($format) }}">{{ ReportFormat::cell($row[$index] ?? null, $format) }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach

            @if ($report['notes'])
                <section class="ccc-report-notes">
                    <h3><x-icon name="info" /> About this report</h3>
                    <ul>
                        @foreach ($report['notes'] as $note)
                            <li>{{ $note }}</li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>
    </article>
@endsection
