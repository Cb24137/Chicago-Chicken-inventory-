<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    // Display all purchases
    public function index()
    {
        $purchases = Purchase::with('supplier')
            ->latest()
            ->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    // Show add purchase form
    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.create', compact('suppliers'));
    }

    // Save new purchase
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id'),
            ],
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|gt:0',
            'unit' => 'required|string|max:50',
            'total_cost' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        Purchase::create($validated);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase added successfully!');
    }

    // Show edit purchase form
    public function edit(Purchase $purchase)
    {
        $suppliers = Supplier::orderBy('name')->get();

        return view('purchases.edit', compact('purchase', 'suppliers'));
    }

    // Update purchase
    public function update(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'supplier_id' => [
                'required',
                Rule::exists('suppliers', 'id'),
            ],
            'item_name' => 'required|string|max:255',
            'quantity' => 'required|numeric|gt:0',
            'unit' => 'required|string|max:50',
            'total_cost' => 'required|numeric|min:0',
            'purchase_date' => 'required|date',
        ]);

        $purchase->update($validated);

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase updated successfully!');
    }

    // Delete purchase
    public function destroy(Purchase $purchase)
    {
        $purchase->delete();

        return redirect()
            ->route('purchases.index')
            ->with('success', 'Purchase deleted successfully!');
    }
}
