<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard | Chicago Chicken City</title>
    <link rel="stylesheet"
          href="{{ asset('css/dashboard.css') }}">
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>
<body>

<nav class="navbar">
    <div class="nav-inner">
        <div class="brand">
            <img src="{{ asset('images/chicago-logo-transparent.png') }}"
                 alt="Chicago Chicken City">
            <span>Inventory System</span>
        </div>

        <div class="nav-actions">
            <span class="role">Administrator</span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container">

    <header class="page-header">
        <h1>Admin Dashboard</h1>
        <p class="subtitle">
            Welcome back, {{ $user->name }}!
            Manage your team and business operations.
        </p>
    </header>
    <div style="margin:15px 0 25px">
        <a href="{{ route('storage-locations.index') }}"
           style="display:inline-block;padding:12px 20px;
                  background:#d9600b;color:white;
                  border-radius:9px;text-decoration:none;
                  font-weight:600">
            Open Storage Management &rarr;
        </a>
    </div>


    <!-- ADMIN MANAGEMENT -->
    <section class="section">
        <h2 class="section-title">Administration</h2>

        <div class="admin-feature">
            <div>
                <h2>Staff Management</h2>
                <p>
                    Create staff accounts, view employees
                    and manage access.
                </p>
            </div>

            <a href="{{ route('admin.staff.index') }}"
               class="btn btn-orange">
                Manage Staff &rarr;
            </a>
        </div>

        <div class="stats">
            <div class="stat-card">
                <p>Total Staff Accounts</p>
                <div class="number">{{ $totalStaff }}</div>
                <p>Registered staff members</p>
            </div>

            <div class="stat-card">
                <p>Your Access Level</p>
                <div class="number" style="font-size:25px">
                    Admin
                </div>
                <p>Administrator account</p>
            </div>
        </div>
    </section>

    <!-- INVENTORY MODULES -->
    <section class="section">
        <h2 class="section-title">Inventory Management</h2>

        <div class="grid">

            <div class="card">
                <span class="module-label">MODULE 01</span>
                <h3>Food &amp; Beverage Inventory</h3>
                <p>
                    Manage ingredients, beverages
                    and stock levels.
                </p>
            </div>

            <div class="card">
                <span class="module-label">MODULE 02</span>
                <h3>Supplier &amp; Purchase Management</h3>
                <p>
                    Manage suppliers and purchase records.
                </p>
            </div>

            <a href="{{ route('storage-locations.index') }}" class="card module-link">
                <span class="module-label">MODULE 03</span>
                <h3>Food Storage &amp; Inventory Location</h3>
                <p>
                    Track storage locations and stock placement.
                </p>
            </a>

            <div class="card">
                <span class="module-label">MODULE 04</span>
                <h3>Waste &amp; Expiry Management</h3>
                <p>
                    Monitor expired items and food wastage.
                </p>
            </div>

        </div>
    </section>

</main>
</body>
</html>