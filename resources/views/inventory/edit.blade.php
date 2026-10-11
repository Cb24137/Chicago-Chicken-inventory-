
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

        .form-card {
            border: none;
            border-radius: 12px;
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

        <a href="{{ route('inventory.index') }}"
           class="btn btn-outline-light btn-sm">
            Back to Inventory
        </a>
    </div>
</nav>

<div class="container py-5">

    <h2 class="page-title">Edit Inventory Item</h2>
    <p class="text-muted">
        Update food or beverage inventory details.
    </p>

    <div class="card form-card shadow-sm mt-4">
        <div class="card-body p-4">

            <form action="{{ route('inventory.update', $inventoryItem->id) }}"
                  method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="item_name"
                           class="form-control"
                           value="{{ old('item_name', $inventoryItem->item_name) }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-select" required>
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

                <div class="mb-3">
                    <label class="form-label">Unit</label>
                    <select name="unit" class="form-select" required>
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

                <div class="mb-3">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity"
                           class="form-control"
                           value="{{ old('quantity', $inventoryItem->quantity) }}"
                           min="0" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Minimum Stock</label>
                    <input type="number" name="minimum_stock"
                           class="form-control"
                           value="{{ old('minimum_stock', $inventoryItem->minimum_stock) }}"
                           min="0" step="0.01" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Unit Price (RM)</label>
                    <input type="number" name="unit_price"
                           class="form-control"
                           value="{{ old('unit_price', $inventoryItem->unit_price) }}"
                           min="0" step="0.01" required>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-green">
                        Update Inventory
                    </button>

                    <a href="{{ route('inventory.index') }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>

            </form>

        </div>
    </div>

</div>

</body>
</html>
