<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // ========================================
    // DISPLAY ALL SUPPLIERS
    // ========================================
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $suppliers = Supplier::withCount('purchases')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    foreach (['name', 'phone', 'email', 'products_supplied'] as $column) {
                        $query->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Summary cards: totals for all suppliers, whatever the search.
        // Order value is the value of orders placed (not cancelled), not money paid.
        $linkedOrders = fn () => Purchase::query()->has('supplier');

        $summary = [
            'suppliers' => Supplier::count(),
            'orders' => $linkedOrders()->count(),
            'withOrders' => $linkedOrders()->distinct()->count('supplier_id'),
            'orderValue' => (float) $linkedOrders()->where('status', '!=', 'cancelled')->sum('total_cost'),
        ];

        return view('suppliers.index', compact('suppliers', 'search', 'summary'));
    }

    // ========================================
    // SHOW ADD SUPPLIER FORM
    // ========================================
    public function create()
    {
        return view('suppliers.create');
    }

    // ========================================
    // SAVE NEW SUPPLIER
    // ========================================
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier added successfully!');
    }

    // ========================================
    // SHOW EDIT SUPPLIER FORM
    // ========================================
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    // ========================================
    // UPDATE SUPPLIER
    // ========================================
    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate($this->rules());

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier updated successfully!');
    }

    // ========================================
    // DELETE SUPPLIER
    // ========================================
    public function destroy(Supplier $supplier)
    {
        // Prevent deleting suppliers with purchase records
        if ($supplier->purchases()->exists()) {
            return redirect()
                ->route('suppliers.index')
                ->with('error', "Cannot delete {$supplier->name} because it has purchase orders.");
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }

    // Supplier details only. Quantities and costs are recorded
    // on purchase orders (PurchaseController), not on suppliers.
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'products_supplied' => 'nullable|string|max:255',
        ];
    }
}
