<section class="ccc-card {{ $span ?? '' }}" aria-labelledby="stock-status-title">
    <div class="ccc-card-header">
        <div>
            <h2 id="stock-status-title" class="ccc-card-title">
                <x-icon name="inventory" />
                Stock status
            </h2>
            <p class="ccc-card-subtitle">All {{ $stock['total'] }} inventory items by stock level</p>
        </div>
    </div>
    <div class="ccc-card-body">
        @if ($stock['total'] > 0)
            <x-bar-chart
                :items="$stockStatusChart"
                caption="Inventory items by stock status"
                unit="item"
                category-heading="Status"
                value-heading="Items" />
        @else
            <x-empty-state icon="inventory" title="No inventory items yet" compact>
                Add food and beverage items to see their stock status.
            </x-empty-state>
        @endif
    </div>
</section>
