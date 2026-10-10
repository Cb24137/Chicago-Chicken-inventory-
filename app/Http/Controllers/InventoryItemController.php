<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InventoryItem;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
public function index()
{
    $inventoryItems = InventoryItem::orderBy('item_name', 'asc')->get();

    return view('inventory.index', compact('inventoryItems'));
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
        'category' => 'required|in:Food,Beverage',
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
