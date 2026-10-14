<section class="ccc-section" aria-labelledby="quick-actions-title">
    <div class="ccc-section-head">
        <h2 id="quick-actions-title" class="ccc-section-title">Quick actions</h2>
    </div>

    <div class="ccc-quick-actions">
        <a href="{{ route('inventory.create') }}" class="ccc-quick-action">
            <span class="ccc-quick-action-icon"><x-icon name="inventory" /></span>
            <span>Add Inventory Item<small>Inventory</small></span>
        </a>

        <a href="{{ route('suppliers.create') }}" class="ccc-quick-action">
            <span class="ccc-quick-action-icon"><x-icon name="supplier" /></span>
            <span>Add Supplier<small>Suppliers</small></span>
        </a>

        <a href="{{ route('purchases.create') }}" class="ccc-quick-action">
            <span class="ccc-quick-action-icon"><x-icon name="purchase" /></span>
            <span>Create Purchase Order<small>Purchases</small></span>
        </a>

        <a href="{{ route('storage-locations.create') }}" class="ccc-quick-action">
            <span class="ccc-quick-action-icon"><x-icon name="storage" /></span>
            <span>Add Storage Location<small>Storage</small></span>
        </a>

        <a href="{{ route('waste-expiry.create') }}" class="ccc-quick-action">
            <span class="ccc-quick-action-icon"><x-icon name="waste" /></span>
            <span>Record Waste<small>Waste &amp; Expiry</small></span>
        </a>
    </div>
</section>
