
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
            background: #faf9f6;
            font-family: Arial, sans-serif;
            color: #4a2410;
        }

        /* NAVBAR */
        .navbar {
            background: #ffffff;
            border-bottom: 1px solid #eee5da;
            padding: 12px 0;
        }

        .navbar-brand img {
            height: 65px;
            width: auto;
            object-fit: contain;
        }

        .btn-back,
        .btn-cancel {
            background: #fff8ef;
            color: #4a2410;
            border: none;
            border-radius: 10px;
            padding: 11px 20px;
        }

        .btn-back:hover,
        .btn-cancel:hover {
            background: #f5e9d8;
            color: #4a2410;
        }

        /* PAGE */
        .page-title {
            font-weight: 700;
            color: #4a2410;
        }

        .page-description {
            color: #938c83;
        }

        /* FORM CARD */
        .form-card {
            background: #ffffff;
            border: 1px solid #eee5da;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(74, 36, 16, 0.04);
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 9px;
            color: #4a2410;
        }

        .form-control,
        .form-select {
            border: 1px solid #e5ddd2;
            border-radius: 10px;
            padding: 12px 15px;
            color: #4a2410;
            background-color: #ffffff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #dc6500;
            box-shadow: 0 0 0 .2rem rgba(220, 101, 0, .12);
        }

        .field-hint {
            font-size: 12px;
            color: #938c83;
            margin-top: 6px;
        }

        /* CATEGORY CARDS */
        .category-option {
            position: relative;
            display: block;
            cursor: pointer;
            height: 100%;
        }

        .category-option input {
            position: absolute;
            opacity: 0;
            width: 1px;
            height: 1px;
        }

        .category-content {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 17px;
            border: 1.5px solid #e9dfd3;
            border-radius: 13px;
            background: #fffdfb;
            transition: .2s ease;
            height: 100%;
        }

        .category-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #fff1df;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            flex-shrink: 0;
        }

        .category-name {
            font-weight: 700;
            margin-bottom: 3px;
        }

        .category-desc {
            font-size: 12px;
            color: #938c83;
        }

        .category-option input:checked + .category-content {
            border-color: #dc6500;
            background: #fff7ed;
            box-shadow: 0 0 0 2px rgba(220, 101, 0, .08);
        }

        .category-option input:focus-visible + .category-content {
            outline: 3px solid rgba(220, 101, 0, .35);
            outline-offset: 3px;
        }

        .category-content:hover {
            border-color: #dc6500;
        }

        /* SECTIONS */
        .section-label {
            color: #a55a23;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 18px;
        }

        .section-divider {
            border-top: 1px solid #f0e7dc;
            margin: 26px 0;
        }

        /* BUTTONS */
        .btn-update {
            background: #dc6500;
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
        }

        .btn-update:hover {
            background: #b95200;
            color: #ffffff;
        }

        /* RESPONSIVE */
        @media (max-width: 576px) {
            .navbar-brand img {
                height: 50px;
            }

            .page-title {
                font-size: 25px;
            }

            .category-content {
                padding: 12px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="container">

        <a class="navbar-brand"
           href="{{ route('dashboard') }}">

            <img src="{{ asset('images/chicago-logo-transparent.png') }}"
                 alt="Chicago Chicken City Logo">

        </a>

        <a href="{{ route('inventory.index') }}"
           class="btn btn-back">

            ← Back to Inventory

        </a>

    </div>
</nav>

<!-- MAIN CONTENT -->
<main class="container py-5">

    <h2 class="page-title">
        Edit Inventory Item
    </h2>

    <p class="page-description">
        Update food or beverage inventory details.
    </p>

    <!-- FORM CARD -->
    <div class="card form-card mt-4">
        <div class="card-body p-4 p-md-5">

            <!-- VALIDATION ERRORS -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- EDIT FORM -->
            <form action="{{ route('inventory.update', $inventoryItem->id) }}"
                  method="POST">

                @csrf
                @method('PUT')

                <!-- ITEM INFORMATION -->
                <div class="section-label">
                    Item Information
                </div>

                <!-- ITEM NAME -->
                <div class="mb-4">

                    <label for="item_name" class="form-label">
                        Item Name
                    </label>

                    <input type="text"
                           id="item_name"
                           name="item_name"
                           class="form-control"
                           value="{{ old('item_name', $inventoryItem->item_name) }}"
                           placeholder="e.g. Chicken, French Fries, Cheese"
                           required>

                </div>

                <!-- CATEGORY -->
                <div class="mb-4">

                    <label class="form-label d-block">
                        Category
                    </label>

                    <div class="row g-3">

                        <!-- FOOD -->
                        <div class="col-md-6">

                            <label class="category-option">

                                <input type="radio"
                                       name="category"
                                       value="Food"
                                       @checked(old('category', $inventoryItem->category) === 'Food')
                                       required>

                                <span class="category-content">

                                    <span class="category-icon">
                                        🍗
                                    </span>

                                    <span>
                                        <span class="category-name d-block">
                                            Food
                                        </span>

                                        <span class="category-desc d-block">
                                            Ingredients, fries, cheese, vegetables
                                        </span>
                                    </span>

                                </span>

                            </label>

                        </div>

                        <!-- BEVERAGE -->
                        <div class="col-md-6">

                            <label class="category-option">

                                <input type="radio"
                                       name="category"
                                       value="Beverage"
                                       @checked(old('category', $inventoryItem->category) === 'Beverage')
                                       required>

                                <span class="category-content">

                                    <span class="category-icon">
                                        🥤
                                    </span>

                                    <span>
                                        <span class="category-name d-block">
                                            Beverage
                                        </span>

                                        <span class="category-desc d-block">
                                            Soft drinks, juice, water and other drinks
                                        </span>
                                    </span>

                                </span>

                            </label>

                        </div>

                    </div>

                </div>

                <div class="section-divider"></div>

                <!-- STOCK DETAILS -->
                <div class="section-label">
                    Stock Details
                </div>

                <div class="row g-4">

                    <!-- MEASUREMENT UNIT -->
                    <div class="col-md-6">

                        <label for="unit" class="form-label">
                            Measurement Unit
                        </label>

                        <select id="unit"
                                name="unit"
                                class="form-select"
                                required>

                            <option value="">
                                Choose a unit
                            </option>

                            <optgroup label="Weight">

                                <option value="kg"
                                    @selected(old('unit', $inventoryItem->unit) === 'kg')>
                                    Kilogram (kg)
                                </option>

                                <option value="g"
                                    @selected(old('unit', $inventoryItem->unit) === 'g')>
                                    Gram (g)
                                </option>

                            </optgroup>

                            <optgroup label="Volume">

                                <option value="litre"
                                    @selected(old('unit', $inventoryItem->unit) === 'litre')>
                                    Litre (L)
                                </option>

                                <option value="ml"
                                    @selected(old('unit', $inventoryItem->unit) === 'ml')>
                                    Millilitre (ml)
                                </option>

                            </optgroup>

                            <optgroup label="Quantity / Packaging">

                                <option value="pcs"
                                    @selected(old('unit', $inventoryItem->unit) === 'pcs')>
                                    Pieces (pcs)
                                </option>

                                <option value="pack"
                                    @selected(old('unit', $inventoryItem->unit) === 'pack')>
                                    Pack
                                </option>

                                <option value="bottle"
                                    @selected(old('unit', $inventoryItem->unit) === 'bottle')>
                                    Bottle
                                </option>

                                <option value="can"
                                    @selected(old('unit', $inventoryItem->unit) === 'can')>
                                    Can
                                </option>

                                <option value="box"
                                    @selected(old('unit', $inventoryItem->unit) === 'box')>
                                    Box
                                </option>

                                <option value="bag"
                                    @selected(old('unit', $inventoryItem->unit) === 'bag')>
                                    Bag
                                </option>

                            </optgroup>

                        </select>

                        <div class="field-hint">
                            Choose how this item is measured or counted.
                        </div>

                    </div>

                    <!-- CURRENT QUANTITY -->
                    <div class="col-md-6">

                        <label for="quantity" class="form-label">
                            Current Quantity
                        </label>

                        <input type="number"
                               id="quantity"
                               name="quantity"
                               class="form-control"
                               value="{{ old('quantity', $inventoryItem->quantity) }}"
                               min="0"
                               step="0.01"
                               placeholder="e.g. 20"
                               required>

                        <div class="field-hint">
                            Available stock currently in inventory.
                        </div>

                    </div>

                    <!-- MINIMUM STOCK -->
                    <div class="col-md-6">

                        <label for="minimum_stock" class="form-label">
                            Minimum Stock Level
                        </label>

                        <input type="number"
                               id="minimum_stock"
                               name="minimum_stock"
                               class="form-control"
                               value="{{ old('minimum_stock', $inventoryItem->minimum_stock) }}"
                               min="0"
                               step="0.01"
                               placeholder="e.g. 5"
                               required>

                        <div class="field-hint">
                            A low-stock alert appears when stock reaches this level.
                        </div>

                    </div>

                    <!-- UNIT PRICE -->
                    <div class="col-md-6">

                        <label for="unit_price" class="form-label">
                            Unit Price (RM)
                        </label>

                        <div class="input-group">

                            <span class="input-group-text"
                                  style="background:#fff8ef;border-color:#e5ddd2;">
                                RM
                            </span>

                            <input type="number"
                                   id="unit_price"
                                   name="unit_price"
                                   class="form-control"
                                   value="{{ old('unit_price', $inventoryItem->unit_price) }}"
                                   min="0"
                                   step="0.01"
                                   placeholder="0.00"
                                   required>

                        </div>

                        <div class="field-hint">
                            Price per selected measurement unit.
                        </div>

                    </div>

                </div>

                <div class="section-divider"></div>

                <!-- ACTION BUTTONS -->
                <div class="d-flex gap-2 flex-wrap">

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

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
