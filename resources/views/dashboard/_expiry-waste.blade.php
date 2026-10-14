@php
    // Purchase order units => the matching waste record units
    $wasteUnits = ['kg' => 'kg', 'g' => 'g', 'litres' => 'litre', 'ml' => 'ml', 'pieces' => 'pcs', 'boxes' => 'box', 'bottles' => 'bottle'];
    $reasonTones = ['expired' => 'is-danger', 'spoiled' => 'is-warning', 'damaged' => 'is-warning', 'other' => 'is-neutral'];
@endphp

<section id="expiry-watch" class="ccc-card {{ $span ?? '' }}" aria-labelledby="expiry-waste-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="expiry-waste-title" class="ccc-card-title">
                <x-icon name="expiry" />
                Expiry Watch &amp; Waste
            </h2>
            <p class="ccc-card-subtitle">Deliveries expiring within {{ $expiryDays }} days or expired this week, and the latest waste</p>
        </div>
        <a href="{{ route('waste-expiry.index') }}" class="ccc-card-link">
            Waste records <x-icon name="arrow-right" />
        </a>
    </div>

    <div class="ccc-card-body">
        <h3 class="ccc-subheading">Expiry watch</h3>

        @if ($expiryWatch->isEmpty())
            <p class="ccc-muted ccc-mb">
                No received deliveries expire within {{ $expiryDays }} days. Enter a delivery's expiry date when
                you receive a purchase order to track it here.
            </p>
        @else
            <ul class="ccc-watch-list ccc-mb">
                @foreach ($expiryWatch as $delivery)
                    @php $days = $delivery->daysUntilExpiry(); @endphp
                    <li class="ccc-watch-item">
                        <div>
                            <a href="{{ route('purchases.show', $delivery) }}" class="ccc-cell-title">{{ $delivery->item_name }}</a>
                            <span class="ccc-cell-sub">
                                {{ $delivery->po_number }} · {{ (float) $delivery->quantity }} {{ $delivery->unit }} · expiry {{ $delivery->expiry_date->format('d M Y') }}
                            </span>
                        </div>
                        <span class="ccc-badge {{ $days < 0 ? 'is-danger' : 'is-warning' }}">
                            <x-icon name="expiry" />
                            @if ($days < 0)
                                Expired {{ abs($days) }} {{ \Illuminate\Support\Str::plural('day', abs($days)) }} ago
                            @elseif ($days === 0)
                                Expires today
                            @elseif ($days === 1)
                                Expires tomorrow
                            @else
                                Expires in {{ $days }} days
                            @endif
                        </span>
                        <a href="{{ route('waste-expiry.create', [
                                'item_name' => $delivery->item_name,
                                'unit' => $wasteUnits[$delivery->unit] ?? null,
                                'reason' => 'expired',
                                'expiry_date' => $delivery->expiry_date->toDateString(),
                            ]) }}"
                           class="ccc-btn ccc-btn-soft ccc-btn-sm">
                            Record waste
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <h3 class="ccc-subheading">Recent waste</h3>

        @if ($recentWaste->isEmpty())
            <p class="ccc-muted">No waste has been recorded yet.</p>
        @else
            <ul class="ccc-watch-list">
                @foreach ($recentWaste as $record)
                    <li class="ccc-watch-item">
                        <div>
                            <a href="{{ route('waste-expiry.show', $record) }}" class="ccc-cell-title">{{ $record->item_name }}</a>
                            <span class="ccc-cell-sub">{{ (float) $record->quantity }} {{ $record->unit }} · {{ $record->record_date->format('d M Y') }}</span>
                        </div>
                        <span class="ccc-badge {{ $reasonTones[$record->reason] ?? 'is-neutral' }}">{{ ucfirst($record->reason) }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
