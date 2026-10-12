
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Waste Record Details | Chicago Chicken City</title>
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
                <h1>Waste Record Details</h1>
                <p>Review the recorded waste or expiry information.</p>
            </div>
            <a href="{{ route('waste-expiry.edit', $wasteExpiryRecord) }}"
               class="btn btn-orange">Edit Record</a>
        </div>

        <div class="card form-card">
            <div class="form-group">
                <label>Item Name</label>
                <p>{{ $wasteExpiryRecord->item_name }}</p>
            </div>

            <div class="form-group">
                <label>Quantity</label>
                <p>{{ number_format((float) $wasteExpiryRecord->quantity, 2) }}
                   {{ $wasteExpiryRecord->unit }}</p>
            </div>

            <div class="form-group">
                <label>Waste Reason</label>
                <p>{{ ucfirst($wasteExpiryRecord->reason) }}</p>
            </div>

            <div class="form-group">
                <label>Expiry Date</label>
                <p>{{ $wasteExpiryRecord->expiry_date
                    ? $wasteExpiryRecord->expiry_date->format('d M Y')
                    : 'Not provided' }}</p>
            </div>

            <div class="form-group">
                <label>Record Date</label>
                <p>{{ $wasteExpiryRecord->record_date->format('d M Y') }}</p>
            </div>

            <div class="form-group">
                <label>Description / Remarks</label>
                <p>{{ $wasteExpiryRecord->description ?: 'No description provided.' }}</p>
            </div>

            <div class="form-actions">
                <a href="{{ route('waste-expiry.index') }}"
                   class="btn btn-light">Back</a>
                <a href="{{ route('waste-expiry.edit', $wasteExpiryRecord) }}"
                   class="btn btn-orange">Edit Record</a>
            </div>
        </div>
    </main>
</body>
</html>
