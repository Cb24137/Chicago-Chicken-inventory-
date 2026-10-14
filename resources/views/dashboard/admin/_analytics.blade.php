{{-- Section 3: business analytics --}}
@php
    $money = fn (float $value) => 'RM ' . number_format($value, 2);
    $hasValues = fn (array $rows) => array_sum(array_map(fn ($row) => (float) $row['value'], $rows)) > 0;
    $categoryTotal = array_sum(array_column($charts['categoryValue'], 'value'));
    $supplierTotal = array_sum(array_column($charts['supplierSpend'], 'value'));
@endphp

<section class="ccc-section" aria-labelledby="analytics-title">
    <div class="ccc-section-head">
        <h2 id="analytics-title" class="ccc-section-title">Business analytics</h2>
        <p class="ccc-section-text">Monthly trends cover {{ $charts['window'] }}; the selected period is highlighted</p>
    </div>

    <div class="ccc-analytics-grid">
        <article class="ccc-card" aria-labelledby="chart-expenditure-title">
            <div class="ccc-card-header">
                <div>
                    <h3 id="chart-expenditure-title" class="ccc-card-title"><x-icon name="cash" /> Monthly purchase expenditure</h3>
                    <p class="ccc-card-subtitle">Received orders (RM), by purchase date</p>
                </div>
                <a href="{{ route('reports.show', ['report' => 'purchases'] + $period->reportQuery()) }}" class="ccc-card-link">Report <x-icon name="arrow-right" /></a>
            </div>
            <div class="ccc-card-body">
                <div class="ccc-chart-headline">
                    <strong>{{ $money($kpis['expenditure']['value']) }}</strong>
                    <span>in {{ $period->label() }}</span>
                </div>
                @if ($hasValues($charts['expenditure']))
                    <x-column-chart
                        :points="$charts['expenditure']"
                        caption="Purchase expenditure by month in Malaysian ringgit"
                        prefix="RM "
                        :decimals="2"
                        value-heading="Expenditure (RM)" />
                @else
                    <x-empty-state icon="chart" title="No received orders yet" compact>
                        Received purchase orders from {{ $charts['window'] }} will appear here.
                    </x-empty-state>
                @endif
            </div>
        </article>

        <article class="ccc-card" aria-labelledby="chart-category-title">
            <div class="ccc-card-header">
                <div>
                    <h3 id="chart-category-title" class="ccc-card-title"><x-icon name="tag" /> Inventory value by category</h3>
                    <p class="ccc-card-subtitle">Current stock × recorded unit price, with each category's share</p>
                </div>
                <a href="{{ route('reports.show', 'inventory') }}" class="ccc-card-link">Report <x-icon name="arrow-right" /></a>
            </div>
            <div class="ccc-card-body">
                <div class="ccc-chart-headline">
                    <strong>{{ $money($categoryTotal) }}</strong>
                    <span>total inventory value today</span>
                </div>
                @if ($categoryTotal > 0)
                    <x-bar-chart
                        :items="$charts['categoryValue']"
                        caption="Inventory value by category in Malaysian ringgit"
                        prefix="RM "
                        :decimals="2"
                        value-heading="Value (RM)" />
                @else
                    <x-empty-state icon="inventory" title="No inventory value recorded" compact>
                        Items with a quantity and unit price will appear here.
                    </x-empty-state>
                @endif
            </div>
        </article>

        <article class="ccc-card" aria-labelledby="chart-waste-title">
            <div class="ccc-card-header">
                <div>
                    <h3 id="chart-waste-title" class="ccc-card-title"><x-icon name="waste" /> Waste cost trend</h3>
                    <p class="ccc-card-subtitle">Estimated cost of waste records (RM), by record date</p>
                </div>
                <a href="{{ route('reports.show', ['report' => 'waste'] + $period->reportQuery()) }}" class="ccc-card-link">Report <x-icon name="arrow-right" /></a>
            </div>
            <div class="ccc-card-body">
                <div class="ccc-chart-headline">
                    <strong>{{ $money($kpis['wasteCost']['cost']) }}</strong>
                    <span>
                        estimated in {{ $period->label() }}
                        @if ($kpis['wasteCost']['records'] > 0) · {{ $kpis['wasteCost']['priced'] }} of {{ $kpis['wasteCost']['records'] }} records priced @endif
                    </span>
                </div>
                @if ($hasValues($charts['wasteCost']))
                    <x-line-chart
                        :points="$charts['wasteCost']"
                        caption="Estimated waste cost by month in Malaysian ringgit"
                        prefix="RM "
                        :decimals="2"
                        value-heading="Estimated cost (RM)" />
                @else
                    <x-empty-state icon="waste" title="No priced waste yet" compact>
                        Waste records that match an inventory item's price will appear here.
                    </x-empty-state>
                @endif
            </div>
        </article>

        <article class="ccc-card" aria-labelledby="chart-supplier-title">
            <div class="ccc-card-header">
                <div>
                    <h3 id="chart-supplier-title" class="ccc-card-title"><x-icon name="supplier" /> Supplier purchase distribution</h3>
                    <p class="ccc-card-subtitle">Received orders (RM) in {{ $period->label() }}, with each supplier's share</p>
                </div>
                <a href="{{ route('reports.show', ['report' => 'suppliers'] + $period->reportQuery()) }}" class="ccc-card-link">Report <x-icon name="arrow-right" /></a>
            </div>
            <div class="ccc-card-body">
                <div class="ccc-chart-headline">
                    <strong>{{ $money($supplierTotal) }}</strong>
                    <span>received from {{ $charts['supplierCount'] }} {{ \Illuminate\Support\Str::plural('supplier', $charts['supplierCount']) }}</span>
                </div>
                @if ($supplierTotal > 0)
                    <x-bar-chart
                        :items="$charts['supplierSpend']"
                        caption="Purchase expenditure by supplier in Malaysian ringgit"
                        prefix="RM "
                        :decimals="2"
                        category-heading="Supplier"
                        value-heading="Expenditure (RM)" />
                @else
                    <x-empty-state icon="supplier" title="No received orders in this period" compact>
                        Choose a longer period to compare suppliers.
                    </x-empty-state>
                @endif
            </div>
        </article>
    </div>
</section>
