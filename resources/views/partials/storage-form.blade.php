{{-- Shared storage location form (create + edit) --}}
@php
    $selectedStatus = (string) old('is_active', $storageLocation->exists ? (int) $storageLocation->is_active : 1);
@endphp

<form method="POST" action="{{ $action }}" class="ccc-card ccc-form-card">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="ccc-form-section">
        <h2 class="ccc-form-section-title">Location details</h2>
        <p class="ccc-form-section-text">Freezers, refrigerators and dry storage areas used to keep stock.</p>

        <div class="ccc-form-grid">
            <div class="ccc-field is-full">
                <label for="name" class="ccc-label">Storage location name <span class="ccc-required">*</span></label>
                <input type="text" id="name" name="name" maxlength="255" required
                       value="{{ old('name', $storageLocation->name) }}"
                       placeholder="Example: Freezer A"
                       class="ccc-input @error('name') is-invalid @enderror">
                <x-field-error name="name" />
            </div>

            <div class="ccc-field">
                <label for="type" class="ccc-label">Storage type <span class="ccc-required">*</span></label>
                <select id="type" name="type" required
                        class="ccc-input @error('type') is-invalid @enderror">
                    <option value="">Select storage type</option>
                    @foreach (['Freezer', 'Refrigerator', 'Dry Storage'] as $type)
                        <option value="{{ $type }}" @selected(old('type', $storageLocation->type) === $type)>{{ $type }}</option>
                    @endforeach
                </select>
                <x-field-error name="type" />
            </div>

            <div class="ccc-field">
                <label for="is_active" class="ccc-label">Status <span class="ccc-required">*</span></label>
                <select id="is_active" name="is_active" required
                        class="ccc-input @error('is_active') is-invalid @enderror">
                    <option value="1" @selected($selectedStatus === '1')>Active</option>
                    <option value="0" @selected($selectedStatus === '0')>Inactive</option>
                </select>
                <x-field-error name="is_active" />
            </div>

            <div class="ccc-field">
                <label for="quantity" class="ccc-label">Quantity (items) <span class="ccc-optional">(optional)</span></label>
                <input type="number" id="quantity" name="quantity" min="0" max="1000000" step="1"
                       value="{{ old('quantity', $storageLocation->quantity) }}"
                       placeholder="e.g. 40"
                       class="ccc-input @error('quantity') is-invalid @enderror">
                <x-field-error name="quantity" />
                <p class="ccc-help">Number of items kept here. Recorded for this location only; it does not change inventory stock levels.</p>
            </div>

            <div class="ccc-field is-full">
                <label for="description" class="ccc-label">Description</label>
                <textarea id="description" name="description" rows="3" maxlength="1000"
                          placeholder="Describe this storage location"
                          class="ccc-input @error('description') is-invalid @enderror">{{ old('description', $storageLocation->description) }}</textarea>
                <x-field-error name="description" />
            </div>
        </div>
    </div>

    <div class="ccc-form-actions">
        <button type="submit" class="ccc-btn ccc-btn-primary">
            <x-icon name="check" /> {{ $submitLabel }}
        </button>
        <a href="{{ route('storage-locations.index') }}" class="ccc-btn ccc-btn-secondary">Cancel</a>
    </div>
</form>
