<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    // ========================================
    // DISPLAY ALL SUPPLIERS
    // ========================================
    public function index()
    {
        $suppliers = Supplier::latest()->paginate(10);

        return view('suppliers.index', compact('suppliers'));
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'products_supplied' => 'nullable|string|max:255',

            // New fields
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'products_supplied' => 'nullable|string|max:255',

            // New fields
            'quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'total_cost' => 'nullable|numeric|min:0',
        ]);

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
                ->with('error', 'Cannot delete supplier with purchase records.');
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier deleted successfully!');
    }
}
