
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
            background-color: #faf9f6;
            font-family: Arial, sans-serif;
            color: #4a2410;
        }

        /* Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e9e3da;
            padding: 15px 0;
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        .btn-dashboard {
            background-color: #fff8ef;
            color: #4a2410;
            border: none;
            border-radius: 9px;
            padding: 10px 20px;
        }

        .btn-dashboard:hover {
            background-color: #f5e9d8;
            color: #4a2410;
        }

        /* Page heading */
        .page-title {
            color: #4a2410;
            font-weight: 700;
        }

        .page-description {
            color: #938c83;
        }

        /* Add Inventory button */
        .btn-add {
            background-color: #dc6500;
            color: white;
            border: none;
            border-radius: 9px;
            padding: 12px 22px;
            font-weight: 500;
        }

        .btn-add:hover {
            background-color: #b95200;
            color: white;
        }

        /* Inventory table */
        .inventory-card {
            background-color: white;
            border: 1px solid #eee5da;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background-color: #fff8ef;
            color: #4a2410;
            padding: 18px;
            font-weight: 600;
            border-bottom: 1px solid #eee5da;
        }

        .table tbody td {
            padding: 18px;
            vertical-align: middle;
            color: #5b4637;
            border-color: #f0eae3;
        }

        .table-hover tbody tr:hover td {
            background-color: #fffaf3;
        }

        /* Edit button */
        .btn-edit {
            background-color: #fff0d6;
            color: #a65b13;
            border: none;
            border-radius: 9px;
            padding: 8px 16px;
        }

        .btn-edit:hover {
            background-color: #ffe2b2;
            color: #8a470a;
        }

        /* Delete button */
        .btn-delete {
            background-color: #fff0ee;
            color: #b74646;
            border: none;
            border-radius: 9px;
            padding: 8px 16px;
        }

        .btn-delete:hover {
            background-color: #ffded9;
            color: #a52f2f;
        }

        /* Stock status */
        .stock-in {
            background-color: #e8f7ec;
            color: #27834b;
            border-radius: 20px;
            padding: 8px 12px;
            font-weight: 500;
        }

        .stock-low {
            background-color: #fff0ee;
            color: #c24141;
            border-radius: 20px;
            padding: 8px 12px;
            font-weight: 500;
        }

        @media (max-width: 576px) {
            .navbar-brand img {
                height: 50px;
            }

            .page-title {
                font-size: 25px;
            }
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="container">

            <a class="navbar-brand"
               href="{{ route('dashboard') }}">

                <img src="{{ asset('images/chicago-logo-transparent.png') }}"
                     alt="Chicago Chicken City Logo">

            </a>

            <a href="{{ route('dashboard') }}"
               class="btn btn-dashboard">
                Dashboard
            </a>

        </div>
    </nav>

    <!-- Main content -->
    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-5 flex-wrap gap-3">

            <div>
                <h2 class="page-title mb-3">
                    Food & Beverage Inventory
                </h2>

                <p class="page-description mb-0">
                    Manage food ingredients, beverages and stock levels.
                </p>
            </div>

            <a href="{{ route('inventory.create') }}"
               class="btn btn-add">
                + Add Inventory
            </a>

        </div>

        <!-- Success message -->
        @if (session('success'))

            <div class="alert alert-success alert-dismissible fade show"
                 role="alert">

                {{ session('success') }}

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Close">
                </button>

            </div>

        @endif

        <!-- Inventory table -->
        <div class="card inventory-card">

            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover">

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

                                    <td>
                                        {{ $item->item_name }}
                                    </td>

                                    <td>
                                        {{ $item->category }}
                                    </td>

                                    <td>
                                        {{ $item->quantity }}
                                        {{ $item->unit }}
                                    </td>

                                    <td>
                                        {{ number_format($item->unit_price, 2) }}
                                    </td>

                                    <td>

                                        @if ($item->quantity <= $item->minimum_stock)

                                            <span class="badge stock-low">
                                                Low Stock
                                            </span>

                                        @else

                                            <span class="badge stock-in">
                                                In Stock
                                            </span>

                                        @endif

                                    </td>

                                    <td>

                                        <div class="d-flex gap-2">

                                            <!-- Edit -->
                                            <a href="{{ route('inventory.edit', $item->id) }}"
                                               class="btn btn-edit btn-sm">
                                                Edit
                                            </a>

                                            <!-- Delete -->
                                            <form action="{{ route('inventory.destroy', $item->id) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this inventory item?');">

                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                        class="btn btn-delete btn-sm">
                                                    Delete
                                                </button>

                                            </form>

                                        </div>

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
