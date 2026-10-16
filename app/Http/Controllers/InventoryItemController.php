<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryItemController extends Controller
{
    /**
     * Display all inventory items.
     */
    public function index()
    {
        $inventoryItems = InventoryItem::orderBy('item_name', 'asc')->get();

        return view('inventory.index', compact('inventoryItems'));
    }

    /**
     * Show the Add Inventory form.
     */
    public function create()
    {
        return view('inventory.create');
    }

    /**
     * Store a new inventory item.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:Food,Beverage',
            'unit' => 'required|in:kg,g,litre,ml,pcs,pack,bottle,can,box,bag',
            'quantity' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        InventoryItem::create($validated);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item added successfully!');
    }

    /**
     * Display a specific inventory item.
     */
    public function show(string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        return view('inventory.edit', compact('inventoryItem'));
    }

    /**
     * Show the Edit Inventory form.
     */
    public function edit(string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        return view('inventory.edit', compact('inventoryItem'));
    }

    /**
     * Update an existing inventory item.
     */
    public function update(Request $request, string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'item_name' => 'required|string|max:255',
            'category' => 'required|in:Food,Beverage',
            'unit' => 'required|in:kg,g,litre,ml,pcs,pack,bottle,can,box,bag',
            'quantity' => 'required|numeric|min:0',
            'minimum_stock' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $inventoryItem->update($validated);

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item updated successfully!');
    }

    /**
     * Delete an inventory item.
     */
    public function destroy(string $id)
    {
        $inventoryItem = InventoryItem::findOrFail($id);

        $inventoryItem->delete();

        return redirect()
            ->route('inventory.index')
            ->with('success', 'Inventory item deleted successfully!');
    }
}
