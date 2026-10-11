
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Inventory | Chicago Chicken City</title>

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

        .btn-back {
            background-color: #fff8ef;
            color: #4a2410;
            border: none;
            border-radius: 9px;
            padding: 10px 20px;
        }

        .btn-back:hover {
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

        /* Form card */
        .form-card {
            background-color: #ffffff;
            border: 1px solid #eee5da;
            border-radius: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.03);
        }

        /* Form fields */
        .form-label {
            color: #4a2410;
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border: 1px solid #e5ddd2;
            border-radius: 9px;
            padding: 11px 14px;
            color: #5b4637;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #dc6500;
            box-shadow: 0 0 0 0.2rem rgba(220, 101, 0, 0.12);
        }

        /* Update button */
        .btn-update {
            background-color: #dc6500;
            color: #ffffff;
            border: none;
            border-radius: 9px;
            padding: 11px 22px;
            font-weight: 500;
        }

        .btn-update:hover {
            background-color: #b95200;
            color: #ffffff;
        }

        /* Cancel button */
        .btn-cancel {
            background-color: #fff8ef;
            color: #4a2410;
            border: none;
            border-radius: 9px;
            padding: 11px 22px;
        }

        .btn-cancel:hover {
            background-color: #f5e9d8;
            color: #4a2410;
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

            <a href="{{ route('inventory.index') }}"
               class="btn btn-back">
                Back to Inventory
            </a>

        </div>
    </nav>

    <!-- Main content -->
    <div class="container py-5">

        <h2 class="page-title">
            Edit Inventory Item
        </h2>

        <p class="page-description">
            Update food or beverage inventory details.
        </p>

        <!-- Form card -->
        <div class="card form-card mt-4">
            <div class="card-body p-4">

                <!-- Validation errors -->
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('inventory.update', $inventoryItem->id) }}"
                      method="POST">

                    @csrf
                    @method('PUT')

                    <!-- Item Name -->
                    <div class="mb-3">
                        <label class="form-label">
                            Item Name
                        </label>

                        <input type="text"
                               name="item_name"
                               class="form-control"
                               value="{{ old('item_name', $inventoryItem->item_name) }}"
                               required>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label class="form-label">
                            Category
                        </label>

                        <select name="category"
                                class="form-select"
                                required>

                            <option value="Food"
                                @selected(old('category', $inventoryItem->category) === 'Food')>
                                Food
                            </option>

                            <option value="Beverage"
                                @selected(old('category', $inventoryItem->category) === 'Beverage')>
                                Beverage
                            </option>

                        </select>
                    </div>

                    <!-- Unit -->
                    <div class="mb-3">
                        <label class="form-label">
                            Unit
                        </label>

                        <select name="unit"
                                class="form-select"
                                required>

                            <option value="kg"
                                @selected(old('unit', $inventoryItem->unit) === 'kg')>
                                Kilogram (kg)
                            </option>

                            <option value="litre"
                                @selected(old('unit', $inventoryItem->unit) === 'litre')>
                                Litre
                            </option>

                            <option value="pcs"
                                @selected(old('unit', $inventoryItem->unit) === 'pcs')>
                                Pieces (pcs)
                            </option>

                        </select>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label class="form-label">
                            Quantity
                        </label>

                        <input type="number"
                               name="quantity"
                               class="form-control"
                               value="{{ old('quantity', $inventoryItem->quantity) }}"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <!-- Minimum Stock -->
                    <div class="mb-3">
                        <label class="form-label">
                            Minimum Stock
                        </label>

                        <input type="number"
                               name="minimum_stock"
                               class="form-control"
                               value="{{ old('minimum_stock', $inventoryItem->minimum_stock) }}"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <!-- Unit Price -->
                    <div class="mb-3">
                        <label class="form-label">
                            Unit Price (RM)
                        </label>

                        <input type="number"
                               name="unit_price"
                               class="form-control"
                               value="{{ old('unit_price', $inventoryItem->unit_price) }}"
                               min="0"
                               step="0.01"
                               required>
                    </div>

                    <!-- Action buttons -->
                    <div class="d-flex gap-2 flex-wrap mt-4">

                        <button type="submit"
                                class="btn btn-update">
                            Update Inventory
                        </button>

                        <a href="{{ route('inventory.index') }}"
                           class="btn btn-cancel">
                            Cancel
                        </a>

                    </div>

                </form>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
