{{-- Shared purchase order form (create + edit) --}}
@php
    // After stock has been added, fields that change stock are locked
    $stockLocked = $purchase->exists && $purchase->hasAddedStock();
    $duplicateNames = $suppliers->countBy('name')->filter(fn ($count) => $count > 1);
    $selectedSupplier = (string) old('supplier_id', $purchase->supplier_id);
    $selectedItem = (string) old('inventory_item_id', $purchase->inventory_item_id);
    $selectedUnit = old('unit', $purchase->unit);
    $selectedStatus = old('status', $purchase->status ?? 'pending');
    $quantityValue = old('quantity', $purchase->quantity !== null ? (float) $purchase->quantity : null);
    $priceValue = old('unit_price', $purchase->unit_price !== null ? number_format((float) $purchase->unit_price, 2, '.', '') : null);
    $dateValue = old('purchase_date', $purchase->purchase_date?->format('Y-m-d'));
    $expiryValue = old('expiry_date', $purchase->expiry_date?->format('Y-m-d'));
@endphp

<form method="POST"
      action="{{ $action }}"
      class="ccc-card ccc-form-card"
      data-purchase-form
      data-unit-factors="{{ json_encode($unitFactors) }}"
      data-unit-labels="{{ json_encode($units) }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    @if ($stockLocked)
        <div class="ccc-form-section">
            <div class="ccc-alert is-info ccc-mb-0">
                <x-icon name="lock" />
                <div class="ccc-alert-content">
                    <strong>Stock already received.</strong>
                    {{ (float) $purchase->stock_added_quantity }} {{ $purchase->inventoryItem?->unit }}
                    was added to inventory on {{ $purchase->stock_added_at->format('d M Y') }}.
                    Quantity, unit, inventory item and status are locked to keep stock levels correct.
                </div>
            </div>
        </div>
    @endif

    {{-- Supplier and product --}}
    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Supplier &amp; product</h2>
        <p class="ccc-form-section-text">Choose who you are ordering from and what you are ordering.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field">
                <label for="supplier_id" class="ccc-label">Supplier <span class="ccc-required">*</span></label>
                <select id="supplier_id" name="supplier_id" required
                        class="ccc-input @error('supplier_id') is-invalid @enderror">
                    <option value="">Select a supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected($selectedSupplier === (string) $supplier->id)>
                            {{ $supplier->name }}{{ $duplicateNames->has($supplier->name) ? ' (ID ' . $supplier->id . ')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-field-error name="supplier_id" />
                <p class="ccc-help">
                    Not listed? <a href="{{ route('suppliers.create') }}" class="ccc-text-link">Add a new supplier</a>.
                </p>
            </div>

            <div class="ccc-field">
                <label for="inventory_item_id" class="ccc-label">
                    Inventory item <span class="ccc-optional">(optional)</span>
                </label>

                @if ($stockLocked)
                    <div class="ccc-locked-value">
                        <x-icon name="lock" />
                        {{ $purchase->inventoryItem?->item_name ?? 'Not linked' }}
                    </div>
                @else
                    <select id="inventory_item_id" name="inventory_item_id"
                            class="ccc-input @error('inventory_item_id') is-invalid @enderror">
                        <option value="">Not linked to inventory</option>
                        @foreach ($inventoryItems as $item)
                            <option value="{{ $item->id }}"
                                    data-name="{{ $item->item_name }}"
                                    data-unit="{{ $item->unit }}"
                                    data-price="{{ number_format((float) $item->unit_price, 2, '.', '') }}"
                                    @selected($selectedItem === (string) $item->id)>
                                {{ $item->item_name }} ({{ (float) $item->quantity }} {{ $item->unit }} in stock)
                            </option>
                        @endforeach
                    </select>
                    <x-field-error name="inventory_item_id" />
                    <p class="ccc-help"
                       data-unit-hint
                       data-default="Link a Food &amp; Beverage Inventory item so its stock increases when this order is received.">
                        Link a Food &amp; Beverage Inventory item so its stock increases when this order is received.
                    </p>
                @endif
            </div>

            <div class="ccc-field is-full">
                <label for="item_name" class="ccc-label">Product name <span class="ccc-required">*</span></label>
                <input type="text" id="item_name" name="item_name" maxlength="255"
                       value="{{ old('item_name', $purchase->item_name) }}"
                       placeholder="e.g. Frozen chicken breast"
                       class="ccc-input @error('item_name') is-invalid @enderror">
                <x-field-error name="item_name" />
                <p class="ccc-help">Filled in automatically when you choose an inventory item.</p>
            </div>
        </div>
    </div>

    {{-- Quantity and cost --}}
    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Quantity &amp; cost</h2>
        <p class="ccc-form-section-text">The total cost is calculated automatically: quantity × unit price.</p>

        <div class="ccc-form-grid is-three">
            <div class="ccc-field">
                <label for="quantity" class="ccc-label">Quantity <span class="ccc-required">*</span></label>
                <input type="number" id="quantity" name="quantity" min="0.01" step="0.01" required
                       value="{{ $quantityValue }}"
                       @disabled($stockLocked)
                       class="ccc-input @error('quantity') is-invalid @enderror">
                <x-field-error name="quantity" />
            </div>

            <div class="ccc-field">
                <label for="unit" class="ccc-label">Unit of measurement <span class="ccc-required">*</span></label>
                <select id="unit" name="unit" required @disabled($stockLocked)
                        class="ccc-input @error('unit') is-invalid @enderror">
                    <option value="">Select a unit</option>
                    @foreach ($units as $value => $label)
                        <option value="{{ $value }}" @selected($selectedUnit === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="unit" />
            </div>

            <div class="ccc-field">
                <label for="unit_price" class="ccc-label">Unit price (RM) <span class="ccc-required">*</span></label>
                <div class="ccc-input-group">
                    <span class="ccc-input-addon">RM</span>
                    <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required
                           value="{{ $priceValue }}"
                           placeholder="0.00"
                           class="ccc-input @error('unit_price') is-invalid @enderror">
                </div>
                <x-field-error name="unit_price" />
            </div>

            <div class="ccc-field is-full">
                <div class="ccc-total-box" aria-live="polite">
                    <span>Total cost (RM) · calculated automatically</span>
                    <strong data-total>RM {{ number_format(\App\Models\Purchase::calculateTotal($quantityValue, $priceValue), 2) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- Order details --}}
    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Order details</h2>
        <p class="ccc-form-section-text">Set the purchase date and where the order is up to.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field">
                <label for="purchase_date" class="ccc-label">Purchase date <span class="ccc-required">*</span></label>
                <input type="date" id="purchase_date" name="purchase_date" required
                       value="{{ $dateValue }}"
                       class="ccc-input @error('purchase_date') is-invalid @enderror">
                <x-field-error name="purchase_date" />
            </div>

            <div class="ccc-field">
                <label for="status" class="ccc-label">Order status <span class="ccc-required">*</span></label>
                <select id="status" name="status" required @disabled($stockLocked)
                        class="ccc-input @error('status') is-invalid @enderror">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="status" />
            </div>

            <div class="ccc-field">
                <label for="expiry_date" class="ccc-label">Expiry date <span class="ccc-optional">(optional)</span></label>
                <input type="date" id="expiry_date" name="expiry_date"
                       value="{{ $expiryValue }}"
                       class="ccc-input @error('expiry_date') is-invalid @enderror">
                <x-field-error name="expiry_date" />
                <p class="ccc-help">
                    For perishable goods. Once the order is received, everyone is alerted
                    {{ config('inventory.expiry_alert_days') }} days before this date.
                </p>
            </div>

            @unless ($stockLocked)
                <div class="ccc-field is-full">
                    <p class="ccc-alert is-info ccc-mb-0" data-receive-hint hidden></p>
                </div>
            @endunless

            <div class="ccc-field is-full">
                <label for="notes" class="ccc-label">Notes <span class="ccc-optional">(optional)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="2000"
                          placeholder="Delivery instructions, invoice number, etc."
                          class="ccc-input @error('notes') is-invalid @enderror">{{ old('notes', $purchase->notes) }}</textarea>
                <x-field-error name="notes" />
            </div>
        </div>
    </div>

    <div class="ccc-form-actions">
        <button type="submit" class="ccc-btn ccc-btn-primary">
            <x-icon name="check" /> {{ $submitLabel }}
        </button>
        <a href="{{ route('purchases.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
    </div>
</form>

@push('scripts')
    <script src="{{ asset('js/purchase-form.js') }}?v={{ filemtime(public_path('js/purchase-form.js')) }}" defer></script>
@endpush
