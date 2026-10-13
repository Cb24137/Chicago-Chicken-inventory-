
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Food & Beverage Inventory | Chicago Chicken City</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --brown: #4a2410;
            --orange: #e86b00;
            --orange-dark: #c75b00;
            --cream: #faf9f6;
            --soft-cream: #fff7ed;
            --border: #eee4d9;
            --muted: #92867b;
        }

        body {
            background: var(--cream);
            font-family: Arial, sans-serif;
            color: var(--brown);
        }

        .navbar {
            background: white;
            border-bottom: 1px solid var(--border);
            padding: 12px 0;
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        .btn-dashboard {
            background: var(--soft-cream);
            color: var(--brown);
            border-radius: 10px;
            padding: 10px 18px;
            text-decoration: none;
        }

        .btn-dashboard:hover {
            background: #f7e7d5;
            color: var(--brown);
        }

        .page-title {
            font-size: 30px;
            font-weight: 700;
            color: var(--brown);
        }

        .page-description {
            color: var(--muted);
            font-size: 14px;
        }

        .btn-add {
            background: var(--orange);
            color: white;
            border: none;
            border-radius: 11px;
            padding: 12px 20px;
            font-weight: 600;
        }

        .btn-add:hover {
            background: var(--orange-dark);
            color: white;
        }

        /* SUMMARY CARDS */
        .summary-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            height: 100%;
            box-shadow: 0 3px 12px rgba(74,36,16,.03);
        }

        .summary-content {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .summary-icon {
            width: 54px;
            height: 54px;
            min-width: 54px;
            border-radius: 15px;
            background: #fff1df;
            color: var(--orange);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 25px;
        }

        .summary-number {
            font-size: 30px;
            line-height: 1.1;
            font-weight: 700;
        }

        .summary-label {
            color: var(--muted);
            font-size: 13px;
            margin-top: 6px;
        }

        .summary-danger {
            background: #fff5f4;
            border-color: #f5c5c1;
        }

        .summary-danger .summary-icon {
            background: #ffe1de;
            color: #dc2626;
        }

        .summary-danger .summary-number,
        .summary-danger .summary-label {
            color: #c52222;
        }

        .attention-badge {
            display: inline-block;
            margin-top: 8px;
            background: #dc2626;
            color: white;
            border-radius: 20px;
            padding: 4px 9px;
            font-size: 10px;
            font-weight: 600;
        }

        /* SECTION CARDS */
        .section-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(74,36,16,.03);
        }

        .section-header {
            padding: 20px 22px;
            border-bottom: 1px solid #f2eae2;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .section-description {
            font-size: 12px;
            color: var(--muted);
            margin: 6px 0 0;
        }

        /* SEARCH AND FILTER */
        .search-box {
            position: relative;
        }

        .search-box i {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            color: #a89a8b;
        }

        .search-input {
            padding: 12px 15px 12px 42px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
        }

        .filter-select {
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            color: var(--brown);
        }

        .search-input:focus,
        .filter-select:focus {
            border-color: var(--orange);
            box-shadow: 0 0 0 3px rgba(232,107,0,.1);
        }

        /* TABLE */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: var(--soft-cream);
            color: var(--brown);
            font-size: 13px;
            font-weight: 700;
            padding: 17px;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .table tbody td {
            padding: 17px;
            vertical-align: middle;
            color: #5b4637;
            border-color: #f2eae2;
            font-size: 14px;
        }

        .table-hover tbody tr:hover td {
            background: #fffaf4;
        }

        .item-name {
            font-weight: 700;
            color: var(--brown);
        }

        /* CATEGORY BADGES */
        .category-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            border-radius: 20px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 600;
        }

        .category-food {
            background: #fff1df;
            color: #ad5b12;
        }

        .category-beverage {
            background: #e9f4ff;
            color: #286b9d;
        }

        .total-badge {
            background: #fff3e4;
            color: #ad5b12;
        }

        /* QUANTITY */
        .quantity-display {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .quantity-number {
            font-weight: 700;
            font-size: 14px;
            color: var(--brown);
        }

        .quantity-low .quantity-number {
            color: #dc2626;
        }

        .unit-badge {
            background: #f7f2ec;
            border: 1px solid #eee4d9;
            color: #796454;
            border-radius: 7px;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        /* STOCK STATUS */
        .stock-in {
            background: #e7f6eb;
            color: #27834b;
            border-radius: 20px;
            padding: 7px 11px;
            font-size: 12px;
        }

        .stock-low {
            background: #ffe8e6;
            color: #c62828;
            border-radius: 20px;
            padding: 7px 11px;
            font-size: 12px;
        }

        /* ACTION BUTTONS */
        .btn-edit {
            background: #fff0d6;
            color: #a65b13;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .btn-edit:hover {
            background: #ffe2b2;
            color: #8a470a;
        }

        .btn-delete {
            background: #fff0ee;
            color: #b74646;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
        }

        .btn-delete:hover {
            background: #ffded9;
            color: #a52f2f;
        }

        /* LOW STOCK ALERTS */
        .alert-icon {
            color: var(--orange);
        }

        .alert-count {
            background: #ffe8e6;
            color: #c62828;
            border-radius: 20px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .alert-item {
            padding: 15px 22px;
            border-bottom: 1px solid #f2eae2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-item-name {
            font-size: 14px;
            font-weight: 700;
        }

        .alert-item-detail {
            color: var(--muted);
            font-size: 12px;
            margin-top: 5px;
        }

        .alert-item-detail strong {
            color: #dc2626;
        }

        .btn-view {
            background: #fff3e4;
            color: #ad5b12;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-view:hover {
            background: #ffe5c4;
            color: #8a470a;
        }

        .empty-message {
            padding: 30px;
            text-align: center;
            color: var(--muted);
            font-size: 14px;
        }

        .inventory-footer {
            padding: 14px 20px;
            color: var(--muted);
            font-size: 12px;
            border-top: 1px solid #f2eae2;
        }

        @media (max-width: 768px) {
            .navbar-brand img {
                height: 50px;
            }

            .page-title {
                font-size: 25px;
            }

            .summary-card {
                padding: 17px;
            }

            .summary-number {
                font-size: 25px;
            }

            .table thead th,
            .table tbody td {
                padding: 13px;
            }
        }
    </style>
</head>

<body>

@php
    $totalItems = $inventoryItems->count();

    $foodCount = $inventoryItems->filter(function ($item) {
        return $item->category === 'Food';
    })->count();

    $beverageCount = $inventoryItems->filter(function ($item) {
        return $item->category === 'Beverage';
    })->count();

    $lowStockItems = $inventoryItems->filter(function ($item) {
        return $item->quantity <= $item->minimum_stock;
    });

    $lowStockCount = $lowStockItems->count();

    $unitLabels = [
        'kg' => 'kg',
        'g' => 'g',
        'litre' => 'L',
        'ml' => 'ml',
        'pcs' => 'pcs',
        'pack' => 'pack',
        'bottle' => 'bottle',
        'can' => 'can',
        'box' => 'box',
        'bag' => 'bag'
    ];
@endphp

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">

        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/chicago-logo-transparent.png') }}"
                 alt="Chicago Chicken City Logo">
        </a>

        <a href="{{ route('dashboard') }}" class="btn btn-dashboard">
            <i class="bi bi-arrow-left me-1"></i>
            Dashboard
        </a>

    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="container py-5">

    <!-- PAGE HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">

        <div>
            <h2 class="page-title mb-2">
                Food &amp; Beverage Inventory
            </h2>

            <p class="page-description mb-0">
                Keep track of your ingredients, beverages and stock levels.
            </p>
        </div>

        <a href="{{ route('inventory.create') }}" class="btn btn-add">
            <i class="bi bi-plus-circle me-2"></i>
            Add Inventory
        </a>

    </div>

    <!-- SUCCESS MESSAGE -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    <!-- SUMMARY CARDS -->
    <div class="row g-3 mb-4">

        <!-- TOTAL INVENTORY -->
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-content">

                    <div class="summary-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <div>
                        <div class="summary-number">{{ $totalItems }}</div>
                        <div class="summary-label">Total Inventory</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- FOOD ITEMS -->
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-content">

                    <div class="summary-icon">
                        <i class="bi bi-egg-fried"></i>
                    </div>

                    <div>
                        <div class="summary-number">{{ $foodCount }}</div>
                        <div class="summary-label">Food Items</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- BEVERAGES -->
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-content">

                    <div class="summary-icon">
                        <i class="bi bi-cup-straw"></i>
                    </div>

                    <div>
                        <div class="summary-number">{{ $beverageCount }}</div>
                        <div class="summary-label">Beverages</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- LOW STOCK -->
        <div class="col-6 col-lg-3">
            <div class="summary-card summary-danger">
                <div class="summary-content">

                    <div class="summary-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div>
                        <div class="summary-number">{{ $lowStockCount }}</div>
                        <div class="summary-label">Low Stock Items</div>

                        @if ($lowStockCount > 0)
                            <span class="attention-badge">
                                Attention Required
                            </span>
                        @endif
                    </div>

                </div>
            </div>
        </div>

    </div>

    <!-- LOW STOCK ALERTS -->
    <section class="section-card mb-4">

        <div class="section-header d-flex justify-content-between align-items-center gap-3">

            <div>
                <h3 class="section-title">
                    <i class="bi bi-exclamation-triangle-fill alert-icon me-2"></i>
                    Low Stock Alerts
                </h3>

                <p class="section-description">
                    Items that have reached or fallen below their minimum stock level.
                </p>
            </div>

            <span class="alert-count">
                {{ $lowStockCount }} Alerts
            </span>

        </div>

        @forelse ($lowStockItems as $item)

            <div class="alert-item">

                <div>
                    <div class="alert-item-name">
                        {{ $item->item_name }}
                    </div>

                    <div class="alert-item-detail">
                        Current:
                        <strong>
                            {{ number_format((float) $item->quantity, 2) }}
                            {{ $unitLabels[$item->unit] ?? $item->unit }}
                        </strong>

                        &nbsp; | &nbsp;

                        Minimum:
                        {{ number_format((float) $item->minimum_stock, 2) }}
                        {{ $unitLabels[$item->unit] ?? $item->unit }}
                    </div>
                </div>

                <a href="{{ route('inventory.edit', $item->id) }}"
                   class="btn-view">
                    <i class="bi bi-pencil-square me-1"></i>
                    View Item
                </a>

            </div>

        @empty

            <div class="empty-message">
                <i class="bi bi-check-circle-fill text-success me-2"></i>
                All inventory items are currently above their minimum stock levels.
            </div>

        @endforelse

    </section>

    <!-- INVENTORY TABLE -->
    <section class="section-card">

        <div class="section-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">

                <div>
                    <h3 class="section-title">
                        <i class="bi bi-box-seam me-2"></i>
                        Inventory Items
                    </h3>

                    <p class="section-description">
                        Browse, search and manage your inventory records.
                    </p>
                </div>

                <span class="category-badge total-badge">
                    {{ $totalItems }} Total Items
                </span>

            </div>

            <!-- SEARCH AND FILTER -->
            <div class="row g-2">

                <div class="col-12 col-md-6">
                    <div class="search-box">

                        <i class="bi bi-search"></i>

                        <input type="text"
                               id="inventorySearch"
                               class="form-control search-input"
                               placeholder="Search inventory items..."
                               aria-label="Search inventory items">

                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select id="categoryFilter"
                            class="form-select filter-select"
                            aria-label="Filter by category">

                        <option value="all">All Categories</option>
                        <option value="food">Food</option>
                        <option value="beverage">Beverage</option>

                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <select id="stockFilter"
                            class="form-select filter-select"
                            aria-label="Filter by stock status">

                        <option value="all">All Stock</option>
                        <option value="low">Low Stock</option>
                        <option value="in">In Stock</option>

                    </select>
                </div>

            </div>

        </div>

        <!-- TABLE -->
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

                <tbody id="inventoryTableBody">

                    @foreach ($inventoryItems as $item)

                        @php
                            $isLowStock = $item->quantity <= $item->minimum_stock;
                        @endphp

                        <tr class="inventory-row"
                            data-name="{{ strtolower($item->item_name) }}"
                            data-category="{{ strtolower($item->category) }}"
                            data-stock="{{ $isLowStock ? 'low' : 'in' }}">

                            <!-- ITEM NAME -->
                            <td>
                                <span class="item-name">
                                    {{ $item->item_name }}
                                </span>
                            </td>

                            <!-- CATEGORY -->
                            <td>
                                @if ($item->category === 'Food')

                                    <span class="category-badge category-food">
                                        <i class="bi bi-egg-fried"></i>
                                        Food
                                    </span>

                                @else

                                    <span class="category-badge category-beverage">
                                        <i class="bi bi-cup-straw"></i>
                                        {{ $item->category }}
                                    </span>

                                @endif
                            </td>

                            <!-- QUANTITY -->
                            <td>
                                <span class="quantity-display {{ $isLowStock ? 'quantity-low' : '' }}">

                                    <span class="quantity-number">
                                        {{ number_format((float) $item->quantity, 2) }}
                                    </span>

                                    <span class="unit-badge">
                                        {{ $unitLabels[$item->unit] ?? $item->unit }}
                                    </span>

                                </span>
                            </td>

                            <!-- UNIT PRICE -->
                            <td>
                                <strong>
                                    {{ number_format((float) $item->unit_price, 2) }}
                                </strong>
                            </td>

                            <!-- STOCK STATUS -->
                            <td>
                                @if ($isLowStock)

                                    <span class="badge stock-low">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        Low Stock
                                    </span>

                                @else

                                    <span class="badge stock-in">
                                        <i class="bi bi-check-circle me-1"></i>
                                        In Stock
                                    </span>

                                @endif
                            </td>

                            <!-- ACTIONS -->
                            <td>
                                <div class="d-flex gap-2">

                                    <a href="{{ route('inventory.edit', $item->id) }}"
                                       class="btn btn-edit btn-sm">

                                        <i class="bi bi-pencil-square me-1"></i>
                                        Edit
                                    </a>

                                    <form action="{{ route('inventory.destroy', $item->id) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this inventory item?');">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="btn btn-delete btn-sm">

                                            <i class="bi bi-trash me-1"></i>
                                            Delete

                                        </button>

                                    </form>

                                </div>
                            </td>

                        </tr>

                    @endforeach

                    <!-- EMPTY SEARCH RESULT -->
                    <tr id="noResultsRow" style="display: none;">
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-search me-2"></i>
                            No matching inventory items found.
                        </td>
                    </tr>

                </tbody>

            </table>

        </div>

        <div class="inventory-footer">
            Showing <span id="visibleCount">{{ $totalItems }}</span>
            of {{ $totalItems }} inventory items
        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const searchInput = document.getElementById('inventorySearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const stockFilter = document.getElementById('stockFilter');
        const rows = document.querySelectorAll('.inventory-row');
        const noResultsRow = document.getElementById('noResultsRow');
        const visibleCount = document.getElementById('visibleCount');

        function filterInventory() {

            const searchValue = searchInput.value.trim().toLowerCase();
            const categoryValue = categoryFilter.value;
            const stockValue = stockFilter.value;

            let count = 0;

            rows.forEach(function (row) {

                const itemName = row.dataset.name;
                const itemCategory = row.dataset.category;
                const itemStock = row.dataset.stock;

                const matchesSearch = itemName.includes(searchValue);

                const matchesCategory =
                    categoryValue === 'all' ||
                    itemCategory === categoryValue;

                const matchesStock =
                    stockValue === 'all' ||
                    itemStock === stockValue;

                const shouldShow =
                    matchesSearch &&
                    matchesCategory &&
                    matchesStock;

                row.style.display = shouldShow ? '' : 'none';

                if (shouldShow) {
                    count++;
                }

            });

            visibleCount.textContent = count;

            noResultsRow.style.display =
                count === 0 ? '' : 'none';
        }

        searchInput.addEventListener('input', filterInventory);
        categoryFilter.addEventListener('change', filterInventory);
        stockFilter.addEventListener('change', filterInventory);

        filterInventory();

    });
</script>

</body>
</html>
