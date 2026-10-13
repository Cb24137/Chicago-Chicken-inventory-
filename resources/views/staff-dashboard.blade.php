
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Staff Dashboard | Chicago Chicken City</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <script src="{{ asset('js/auth-history.js') }}" defer></script>

    @php
        $inventoryItems = \Illuminate\Support\Facades\Schema::hasTable('inventory_items')
            ? \App\Models\InventoryItem::orderBy('item_name')->get()
            : collect();

        $totalItems = $inventoryItems->count();

        $lowStockItems = $inventoryItems->filter(function ($item) {
            return $item->quantity <= $item->minimum_stock;
        });

        $lowStockCount = $lowStockItems->count();

        $supplierCount = \Illuminate\Support\Facades\Schema::hasTable('suppliers')
            ? \Illuminate\Support\Facades\DB::table('suppliers')->count()
            : null;

        $storageCount = \Illuminate\Support\Facades\Schema::hasTable('storage_locations')
            ? \Illuminate\Support\Facades\DB::table('storage_locations')->count()
            : null;

        $recentItems = $inventoryItems
            ->sortByDesc('created_at')
            ->take(5);

        $supplierRoute = \Illuminate\Support\Facades\Route::has('suppliers.index');
        $storageRoute = \Illuminate\Support\Facades\Route::has('storage-locations.index');
        $wasteRoute = \Illuminate\Support\Facades\Route::has('waste-expiry-records.index');
    @endphp

    <style>
        :root {
            --orange: #e86a00;
            --orange-dark: #c95500;
            --brown: #45220f;
            --cream: #fff7ed;
            --background: #faf8f5;
            --border: #f0e2d5;
            --muted: #8b7c72;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: var(--background);
            color: var(--brown);
            font-family: Arial, sans-serif;
        }

        .navbar-custom {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 13px 0;
        }

        .nav-container {
            max-width: 1300px;
            margin: auto;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 25px;
        }

        .brand-logo {
            width: 125px;
            height: auto;
            object-fit: contain;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--brown);
            font-size: 14px;
            padding: 12px 15px;
            border-radius: 25px;
            display: flex;
            align-items: center;
            gap: 9px;
            transition: 0.2s;
        }

        .nav-links a:hover,
        .nav-links a.active {
            background: #fff1e2;
            color: var(--orange);
        }

        .nav-links i {
            font-size: 18px;
        }

        .staff-dropdown {
            border: 1px solid var(--border);
            background: white;
            color: var(--brown);
            border-radius: 25px;
            padding: 10px 15px;
            font-size: 14px;
        }

        .dashboard-container {
            max-width: 1300px;
            margin: auto;
            padding: 28px 24px 60px;
        }

        .welcome-banner {
            background: linear-gradient(110deg, #fff0df, #fff8f1);
            border: 1px solid #f7e6d6;
            border-radius: 18px;
            padding: 34px 38px;
            position: relative;
            overflow: hidden;
            margin-bottom: 22px;
            min-height: 175px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-content {
            position: relative;
            z-index: 2;
        }

        .welcome-label {
            color: #907361;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .welcome-banner h1 {
            font-size: 35px;
            font-weight: 800;
            margin-bottom: 10px;
        }

        .welcome-banner p {
            color: #796b60;
            max-width: 550px;
            font-size: 14px;
            line-height: 1.7;
            margin: 0;
        }

        .banner-decoration {
            font-size: 90px;
            color: var(--orange);
            opacity: 0.8;
            transform: rotate(-12deg);
            margin-right: 50px;
        }

        .stat-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 15px;
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 17px;
            height: 100%;
            min-height: 122px;
        }

        .stat-icon {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #fff2e4;
            color: var(--orange);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 27px;
            flex-shrink: 0;
        }

        .stat-number {
            font-size: 31px;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 7px;
        }

        .stat-label {
            color: var(--muted);
            font-size: 13px;
        }

        .low-stock-stat {
            background: #fff0f0;
            border: 1px solid #ffbcbc;
            position: relative;
        }

        .low-stock-stat .stat-icon {
            background: #ffdada;
            color: #dc2626;
        }

        .low-stock-stat .stat-number,
        .low-stock-stat .stat-label {
            color: #dc2626;
        }

        .attention-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc2626;
            color: white;
            padding: 4px 9px;
            border-radius: 15px;
            font-size: 10px;
            font-weight: bold;
        }

        .dashboard-panel {
            background: white;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 22px;
            height: 100%;
        }

        .panel-title {
            font-size: 18px;
            font-weight: 800;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .panel-title i {
            color: var(--orange);
        }

        .panel-subtitle {
            color: var(--muted);
            font-size: 12px;
            margin-top: 6px;
            margin-bottom: 18px;
        }

        .view-all {
            color: var(--orange);
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
        }

        .view-all:hover {
            color: var(--orange-dark);
        }

        .stock-table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        .stock-table th {
            background: #fff6eb;
            color: var(--brown);
            padding: 14px 12px;
            white-space: nowrap;
        }

        .stock-table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f4ece5;
            vertical-align: middle;
        }

        .stock-table tr:last-child td {
            border-bottom: none;
        }

        .stock-quantity {
            color: #dc2626;
            font-weight: 800;
        }

        .low-badge {
            display: inline-block;
            background: #ffe0e0;
            color: #dc2626;
            padding: 6px 10px;
            border-radius: 7px;
            font-size: 11px;
            font-weight: bold;
        }

        .view-button {
            display: inline-block;
            background: #fff1df;
            color: #a84e00;
            padding: 7px 13px;
            border-radius: 7px;
            text-decoration: none;
            font-size: 11px;
        }

        .view-button:hover {
            background: #ffe2bd;
            color: #a84e00;
        }

        .quick-action {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 15px;
            border-radius: 10px;
            background: #fff3e5;
            color: var(--brown);
            text-decoration: none;
            font-size: 13px;
            font-weight: bold;
            height: 100%;
            transition: 0.2s;
        }

        .quick-action:hover {
            background: #ffe5c8;
            color: var(--brown);
            transform: translateY(-2px);
        }

        .quick-action.primary {
            background: var(--orange);
            color: white;
        }

        .quick-action.primary:hover {
            background: var(--orange-dark);
            color: white;
        }

        .quick-action.disabled-action {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .activity-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f5ede6;
            font-size: 12px;
        }

        .activity-row:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e6f5e9;
            color: #269447;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
        }

        .activity-text {
            flex: 1;
            min-width: 0;
        }

        .activity-time {
            color: #9c9188;
            font-size: 11px;
            white-space: nowrap;
        }

        .empty-state {
            text-align: center;
            padding: 32px 15px;
            color: var(--muted);
            font-size: 13px;
        }

        .empty-state i {
            font-size: 32px;
            display: block;
            margin-bottom: 10px;
        }

        @media (max-width: 991px) {
            .nav-container {
                flex-wrap: wrap;
            }

            .nav-links {
                order: 3;
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
            }

            .nav-links a {
                white-space: nowrap;
            }

            .banner-decoration {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .dashboard-container {
                padding: 20px 14px;
            }

            .welcome-banner {
                padding: 25px;
            }

            .welcome-banner h1 {
                font-size: 27px;
            }

            .stat-card {
                padding: 16px;
            }

            .stat-number {
                font-size: 25px;
            }

            .dashboard-panel {
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <!-- NAVIGATION BAR -->
    <nav class="navbar-custom">
        <div class="nav-container">

            <a href="{{ route('dashboard') }}">
                <img
                    src="{{ asset('images/chicago-logo-transparent.png') }}"
                    alt="Chicago Chicken City"
                    class="brand-logo"
                >
            </a>

            <div class="nav-links">

                <a href="{{ route('dashboard') }}" class="active">
                    <i class="bi bi-house-door"></i>
                    Dashboard
                </a>

                <a href="{{ route('inventory.index') }}">
                    <i class="bi bi-box-seam"></i>
                    Inventory
                </a>

                @if($supplierRoute)
                    <a href="{{ route('suppliers.index') }}">
                        <i class="bi bi-truck"></i>
                        Suppliers
                    </a>
                @endif

                @if($storageRoute)
                    <a href="{{ route('storage-locations.index') }}">
                        <i class="bi bi-building"></i>
                        Storage
                    </a>
                @endif

                @if($wasteRoute)
                    <a href="{{ route('waste-expiry-records.index') }}">
                        <i class="bi bi-trash3"></i>
                        Waste & Expiry
                    </a>
                @endif

            </div>

            <div class="dropdown">

                <button
                    class="staff-dropdown dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                >
                    <i class="bi bi-person-circle me-2"></i>
                    Staff
                </button>

                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <span class="dropdown-item-text">
                            {{ $user->name ?? 'Chicago Staff' }}
                        </span>
                    </li>

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </button>
                        </form>
                    </li>
                </ul>

            </div>

        </div>
    </nav>

    <main class="dashboard-container">

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">

            <div class="welcome-content">

                <div class="welcome-label">
                    WELCOME BACK,
                </div>

                <h1>
                    {{ $user->name ?? 'Chicago Staff' }}!
                </h1>

                <p>
                    Here's your inventory overview.
                    Keep track of your ingredients, beverages
                    and supplies to ensure smooth restaurant operations.
                </p>

            </div>

            <div class="banner-decoration">
                <i class="bi bi-basket2-fill"></i>
            </div>

        </section>

        <!-- SUMMARY CARDS -->
        <section class="row g-3 mb-4">

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-box-seam"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            {{ str_pad($totalItems, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="stat-label">
                            Total Inventory Items
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card low-stock-stat">

                    @if($lowStockCount > 0)
                        <span class="attention-badge">
                            Attention Required
                        </span>
                    @endif

                    <div class="stat-icon">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            {{ str_pad($lowStockCount, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="stat-label">
                            Low Stock Items
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-truck"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            {{ $supplierCount === null ? '—' : str_pad($supplierCount, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="stat-label">
                            Total Suppliers
                        </div>
                    </div>

                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="stat-card">

                    <div class="stat-icon">
                        <i class="bi bi-building"></i>
                    </div>

                    <div>
                        <div class="stat-number">
                            {{ $storageCount === null ? '—' : str_pad($storageCount, 2, '0', STR_PAD_LEFT) }}
                        </div>

                        <div class="stat-label">
                            Storage Locations
                        </div>
                    </div>

                </div>
            </div>

        </section>

        <section class="row g-3">

            <!-- LOW STOCK ALERTS -->
            <div class="col-lg-7">

                <div class="dashboard-panel">

                    <div class="d-flex justify-content-between align-items-start gap-2">

                        <div>
                            <h2 class="panel-title">
                                <i class="bi bi-exclamation-triangle"></i>
                                Low Stock Alerts
                            </h2>

                            <p class="panel-subtitle">
                                Items that have reached or fallen below the minimum stock level.
                            </p>
                        </div>

                        <a href="{{ route('inventory.index') }}" class="view-all">
                            View All
                            <i class="bi bi-arrow-right"></i>
                        </a>

                    </div>

                    <div class="table-responsive">

                        <table class="stock-table">

                            <thead>
                                <tr>
                                    <th>Item Name</th>
                                    <th>Category</th>
                                    <th>Current Stock</th>
                                    <th>Minimum Stock</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse($lowStockItems as $item)

                                    <tr>

                                        <td>
                                            <strong>{{ $item->item_name }}</strong>
                                        </td>

                                        <td>
                                            {{ $item->category }}
                                        </td>

                                        <td class="stock-quantity">
                                            {{ number_format($item->quantity, 2) }}
                                            {{ $item->unit }}
                                        </td>

                                        <td>
                                            {{ number_format($item->minimum_stock, 2) }}
                                            {{ $item->unit }}
                                        </td>

                                        <td>
                                            <span class="low-badge">
                                                Low Stock
                                            </span>
                                        </td>

                                        <td>
                                            <a
                                                href="{{ route('inventory.edit', $item->id) }}"
                                                class="view-button"
                                            >
                                                View
                                            </a>
                                        </td>

                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6">

                                            <div class="empty-state">
                                                <i class="bi bi-check-circle text-success"></i>
                                                Great! No low-stock items at the moment.
                                            </div>

                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

            <!-- RIGHT SIDE -->
            <div class="col-lg-5">

                <!-- QUICK ACTIONS -->
                <div class="dashboard-panel mb-3">

                    <h2 class="panel-title">
                        <i class="bi bi-lightning-charge-fill"></i>
                        Quick Actions
                    </h2>

                    <p class="panel-subtitle">
                        Perform common inventory tasks quickly.
                    </p>

                    <div class="row g-2">

                        <div class="col-sm-6">
                            <a
                                href="{{ route('inventory.create') }}"
                                class="quick-action primary"
                            >
                                <span>
                                    <i class="bi bi-plus-circle me-2"></i>
                                    Add Inventory Item
                                </span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>

                        <div class="col-sm-6">
                            <a
                                href="{{ route('inventory.index') }}"
                                class="quick-action"
                            >
                                <span>
                                    <i class="bi bi-box-seam me-2"></i>
                                    View Inventory
                                </span>
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </div>

                        <div class="col-sm-6">

                            @if($supplierRoute)
                                <a
                                    href="{{ route('suppliers.index') }}"
                                    class="quick-action"
                                >
                                    <span>
                                        <i class="bi bi-truck me-2"></i>
                                        Manage Suppliers
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @else
                                <div class="quick-action disabled-action">
                                    <span>
                                        <i class="bi bi-truck me-2"></i>
                                        Manage Suppliers
                                    </span>
                                </div>
                            @endif

                        </div>

                        <div class="col-sm-6">

                            @if($wasteRoute)
                                <a
                                    href="{{ route('waste-expiry-records.index') }}"
                                    class="quick-action"
                                >
                                    <span>
                                        <i class="bi bi-trash3 me-2"></i>
                                        Record Waste/Expiry
                                    </span>
                                    <i class="bi bi-chevron-right"></i>
                                </a>
                            @else
                                <div class="quick-action disabled-action">
                                    <span>
                                        <i class="bi bi-trash3 me-2"></i>
                                        Record Waste/Expiry
                                    </span>
                                </div>
                            @endif

                        </div>

                    </div>

                </div>

                <!-- RECENT ACTIVITIES -->
                <div class="dashboard-panel">

                    <h2 class="panel-title">
                        <i class="bi bi-clock-history"></i>
                        Recent Activities
                    </h2>

                    <p class="panel-subtitle">
                        Recently added inventory items.
                    </p>

                    @forelse($recentItems as $item)

                        <div class="activity-row">

                            <div class="activity-icon">
                                <i class="bi bi-plus-lg"></i>
                            </div>

                            <div class="activity-text">
                                Added inventory item:
                                <strong>{{ $item->item_name }}</strong>
                            </div>

                            <span class="activity-time">
                                {{ $item->created_at?->diffForHumans() ?? 'Recently' }}
                            </span>

                        </div>

                    @empty

                        <div class="empty-state">
                            <i class="bi bi-clock-history"></i>
                            No recent inventory activities yet.
                        </div>

                    @endforelse

                </div>

            </div>

        </section>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
