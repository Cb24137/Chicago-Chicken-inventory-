{{-- Shared supplier form (create + edit). Supplier details only -
     quantities and costs are recorded on purchase orders. --}}
<form method="POST" action="{{ $action }}" class="ccc-card ccc-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Supplier details</h2>
        <p class="ccc-form-section-text">Contact information and the products this supplier provides.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field is-full">
                <label for="name" class="ccc-label">Supplier name <span class="ccc-required">*</span></label>
                <input type="text" id="name" name="name" maxlength="255" required
                       value="{{ old('name', $supplier->name) }}"
                       placeholder="e.g. Halal Poultry Supplies Sdn Bhd"
                       class="ccc-input @error('name') is-invalid @enderror">
                <x-field-error name="name" />
            </div>

            <div class="ccc-field">
                <label for="phone" class="ccc-label">Phone number</label>
                <input type="text" id="phone" name="phone" maxlength="30"
                       value="{{ old('phone', $supplier->phone) }}"
                       placeholder="e.g. 012-345 6789"
                       class="ccc-input @error('phone') is-invalid @enderror">
                <x-field-error name="phone" />
            </div>

            <div class="ccc-field">
                <label for="email" class="ccc-label">Email address</label>
                <input type="email" id="email" name="email" maxlength="255"
                       value="{{ old('email', $supplier->email) }}"
                       placeholder="e.g. orders@supplier.com"
                       class="ccc-input @error('email') is-invalid @enderror">
                <x-field-error name="email" />
            </div>

            <div class="ccc-field is-full">
                <label for="address" class="ccc-label">Address</label>
                <textarea id="address" name="address" rows="3"
                          class="ccc-input @error('address') is-invalid @enderror">{{ old('address', $supplier->address) }}</textarea>
                <x-field-error name="address" />
            </div>

            <div class="ccc-field is-full">
                <label for="products_supplied" class="ccc-label">Products supplied</label>
                <input type="text" id="products_supplied" name="products_supplied" maxlength="255"
                       value="{{ old('products_supplied', $supplier->products_supplied) }}"
                       placeholder="e.g. Frozen chicken, cooking oil, soft drinks"
                       class="ccc-input @error('products_supplied') is-invalid @enderror">
                <x-field-error name="products_supplied" />
            </div>
        </div>
    </div>

    <div class="ccc-form-actions">
        <button type="submit" class="ccc-btn ccc-btn-primary">
            <x-icon name="check" /> {{ $submitLabel }}
        </button>
        <a href="{{ route('suppliers.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
    </div>
</form>
