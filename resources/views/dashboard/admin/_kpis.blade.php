{{-- Section 2: executive KPIs --}}
@php
    $money = fn (float $value) => 'RM ' . number_format($value, 2);
    $plural = fn (string $word, int $count) => \Illuminate\Support\Str::plural($word, $count);
    $inventory = $kpis['inventoryValue'];
    $spent = $kpis['expenditure'];
    $waste = $kpis['wasteCost'];
    $awaiting = $kpis['awaiting'];
    $suppliers = $kpis['suppliers'];
    $alerts = $kpis['alerts'];
    $alertTone = $alerts['critical'] > 0 ? 'is-critical' : ($alerts['total'] > 0 ? 'is-warning' : 'is-good');
@endphp

<section class="ccc-section" aria-labelledby="kpi-title">
    <div class="ccc-section-head">
        <h2 id="kpi-title" class="ccc-section-title">Key figures</h2>
        <p class="ccc-section-text">{{ $period->label() }} · calculated from the database when the page loads</p>
    </div>

    <div class="ccc-kpi-grid">
        <article class="ccc-kpi">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="inventory" /></span> Total inventory value</h3>
            <p class="ccc-kpi-value">{{ $money($inventory['value']) }}</p>
            <p class="ccc-kpi-meta">{{ $inventory['items'] }} {{ $plural('item', $inventory['items']) }} · stock quantity × recorded unit price</p>
            <p class="ccc-kpi-foot"><span class="ccc-delta is-neutral">Current stock, today</span></p>
        </article>

        <article class="ccc-kpi">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="cash" /></span> Purchase expenditure</h3>
            <p class="ccc-kpi-value">{{ $money($spent['value']) }}</p>
            <p class="ccc-kpi-meta">{{ $spent['orders'] }} received {{ $plural('order', $spent['orders']) }} · {{ $period->label() }}</p>
            <p class="ccc-kpi-foot"><x-delta :change="$spent['change']" :against="$previous->label()" /></p>
        </article>

        <article class="ccc-kpi">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="waste" /></span> Waste cost (estimated)</h3>
            <p class="ccc-kpi-value">{{ $money($waste['cost']) }}</p>
            <p class="ccc-kpi-meta">
                {{ $waste['records'] }} waste {{ $plural('record', $waste['records']) }}
                @if ($waste['records'] > 0) · {{ $waste['priced'] }} of {{ $waste['records'] }} priced @endif
            </p>
            <p class="ccc-kpi-foot"><x-delta :change="$waste['change']" :against="$previous->label()" increase="bad" /></p>
        </article>

        <article class="ccc-kpi">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="purchase" /></span> Pending purchase orders</h3>
            <p class="ccc-kpi-value">{{ $awaiting['count'] }} <small>{{ $plural('order', $awaiting['count']) }}</small></p>
            <p class="ccc-kpi-meta"><strong>{{ $money($awaiting['value']) }}</strong> awaiting delivery (pending + ordered)</p>
            <p class="ccc-kpi-foot">
                <span class="ccc-delta is-neutral">
                    @if ($awaiting['oldest'])
                        Oldest placed {{ $awaiting['oldestDays'] === 0 ? 'today' : $awaiting['oldestDays'] . ' ' . $plural('day', $awaiting['oldestDays']) . ' ago' }}
                    @else
                        Nothing waiting for delivery
                    @endif
                </span>
            </p>
        </article>

        <article class="ccc-kpi">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="supplier" /></span> Total suppliers</h3>
            <p class="ccc-kpi-value">{{ $suppliers['total'] }}</p>
            <p class="ccc-kpi-meta">{{ $suppliers['active'] }} with orders in {{ $period->label() }}</p>
            <p class="ccc-kpi-foot"><span class="ccc-delta is-neutral">Cancelled orders excluded</span></p>
        </article>

        <article class="ccc-kpi {{ $alertTone }}">
            <h3 class="ccc-kpi-label"><span class="ccc-kpi-icon"><x-icon name="alert" /></span> Operational alerts</h3>
            <p class="ccc-kpi-value">{{ $alerts['total'] }}</p>
            <ul class="ccc-kpi-chips" aria-label="Alert breakdown">
                <li class="{{ $alerts['out'] > 0 ? 'is-critical' : '' }}">{{ $alerts['out'] }} out of stock</li>
                <li class="{{ $alerts['low'] > 0 ? 'is-warning' : '' }}">{{ $alerts['low'] }} low stock</li>
                <li class="{{ $alerts['expired'] > 0 ? 'is-critical' : ($alerts['expiring'] > 0 ? 'is-warning' : '') }}">{{ $alerts['expired'] + $alerts['expiring'] }} expiry</li>
            </ul>
            <p class="ccc-kpi-foot">
                <a href="{{ route('inventory.index', ['stock' => 'restock']) }}" class="ccc-card-link">Review stock <x-icon name="arrow-right" /></a>
            </p>
        </article>
    </div>
</section>
