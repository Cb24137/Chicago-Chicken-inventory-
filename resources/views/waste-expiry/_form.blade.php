{{-- Shared waste record form (create + edit) --}}
<form method="POST" action="{{ $action }}" class="ccc-card ccc-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Wasted item</h2>
        <p class="ccc-form-section-text">What was thrown away, how much, and why.</p>

        <div class="ccc-form-grid is-three">
            <div class="ccc-field is-full">
                <label for="item_name" class="ccc-label">Item name <span class="ccc-required">*</span></label>
                <input type="text" id="item_name" name="item_name" maxlength="255" required
                       value="{{ old('item_name', $wasteExpiryRecord->item_name) }}"
                       placeholder="e.g. Chicken Breast"
                       class="ccc-input @error('item_name') is-invalid @enderror">
                <x-field-error name="item_name" />
            </div>

            <div class="ccc-field">
                <label for="quantity" class="ccc-label">Quantity <span class="ccc-required">*</span></label>
                <input type="number" id="quantity" name="quantity" min="0.01" step="0.01" required
                       value="{{ old('quantity', $wasteExpiryRecord->quantity) }}"
                       class="ccc-input @error('quantity') is-invalid @enderror">
                <x-field-error name="quantity" />
            </div>

            <div class="ccc-field">
                <label for="unit" class="ccc-label">Unit <span class="ccc-required">*</span></label>
                <select id="unit" name="unit" required
                        class="ccc-input @error('unit') is-invalid @enderror">
                    <option value="">Select a unit</option>
                    @foreach (['kg', 'g', 'litre', 'ml', 'pcs', 'pack', 'bottle', 'box'] as $unit)
                        <option value="{{ $unit }}" @selected(old('unit', $wasteExpiryRecord->unit) === $unit)>{{ ucfirst($unit) }}</option>
                    @endforeach
                </select>
                <x-field-error name="unit" />
            </div>

            <div class="ccc-field">
                <label for="reason" class="ccc-label">Waste reason <span class="ccc-required">*</span></label>
                <select id="reason" name="reason" required
                        class="ccc-input @error('reason') is-invalid @enderror">
                    <option value="">Select a reason</option>
                    @foreach (['expired' => 'Expired', 'spoiled' => 'Spoiled', 'damaged' => 'Damaged', 'other' => 'Other'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('reason', $wasteExpiryRecord->reason) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-field-error name="reason" />
            </div>
        </div>
    </div>

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Dates &amp; remarks</h2>
        <p class="ccc-form-section-text">The record date is used for the waste chart on the dashboard.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field">
                <label for="expiry_date" class="ccc-label">Expiry date <span class="ccc-optional">(optional)</span></label>
                <input type="date" id="expiry_date" name="expiry_date"
                       value="{{ old('expiry_date', $wasteExpiryRecord->expiry_date?->format('Y-m-d')) }}"
                       class="ccc-input @error('expiry_date') is-invalid @enderror">
                <x-field-error name="expiry_date" />
            </div>

            <div class="ccc-field">
                <label for="record_date" class="ccc-label">Record date <span class="ccc-required">*</span></label>
                <input type="date" id="record_date" name="record_date" required
                       value="{{ old('record_date', $wasteExpiryRecord->record_date?->format('Y-m-d') ?? now()->toDateString()) }}"
                       class="ccc-input @error('record_date') is-invalid @enderror">
                <x-field-error name="record_date" />
            </div>

            <div class="ccc-field is-full">
                <label for="description" class="ccc-label">Description / remarks</label>
                <textarea id="description" name="description" rows="4" maxlength="2000"
                          placeholder="Enter additional details..."
                          class="ccc-input @error('description') is-invalid @enderror">{{ old('description', $wasteExpiryRecord->description) }}</textarea>
                <x-field-error name="description" />
            </div>
        </div>
    </div>

    <div class="ccc-form-actions">
        <button type="submit" class="ccc-btn ccc-btn-primary">
            <x-icon name="check" /> {{ $submitLabel }}
        </button>
        <a href="{{ route('waste-expiry.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
    </div>
</form>
