
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Waste & Expiry Management | Chicago Chicken City</title>
    <link rel="stylesheet" href="{{ asset('css/storage.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
    <style>
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin: 24px 0;
        }
        .summary-card {
            background: white;
            border: 1px solid #eee2d6;
            border-radius: 12px;
            padding: 20px;
        }
        .summary-card p { color: #777; margin-bottom: 8px; }
        .summary-card h2 { margin: 0; color: #713b18; }
        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .filters input, .filters select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .description-cell { min-width: 150px; }
        @media (max-width: 600px) {
            .table-wrap { overflow-x: auto; }
            table { min-width: 850px; }
        }
    </style>
</head>
<body>
<nav>
    <img src="{{ asset('images/chicago-logo-transparent.png') }}"
         alt="Chicago Chicken City">
    <a href="{{ route('dashboard') }}" class="btn btn-light">Dashboard</a>
</nav>

<main>
    <div class="header">
        <div>
            <h1>Waste & Expiry Management</h1>
            <p>Track expired, spoiled, damaged and wasted food items.</p>
        </div>
        <a href="{{ route('waste-expiry.create') }}" class="btn btn-orange">
            + Record Waste
        </a>
    </div>

    @if(session('success'))
        <div class="alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert error" role="alert">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="summary-grid">
        <div class="summary-card">
            <p>Total Records</p>
            <h2>{{ $totalRecords }}</h2>
        </div>
        <div class="summary-card">
            <p>Expired Items</p>
            <h2>{{ $expiredCount }}</h2>
        </div>
        <div class="summary-card">
            <p>Spoiled Records</p>
            <h2>{{ $spoiledCount }}</h2>
        </div>
        <div class="summary-card">
            <p>Damaged Records</p>
            <h2>{{ $damagedCount }}</h2>
        </div>
    </div>

    <form method="GET" action="{{ route('waste-expiry.index') }}"
          class="filters">
        <input type="search" name="search"
               placeholder="Search item name..."
               value="{{ request('search') }}">

        <select name="reason">
            <option value="">All reasons</option>
            @foreach(['expired', 'spoiled', 'damaged', 'other'] as $reason)
                <option value="{{ $reason }}"
                    @selected(request('reason') === $reason)>
                    {{ ucfirst($reason) }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn btn-orange">Filter</button>
        <a href="{{ route('waste-expiry.index') }}"
           class="btn btn-light">Reset</a>
    </form>

    <div class="card table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Reason</th>
                    <th>Expiry Date</th>
                    <th>Record Date</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td>{{ $record->item_name }}</td>
                        <td>{{ $record->quantity }} {{ $record->unit }}</td>
                        <td>{{ ucfirst($record->reason) }}</td>
                        <td>
                            {{ $record->expiry_date
                                ? $record->expiry_date->format('d M Y')
                                : '-' }}
                        </td>
                        <td>{{ $record->record_date->format('d M Y') }}</td>
                        <td class="description-cell">
                            {{ $record->description ?: '-' }}
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('waste-expiry.show', $record) }}"
                                   class="btn btn-light">View</a>
                                <a href="{{ route('waste-expiry.edit', $record) }}"
                                   class="btn btn-edit">Edit</a>
                                <form method="POST"
                                      action="{{ route('waste-expiry.destroy', $record) }}"
                                      onsubmit="return confirm('Delete this waste record?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-delete">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7"
                            style="text-align:center;padding:40px">
                            No waste records found. Select "Record Waste" to
                            add your first record.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">
        {{ $records->links() }}
    </div>
</main>
</body>
</html>
