<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
public function index(Request $request)
{
    // Optional search and filters
    $search = trim((string) $request->input('search'));
    $category = in_array($request->input('category'), InventoryItem::CATEGORIES, true) ? $request->input('category') : null;
    $stock = array_key_exists((string) $request->input('stock'), InventoryItem::STOCK_STATUSES) ? $request->input('stock') : null;

    $inventoryItems = InventoryItem::query()
        ->when($search !== '', fn ($query) => $query->where('item_name', 'like', "%{$search}%"))
        ->when($category, fn ($query) => $query->where('category', $category))
        ->stockStatus($stock)
        ->orderBy('item_name', 'asc')
        ->get();

    // Totals for all items, whatever the filters
    $summary = [
        'items' => InventoryItem::count(),
        'low' => InventoryItem::lowStock()->count(),
        'value' => (float) InventoryItem::sum(\Illuminate\Support\Facades\DB::raw('quantity * unit_price')),
    ];

    return view('inventory.index', compact('inventoryItems', 'summary', 'search', 'category', 'stock'));
}


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    
public function store(Request $request)
{
    // Validate inventory information
    $validated = $request->validate([
        'item_name' => 'required|string|max:255',
        'category' => 'required|in:' . implode(',', InventoryItem::CATEGORIES),
        'unit' => 'required|in:kg,litre,pcs',
        'quantity' => 'required|numeric|min:0',
        'minimum_stock' => 'required|numeric|min:0',
        'unit_price' => 'required|numeric|min:0',
    ]);

    // Save inventory item to database
    InventoryItem::create($validated);

    // Return to inventory list
    return redirect()
        ->route('inventory.index')
        ->with('success', 'Inventory item added successfully!');
}


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    
public function edit(string $id)
{
    $inventoryItem = InventoryItem::findOrFail($id);

    return view('inventory.edit', compact('inventoryItem'));
}


    /**
     * Update the specified resource in storage.
     */
    
public function update(Request $request, string $id)
{
    // Find inventory item
    $inventoryItem = InventoryItem::findOrFail($id);

    // Validate updated information
    $validated = $request->validate([
        'item_name' => 'required|string|max:255',
        'category' => 'required|in:' . implode(',', InventoryItem::CATEGORIES),
        'unit' => 'required|in:kg,litre,pcs',
        'quantity' => 'required|numeric|min:0',
        'minimum_stock' => 'required|numeric|min:0',
        'unit_price' => 'required|numeric|min:0',
    ]);

    // Update inventory record
    $inventoryItem->update($validated);

    // Redirect to inventory list
    return redirect()
        ->route('inventory.index')
        ->with('success', 'Inventory item updated successfully!');
}


    /**
     * Remove the specified resource from storage.
     */
    
public function destroy(string $id)
{
    // Find inventory item
    $inventoryItem = InventoryItem::findOrFail($id);

    // Delete inventory item
    $inventoryItem->delete();

    // Redirect to inventory list
    return redirect()
        ->route('inventory.index')
        ->with('success', 'Inventory item deleted successfully!');
}

}
