<section class="ccc-card {{ $span ?? '' }}" aria-labelledby="low-stock-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="low-stock-title" class="ccc-card-title">
                <x-icon name="alert" />
                Low Stock Alerts
            </h2>
            <p class="ccc-card-subtitle">Items at or below their minimum stock level</p>
        </div>
        <a href="{{ route('inventory.index', ['stock' => 'restock']) }}" class="ccc-card-link">
            View inventory <x-icon name="arrow-right" />
        </a>
    </div>

    <div class="ccc-card-body">
        @if ($lowStockItems->isEmpty())
            <x-empty-state icon="check-circle" title="All stock levels are healthy" compact>
                No inventory item is at or below its minimum stock level.
            </x-empty-state>
        @else
            <ul class="ccc-stock-list">
                @foreach ($lowStockItems as $item)
                    @php
                        $quantity = (float) $item->quantity;
                        $minimum = (float) $item->minimum_stock;
                        $outOfStock = $quantity <= 0;
                        $level = $minimum > 0 ? min(100, max(0, $quantity / $minimum * 100)) : 0;
                    @endphp

                    <li class="ccc-stock-item">
                        <span class="ccc-stock-name">{{ $item->item_name }}</span>
                        <span class="ccc-badge {{ $outOfStock ? 'is-danger' : 'is-warning' }}">
                            <x-icon name="alert" />
                            {{ $outOfStock ? 'Out of stock' : 'Low stock' }}
                        </span>

                        <span class="ccc-stock-meta">
                            {{ $quantity }} {{ $item->unit }} in stock · minimum {{ $minimum }} {{ $item->unit }}
                        </span>
                        <a href="{{ route('purchases.create', ['inventory_item_id' => $item->id]) }}"
                           class="ccc-btn ccc-btn-soft ccc-btn-sm"
                           title="Create a purchase order for {{ $item->item_name }}">
                            Reorder
                        </a>

                        <div class="ccc-meter {{ $outOfStock ? 'is-critical' : '' }}"
                             role="img"
                             aria-label="{{ round($level) }}% of minimum stock level">
                            <span style="width: {{ $level }}%"></span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    @if ($stock['alerts'] > $lowStockItems->count())
        <div class="ccc-card-footer">
            Showing {{ $lowStockItems->count() }} of {{ $stock['alerts'] }} items that need restocking.
        </div>
    @endif
</section>
