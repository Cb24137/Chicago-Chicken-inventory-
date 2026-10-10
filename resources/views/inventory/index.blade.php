<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management | Chicago Chicken City</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        body {
            background-color: #f1f5f2;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #173d30;
        }

        .page-title {
            color: #173d30;
            font-weight: bold;
        }

        .inventory-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead th {
            background-color: #173d30;
            color: white;
            padding: 15px;
        }

        .table td {
            padding: 14px;
            vertical-align: middle;
        }

        .btn-green {
            background-color: #173d30;
            color: white;
        }

        .btn-green:hover {
            background-color: #245c47;
            color: white;
        }
    </style>
</head>

<body>

<nav class="navbar navbar-dark">
    <div class="container">
        <a class="navbar-brand fw-bold"
           href="{{ route('dashboard') }}">
            Chicago Chicken City
        </a>

        <a href="{{ route('dashboard') }}"
           class="btn btn-outline-light btn-sm">
            Back to Dashboard
        </a>
    </div>
</nav>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title">Food & Beverage Inventory</h2>
            <p class="text-muted mb-0">
                Manage food ingredients, beverages and stock levels.
            </p>
        </div>

        
<a href="{{ route('inventory.create') }}" class="btn btn-green">
    + Add Inventory
</a>

    </div>

    
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"></button>
    </div>
@endif


    <div class="card inventory-card shadow-sm">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Unit Price (RM)</th>
                            <th>Stock Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($inventoryItems as $item)
                            <tr>
                                <td>{{ $item->item_name }}</td>

                                <td>{{ $item->category }}</td>

                                <td>
                                    {{ $item->quantity }}
                                    {{ $item->unit }}
                                </td>

                                <td>
                                    {{ number_format($item->unit_price, 2) }}
                                </td>

                                <td>
                                    @if ($item->quantity <= $item->minimum_stock)
                                        <span class="badge bg-danger">
                                            Low Stock
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            In Stock
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-outline-primary"
                                            disabled>
                                        Edit
                                    </button>

                                    <button class="btn btn-sm btn-outline-danger"
                                            disabled>
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="text-center text-muted py-4">
                                    No inventory items found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
