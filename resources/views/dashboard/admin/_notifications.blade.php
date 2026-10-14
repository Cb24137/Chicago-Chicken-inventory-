{{-- Section 7: high-priority notifications and the live risk counts behind them --}}
@php
    $alerts = $kpis['alerts'];
    $expiry = $alerts['expired'] + $alerts['expiring'];
    $risks = [
        ['label' => 'Out of stock', 'count' => $alerts['out'], 'tone' => 'critical', 'href' => route('inventory.index', ['stock' => 'out'])],
        ['label' => 'Low stock', 'count' => $alerts['low'], 'tone' => 'warning', 'href' => route('inventory.index', ['stock' => 'low'])],
        ['label' => 'Expiry issues', 'count' => $expiry, 'tone' => $alerts['expired'] > 0 ? 'critical' : 'warning', 'href' => route('notifications.index', ['category' => 'expiry'])],
        ['label' => 'Awaiting delivery', 'count' => $kpis['awaiting']['count'], 'tone' => 'info', 'href' => route('purchases.index')],
    ];
@endphp

<section class="ccc-card ccc-span-5" aria-labelledby="important-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="important-title" class="ccc-card-title"><x-icon name="bell" /> Important notifications</h2>
            <p class="ccc-card-subtitle">{{ $unreadCount }} unread · critical alerts and staff changes first</p>
        </div>
        <a href="{{ route('notifications.index') }}" class="ccc-card-link">All notifications <x-icon name="arrow-right" /></a>
    </div>

    <div class="ccc-card-body">
        <ul class="ccc-risk-grid" aria-label="Current risks">
            @foreach ($risks as $risk)
                <li>
                    <a href="{{ $risk['href'] }}" class="ccc-risk {{ $risk['count'] > 0 ? 'is-' . $risk['tone'] : '' }}">
                        <strong>{{ $risk['count'] }}</strong>
                        <span>{{ $risk['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>

        @if ($importantNotifications->isEmpty())
            <x-empty-state icon="check-circle" title="No important notifications" compact>
                Critical and warning alerts, and staff account changes, will appear here.
            </x-empty-state>
        @else
            <ul class="ccc-note-list">
                @foreach ($importantNotifications as $notification)
                    @include('notifications._item')
                @endforeach
            </ul>
        @endif
    </div>
</section>
