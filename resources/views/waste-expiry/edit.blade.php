
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Waste Record | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/storage.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>
<body>
    <nav>
        <img src="{{ asset('images/chicago-logo-transparent.png') }}"
             alt="Chicago Chicken City">
        <a href="{{ route('waste-expiry.index') }}" class="btn btn-light">
            Back to Waste Records
        </a>
    </nav>

    <main>
        <div class="header">
            <div>
                <h1>Edit Waste Record</h1>
                <p>Update the waste and expiry information.</p>
            </div>
        </div>

        <div class="card form-card">
            @if ($errors->any())
                <div class="alert error" role="alert">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('waste-expiry.update', $wasteExpiryRecord) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="item_name">Item Name *</label>
                    <input type="text" id="item_name" name="item_name"
                           value="{{ old('item_name', $wasteExpiryRecord->item_name) }}"
                           required maxlength="255">
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity *</label>
                    <input type="number" id="quantity" name="quantity"
                           value="{{ old('quantity', $wasteExpiryRecord->quantity) }}"
                           min="0.01" step="0.01" required>
                </div>

                <div class="form-group">
                    <label for="unit">Unit *</label>
                    <select id="unit" name="unit" required>
                        <option value="">Select a unit</option>
                        @foreach (['kg', 'g', 'litre', 'ml', 'pcs', 'pack', 'bottle', 'box'] as $unit)
                            <option value="{{ $unit }}"
                                @selected(old('unit', $wasteExpiryRecord->unit) === $unit)>
                                {{ ucfirst($unit) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="reason">Waste Reason *</label>
                    <select id="reason" name="reason" required>
                        @foreach (['expired' => 'Expired', 'spoiled' => 'Spoiled', 'damaged' => 'Damaged', 'other' => 'Other'] as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('reason', $wasteExpiryRecord->reason) === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="expiry_date">Expiry Date</label>
                    <input type="date" id="expiry_date" name="expiry_date"
                           value="{{ old('expiry_date', $wasteExpiryRecord->expiry_date?->format('Y-m-d')) }}">
                </div>

                <div class="form-group">
                    <label for="record_date">Record Date *</label>
                    <input type="date" id="record_date" name="record_date"
                           value="{{ old('record_date', $wasteExpiryRecord->record_date->format('Y-m-d')) }}"
                           required>
                </div>

                <div class="form-group">
                    <label for="description">Description / Remarks</label>
                    <textarea id="description" name="description" rows="4"
                              maxlength="2000">{{ old('description', $wasteExpiryRecord->description) }}</textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-orange">
                        Update Waste Record
                    </button>
                    <a href="{{ route('waste-expiry.index') }}"
                       class="btn btn-light">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
