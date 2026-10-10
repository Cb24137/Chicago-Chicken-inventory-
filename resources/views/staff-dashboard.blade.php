<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Staff Dashboard | Chicago Chicken City</title>
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
            <span class="role">Staff</span>

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
        <h1>Staff Dashboard</h1>
        <p class="subtitle">
            Welcome back, {{ $user->name }}!
            Access your inventory management modules.
        </p>
    </header>
    


    <section class="section">
        <h2 class="section-title">Inventory Modules</h2>

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