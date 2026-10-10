
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard | Chicago Chicken City</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <style>
        :root {
            --orange: #f7941d;
            --brown: #44210f;
            --cream: #fff8ef;
            --background: #f8f7f4;
            --muted: #81776e;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--background);
            font-family: 'Segoe UI', Arial, sans-serif;
            color: var(--brown);
        }

        /* Navbar */
        .navbar {
            background: white;
            border-bottom: 1px solid #eee7df;
            padding: 14px 0;
        }

        .navbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 17px;
            font-weight: 700;
            color: var(--brown);
        }

        .navbar-brand img {
            width: 100px;
            height: auto;
        }

        .btn-logout {
            border: 1px solid #eadfd5;
            color: var(--brown);
            border-radius: 8px;
            padding: 8px 18px;
            background: white;
            font-weight: 600;
            font-size: 13px;
        }

        .btn-logout:hover {
            background: #fff1e2;
            border-color: var(--orange);
        }

        /* Dashboard Header */
        .dashboard-header {
            margin-bottom: 35px;
        }

        .dashboard-title {
            font-size: 30px;
            font-weight: 800;
            margin-bottom: 9px;
        }

        .welcome-text {
            color: var(--muted);
            font-size: 14px;
        }

        .role-badge {
            display: inline-block;
            padding: 7px 15px;
            border-radius: 20px;
            background: #fff0d8;
            color: #b45b08;
            font-size: 12px;
            font-weight: 700;
        }

        /* Section */
        .section-title {
            font-size: 19px;
            font-weight: 750;
            margin-bottom: 20px;
        }

        /* Dashboard Cards */
        .dashboard-card {
            background: white;
            border: 1px solid #eee7df;
            border-radius: 15px;
            padding: 26px;
            height: 100%;
            transition: 0.2s ease;
        }

        .dashboard-card:hover {
            transform: translateY(-3px);
            border-color: #f5c78f;
            box-shadow: 0 10px 25px rgba(68,33,15,0.07);
        }

        .card-number {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            color: #c5690a;
            background: #fff3e3;
            border-radius: 7px;
            padding: 6px 12px;
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 17px;
            font-weight: 750;
            color: var(--brown);
            margin-bottom: 12px;
        }

        .card-description {
            color: var(--muted);
            font-size: 14px;
            line-height: 1.7;
            margin-bottom: 0;
        }

        /* Admin Management */
        .admin-section {
            margin-top: 45px;
        }

        .admin-card {
            background: #fff6e9;
            border: 1px solid #f5dfc2;
            border-radius: 15px;
            padding: 28px;
        }

        .admin-card h5 {
            font-size: 18px;
            font-weight: 750;
            margin-bottom: 10px;
        }

        .admin-card p {
            font-size: 14px;
            color: var(--muted);
            margin-bottom: 0;
        }

        .coming-soon {
            display: inline-block;
            background: white;
            color: #ad6416;
            padding: 9px 15px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #f1d5b5;
        }

        @media (max-width: 768px) {
            .dashboard-title {
                font-size: 25px;
            }

            .navbar-brand img {
                width: 75px;
            }

            .dashboard-card {
                padding: 22px;
            }
        }
    </style>
    <script src="{{ asset('js/auth-history.js') }}" defer></script>
</head>

<body>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="container">

            <div class="navbar-brand">
                <img
                    src="{{ asset('images/chicago-logo-transparent.png') }}"
                    alt="Chicago Chicken City"
                >
                <span>Inventory System</span>
            </div>

            <div class="d-flex align-items-center gap-3">

                <span class="role-badge">
                    {{ $isAdmin ? 'Admin' : 'Staff' }}
                </span>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn-logout">
                        Logout
                    </button>
                </form>

            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="container py-5">

        <!-- HEADER -->
        <div class="dashboard-header">

            <h1 class="dashboard-title">
                {{ $isAdmin ? 'Admin Dashboard' : 'Staff Dashboard' }}
            </h1>

            <p class="welcome-text">
                Welcome back, {{ $user->name }}!
            </p>

        </div>

        <!-- MODULES -->
        <h2 class="section-title">
            Inventory Management Modules
        </h2>

        <div class="row g-4">

            <!-- MODULE 1 -->
            <div class="col-md-6">
                <div class="dashboard-card">

                    <span class="card-number">MODULE 01</span>

                    <h5 class="card-title">
                        Food &amp; Beverage Inventory
                    </h5>

                    <p class="card-description">
                        Manage ingredients, beverages
                        and stock levels.
                    </p>

                </div>
            </div>

            <!-- MODULE 2 -->
            <div class="col-md-6">
                <div class="dashboard-card">

                    <span class="card-number">MODULE 02</span>

                    <h5 class="card-title">
                        Supplier &amp; Purchase Management
                    </h5>

                    <p class="card-description">
                        Manage suppliers and purchase records.
                    </p>

                </div>
            </div>

            <!-- MODULE 3 -->
            <div class="col-md-6">
                <div class="dashboard-card">

                    <span class="card-number">MODULE 03</span>

                    <h5 class="card-title">
                        Food Storage &amp; Inventory Location
                    </h5>

                    <p class="card-description">
                        Track storage locations and stock placement.
                    </p>

                </div>
            </div>

            <!-- MODULE 4 -->
            <div class="col-md-6">
                <div class="dashboard-card">

                    <span class="card-number">MODULE 04</span>

                    <h5 class="card-title">
                        Waste &amp; Expiry Management
                    </h5>

                    <p class="card-description">
                        Monitor expired items and food wastage.
                    </p>

                </div>
            </div>

        </div>

        <!-- ADMIN ONLY -->
        @if ($isAdmin)

            <section class="admin-section">

                <h2 class="section-title">
                    Administration
                </h2>

                <div class="admin-card">

                    <div class="d-flex flex-wrap
                                justify-content-between
                                align-items-center gap-3">

                        <div>
                            <h5>Staff Management</h5>

                            <p>
                                Create and manage staff accounts
                                and access permissions.
                            </p>
                        </div>

                        <a href="{{ route('admin.staff.index') }}" class="coming-soon" style="text-decoration:none;">Manage Staff</a>

                    </div>

                </div>

            </section>

        @endif

    </main>

</body>
</html>
