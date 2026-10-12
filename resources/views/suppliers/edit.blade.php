
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Supplier | Chicago Chicken City</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f7f5;
            color: #3b1d0b;
        }

        .container {
            max-width: 650px;
            margin: 45px auto;
            padding: 0 20px;
        }

        .card {
            background: white;
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 30px;
        }

        h1 {
            margin-top: 0;
        }

        .subtitle {
            color: #777;
            margin-bottom: 25px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-bottom: 18px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: inherit;
            background: white;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: #e87915;
        }

        .btn {
            padding: 12px 20px;
            border-radius: 7px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            display: inline-block;
        }

        .save {
            background: #e87915;
            color: white;
        }

        .save:hover {
            background: #cc650e;
        }

        .cancel {
            background: #eee;
            color: #333;
        }

        .error {
            color: #b91c1c;
            font-size: 13px;
            margin-top: -12px;
            margin-bottom: 14px;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        @media (max-width: 600px) {
            .card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>

    <!-- Shared Navbar -->
    @include('partials.navbar')

    <div class="container">
        <div class="card">

            <h1>Edit Supplier</h1>

            <p class="subtitle">
                Update supplier information,
                quantity, unit and total cost.
            </p>

            <form
                action="{{ route('suppliers.update', $supplier) }}"
                method="POST">

                @csrf
                @method('PUT')

                <!-- Supplier Name -->
                <label for="name">
                    Supplier Name *
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $supplier->name) }}"
                    required
                    maxlength="255">

                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Phone Number -->
                <label for="phone">
                    Phone Number
                </label>

                <input
                    type="text"
                    id="phone"
                    name="phone"
                    value="{{ old('phone', $supplier->phone) }}"
                    maxlength="30">

                @error('phone')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Email Address -->
                <label for="email">
                    Email Address
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $supplier->email) }}"
                    maxlength="255">

                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Address -->
                <label for="address">
                    Address
                </label>

                <textarea
                    id="address"
                    name="address"
                    rows="3">{{ old('address', $supplier->address) }}</textarea>

                @error('address')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Products Supplied -->
                <label for="products_supplied">
                    Products Supplied
                </label>

                <input
                    type="text"
                    id="products_supplied"
                    name="products_supplied"
                    value="{{ old('products_supplied', $supplier->products_supplied) }}"
                    placeholder="e.g. Frozen Chicken"
                    maxlength="255">

                @error('products_supplied')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Quantity -->
                <label for="quantity">
                    Quantity
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    value="{{ old('quantity', $supplier->quantity) }}"
                    step="0.01"
                    min="0"
                    placeholder="e.g. 20">

                @error('quantity')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Unit -->
                <label for="unit">
                    Unit
                </label>

                <select
                    id="unit"
                    name="unit">

                    <option value="">
                        -- Select Unit --
                    </option>

                    @foreach([
                        'kg' => 'Kilograms (kg)',
                        'g' => 'Grams (g)',
                        'litres' => 'Litres (L)',
                        'ml' => 'Millilitres (mL)',
                        'pieces' => 'Pieces',
                        'boxes' => 'Boxes',
                        'bottles' => 'Bottles'
                    ] as $value => $label)

                        <option
                            value="{{ $value }}"
                            {{ old('unit', $supplier->unit) == $value ? 'selected' : '' }}>

                            {{ $label }}

                        </option>

                    @endforeach

                </select>

                @error('unit')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Total Cost -->
                <label for="total_cost">
                    Total Cost (RM)
                </label>

                <input
                    type="number"
                    id="total_cost"
                    name="total_cost"
                    value="{{ old('total_cost', $supplier->total_cost) }}"
                    step="0.01"
                    min="0"
                    placeholder="e.g. 150.00">

                @error('total_cost')
                    <p class="error">{{ $message }}</p>
                @enderror


                <!-- Action Buttons -->
                <div class="actions">

                    <button
                        type="submit"
                        class="btn save">
                        Update Supplier
                    </button>

                    <a
                        href="{{ route('suppliers.index') }}"
                        class="btn cancel">
                        Cancel
                    </a>

                </div>

            </form>

        </div>
    </div>

</body>
</html>
