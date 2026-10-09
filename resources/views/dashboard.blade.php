<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Chicago Chicken City</title>

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

        .navbar-brand {
            font-weight: bold;
            color: white !important;
        }

        .dashboard-card {
            border: none;
            border-radius: 12px;
            transition: 0.2s;
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
        }

        .card-title {
            color: #173d30;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-dark">
        <div class="container">
            <span class="navbar-brand">
                Chicago Chicken City
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="btn btn-outline-light btn-sm">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container py-5">

        <h2 class="fw-bold">Staff Dashboard</h2>

        <p class="text-muted mb-4">
            Welcome, {{ auth()->user()->name }}!
        </p>

        <div class="row g-4">

            <div class="col-md-6">
                <div class="card dashboard-card shadow-sm p-4">
                    <h5 class="card-title">
                        Food & Beverage Inventory
                    </h5>
                    <p>Manage ingredients, beverages and stock levels.</p>
                    <span class="text-muted small">
                        Module 1
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card shadow-sm p-4">
                    <h5 class="card-title">
                        Supplier & Purchase Management
                    </h5>
                    <p>Manage suppliers and purchase records.</p>
                    <span class="text-muted small">
                        Module 2
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card shadow-sm p-4">
                    <h5 class="card-title">
                        Food Storage & Inventory Location
                    </h5>
                    <p>Track storage locations and stock placement.</p>
                    <span class="text-muted small">
                        Module 3
                    </span>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card dashboard-card shadow-sm p-4">
                    <h5 class="card-title">
                        Waste & Expiry Management
                    </h5>
                    <p>Monitor expired items and food wastage.</p>
                    <span class="text-muted small">
                        Module 4
                    </span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
