{{-- Shared inventory item form (create + edit) --}}
<form method="POST" action="{{ $action }}" class="ccc-card ccc-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Item details</h2>
        <p class="ccc-form-section-text">Name the item and choose how it is measured.</p>

        <div class="ccc-form-grid is-three">
            <div class="ccc-field is-full">
                <label for="item_name" class="ccc-label">Item name <span class="ccc-required">*</span></label>
                <input type="text" id="item_name" name="item_name" maxlength="255" required
                       value="{{ old('item_name', $inventoryItem->item_name) }}"
                       placeholder="e.g. Chicken"
                       class="ccc-input @error('item_name') is-invalid @enderror">
                <x-field-error name="item_name" />
            </div>

            <div class="ccc-field">
                <label for="category" class="ccc-label">Category <span class="ccc-required">*</span></label>
                <select id="category" name="category" required
                        class="ccc-input @error('category') is-invalid @enderror">
                    <option value="">Select category</option>
                    @foreach (\App\Models\InventoryItem::CATEGORIES as $category)
                        <option value="{{ $category }}" @selected(old('category', $inventoryItem->category) === $category)>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
                <x-field-error name="category" />
            </div>

            <div class="ccc-field">
                <label for="unit" class="ccc-label">Unit <span class="ccc-required">*</span></label>
                <select id="unit" name="unit" required
                        class="ccc-input @error('unit') is-invalid @enderror">
                    <option value="">Select unit</option>
                    @foreach (['kg' => 'Kilogram (kg)', 'litre' => 'Litre', 'pcs' => 'Pieces (pcs)'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('unit', $inventoryItem->unit) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <x-field-error name="unit" />
            </div>

            <div class="ccc-field">
                <label for="unit_price" class="ccc-label">Unit price (RM) <span class="ccc-required">*</span></label>
                <div class="ccc-input-group">
                    <span class="ccc-input-addon">RM</span>
                    <input type="number" id="unit_price" name="unit_price" min="0" step="0.01" required
                           value="{{ old('unit_price', $inventoryItem->unit_price) }}"
                           class="ccc-input @error('unit_price') is-invalid @enderror">
                </div>
                <x-field-error name="unit_price" />
            </div>
        </div>
    </div>

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Stock levels</h2>
        <p class="ccc-form-section-text">Items at or below the minimum stock level appear as low-stock alerts on the dashboard and in notifications.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field">
                <label for="quantity" class="ccc-label">Quantity <span class="ccc-required">*</span></label>
                <input type="number" id="quantity" name="quantity" min="0" step="0.01" required
                       value="{{ old('quantity', $inventoryItem->quantity) }}"
                       class="ccc-input @error('quantity') is-invalid @enderror">
                <x-field-error name="quantity" />
            </div>

            <div class="ccc-field">
                <label for="minimum_stock" class="ccc-label">Minimum stock <span class="ccc-required">*</span></label>
                <input type="number" id="minimum_stock" name="minimum_stock" min="0" step="0.01" required
                       value="{{ old('minimum_stock', $inventoryItem->minimum_stock) }}"
                       class="ccc-input @error('minimum_stock') is-invalid @enderror">
                <x-field-error name="minimum_stock" />
            </div>
        </div>
    </div>

    <div class="ccc-form-actions">
        <button type="submit" class="ccc-btn ccc-btn-primary">
            <x-icon name="check" /> {{ $submitLabel }}
        </button>
        <a href="{{ route('inventory.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
    </div>
</form>
