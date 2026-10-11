
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Supplier Management | Chicago Chicken City</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f8f7f5;
            color: #3b1d0b;
        }

        .container {
            max-width: 1300px;
            margin: 40px auto;
            padding: 20px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        h1 {
            margin-bottom: 5px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border-radius: 7px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            white-space: nowrap;
        }

        .orange {
            background: #e87915;
            color: white;
        }

        .orange:hover {
            background: #cc650e;
        }

        .edit {
            background: #fff0df;
            color: #9a4800;
        }

        .delete {
            background: #fee2e2;
            color: #b91c1c;
        }

        .back {
            background: #eee;
            color: #333;
        }

        .table-box {
            margin-top: 25px;
            background: white;
            border-radius: 12px;
            border: 1px solid #eee;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            text-align: left;
            font-size: 14px;
        }

        th {
            background: #fff7ed;
            color: #3b1d0b;
            white-space: nowrap;
        }

        tr:hover {
            background: #fffaf5;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .actions form {
            margin: 0;
        }

        .alert {
            padding: 12px;
            margin-top: 15px;
            border-radius: 7px;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #b91c1c;
        }

        .cost {
            font-weight: bold;
            color: #b45309;
            white-space: nowrap;
        }

        .quantity {
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
        }
    </style>
</head>

<body>

    <!-- Shared Navbar -->
    @include('partials.navbar')

    <div class="container">

        <div class="top">
            <div>
                <h1>Supplier Management</h1>
                <p>
                    Manage suppliers, quantities,
                    units and purchase costs.
                </p>
            </div>

            <a href="{{ route('suppliers.create') }}"
               class="btn orange">
                + Add Supplier
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert success">
                {{ session('success') }}
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="alert error">
                {{ session('error') }}
            </div>
        @endif

        <!-- Suppliers Table -->
        <div class="table-box">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Supplier Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th>Products Supplied</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th>Total Cost (RM)</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($suppliers as $supplier)

                        <tr>
                            <td>
                                {{ $supplier->id }}
                            </td>

                            <td>
                                <strong>
                                    {{ $supplier->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $supplier->phone ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->email ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->address ?? '-' }}
                            </td>

                            <td>
                                {{ $supplier->products_supplied ?? '-' }}
                            </td>

                            <!-- Quantity -->
                            <td class="quantity">
                                {{ $supplier->quantity !== null
                                    ? number_format($supplier->quantity, 2)
                                    : '-' }}
                            </td>

                            <!-- Unit -->
                            <td>
                                {{ $supplier->unit ?? '-' }}
                            </td>

                            <!-- Total Cost -->
                            <td class="cost">
                                {{ $supplier->total_cost !== null
                                    ? 'RM ' . number_format($supplier->total_cost, 2)
                                    : '-' }}
                            </td>

                            <!-- Actions -->
                            <td>
                                <div class="actions">

                                    <a
                                        href="{{ route('suppliers.edit', $supplier) }}"
                                        class="btn edit">
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('suppliers.destroy', $supplier) }}"
                                        method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this supplier?');">

                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn delete">
                                            Delete
                                        </button>

                                    </form>

                                </div>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="10"
                                style="text-align:center; padding:30px;">
                                No suppliers found.
                                Add your first supplier.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>

        </div>

        <!-- Pagination -->
        <div style="margin-top:20px;">
            {{ $suppliers->links() }}
        </div>

        <!-- Back Button -->
        <div class="footer">
            <a href="{{ route('dashboard') }}"
               class="btn back">
                Back to Dashboard
            </a>
        </div>

    </div>

</body>
</html>
