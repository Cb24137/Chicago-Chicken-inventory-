<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    // Display all purchase orders with search, filters and pagination
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = in_array($request->input('status'), array_keys(Purchase::STATUSES), true)
            ? $request->input('status')
            : null;
        $supplierId = $request->integer('supplier_id') ?: null;
        $from = $this->dateFilter($request->input('from'));
        $to = $this->dateFilter($request->input('to'));

        $purchases = Purchase::with(['supplier', 'inventoryItem'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('po_number', 'like', "%{$search}%")
                        ->orWhere('item_name', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($supplierId, fn ($query) => $query->where('supplier_id', $supplierId))
            ->when($from, fn ($query) => $query->whereDate('purchase_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('purchase_date', '<=', $to))
            ->orderByDesc('purchase_date')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $summary = [
            'total' => Purchase::count(),
            'open' => Purchase::whereIn('status', ['pending', 'ordered'])->count(),
            'received' => Purchase::where('status', 'received')->count(),
            'spending' => (float) Purchase::notCancelled()->sum('total_cost'),
        ];

        return view('purchases.index', [
            'purchases' => $purchases,
            'summary' => $summary,
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'statuses' => Purchase::STATUSES,
            'filters' => compact('search', 'status', 'supplierId', 'from', 'to'),
        ]);
    }

    // Show create purchase order form
    public function create(Request $request)
    {
        $purchase = new Purchase([
            'supplier_id' => $request->integer('supplier_id') ?: null,
            'purchase_date' => now()->toDateString(),
            'status' => 'pending',
        ]);

        // "Reorder" links from low-stock alerts pre-select the inventory item
        $item = InventoryItem::find($request->integer('inventory_item_id'));

        if ($item) {
            $unit = Purchase::compatibleUnits($item->unit)[0] ?? null;

            $purchase->fill([
                'inventory_item_id' => $item->id,
                'item_name' => $item->item_name,
                'unit' => $unit,
                'unit_price' => $unit ? $item->unit_price : null,
            ]);
        }

        return view('purchases.create', $this->formData() + [
            'purchase' => $purchase,
        ]);
    }

    // Save new purchase order
    public function store(Request $request)
    {
        $data = $this->validatedOrder($request);
        $stockAdded = null;

        $purchase = DB::transaction(function () use ($data, &$stockAdded) {
            $purchase = Purchase::create($data);
            $stockAdded = $purchase->addReceivedStock();

            return $purchase;
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', $this->savedMessage($purchase, 'created', $stockAdded));
    }

    // Show one purchase order
    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'inventoryItem']);

        return view('purchases.show', compact('purchase'));
    }

    // Show edit purchase order form
    public function edit(Purchase $purchase)
    {
        return view('purchases.edit', $this->formData() + [
            'purchase' => $purchase,
        ]);
    }

    // Update purchase order
    public function update(Request $request, Purchase $purchase)
    {
        // Once stock has been added to inventory, the fields that
        // affect stock are locked to keep inventory levels correct.
        $stockLocked = $purchase->hasAddedStock();

        if ($stockLocked) {
            $request->merge([
                'inventory_item_id' => $purchase->inventory_item_id,
                'quantity' => $purchase->quantity,
                'unit' => $purchase->unit,
                'status' => $purchase->status,
            ]);
        }

        $data = $this->validatedOrder($request, $stockLocked);
        $stockAdded = null;

        DB::transaction(function () use ($purchase, $data, &$stockAdded) {
            $purchase->update($data);
            $stockAdded = $purchase->addReceivedStock();
        });

        return redirect()
            ->route('purchases.index')
            ->with('success', $this->savedMessage($purchase, 'updated', $stockAdded));
    }

    // Confirm that the goods arrived - adds stock to inventory once.
    // Staff can also enter the delivery's expiry date for expiry alerts.
    public function receive(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'expiry_date' => ['nullable', 'date_format:Y-m-d'],
        ], [], ['expiry_date' => 'expiry date']);

        if (! $purchase->canBeReceived()) {
            return back()->with('error', $purchase->status === 'received'
                ? "{$purchase->po_number} has already been received."
                : "{$purchase->po_number} is cancelled. Change its status before receiving it.");
        }

        $item = $purchase->inventoryItem;

        if ($item && Purchase::convertToInventoryUnit(1, $purchase->unit, $item->unit) === null) {
            return back()->with('error', "{$purchase->po_number} is ordered in {$purchase->unit}, but “{$item->item_name}” is stocked in {$item->unit}. Edit the order's unit before receiving it.");
        }

        $stockAdded = null;

        DB::transaction(function () use ($purchase, $data, &$stockAdded) {
            $purchase->update([
                'status' => 'received',
                'expiry_date' => $data['expiry_date'] ?? $purchase->expiry_date,
            ]);
            $stockAdded = $purchase->addReceivedStock();
        });

        return back()->with('success', $this->savedMessage($purchase, 'marked as received', $stockAdded));
    }

    // Delete purchase order
    public function destroy(Purchase $purchase)
    {
        // Orders that already added stock are part of the inventory history
        if ($purchase->hasAddedStock()) {
            return back()->with('error', "{$purchase->po_number} has already added stock to inventory, so it is kept as a stock record and cannot be deleted.");
        }

        $number = $purchase->po_number;
        $purchase->delete();

        return redirect()
            ->route('purchases.index')
            ->with('success', "Purchase order {$number} deleted successfully!");
    }

    // Validate the form and calculate the total on the server
    private function validatedOrder(Request $request, bool $stockLocked = false): array
    {
        $data = $request->validate([
            'supplier_id' => ['required', Rule::exists('suppliers', 'id')],
            'inventory_item_id' => ['nullable', Rule::exists('inventory_items', 'id')],
            'item_name' => ['nullable', 'required_without:inventory_item_id', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0', 'max:1000000'],
            'unit' => ['required', Rule::in(array_keys(Purchase::UNITS))],
            'unit_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
            'purchase_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(Purchase::STATUSES))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'expiry_date' => ['nullable', 'date'],
        ], [
            'supplier_id.required' => 'Please select a supplier.',
            'supplier_id.exists' => 'The selected supplier no longer exists.',
            'inventory_item_id.exists' => 'The selected inventory item no longer exists.',
            'item_name.required_without' => 'Enter a product name or choose an inventory item.',
            'quantity.gt' => 'The quantity must be greater than zero.',
        ], [
            'item_name' => 'product name',
            'unit_price' => 'unit price',
            'purchase_date' => 'purchase date',
            'expiry_date' => 'expiry date',
        ]);

        $item = isset($data['inventory_item_id'])
            ? InventoryItem::find($data['inventory_item_id'])
            : null;

        // A linked inventory item must be able to receive this unit
        if ($item && ! $stockLocked && Purchase::convertToInventoryUnit(1, $data['unit'], $item->unit) === null) {
            $allowed = collect(Purchase::compatibleUnits($item->unit))
                ->map(fn ($unit) => Purchase::UNITS[$unit])
                ->implode(' or ');

            throw ValidationException::withMessages([
                'unit' => $allowed !== ''
                    ? "“{$item->item_name}” is stocked in {$item->unit}, so this order must use {$allowed}."
                    : "“{$item->item_name}” is stocked in {$item->unit}, which cannot be converted from an order unit. Remove the inventory item link to continue.",
            ]);
        }

        // Use the inventory item's name when no product name was typed
        if (blank($data['item_name'] ?? null) && $item) {
            $data['item_name'] = $item->item_name;
        }

        // Never trust a total sent by the browser - always recalculate it
        $data['total_cost'] = Purchase::calculateTotal($data['quantity'], $data['unit_price']);

        if ($data['total_cost'] > 99999999.99) {
            throw ValidationException::withMessages([
                'unit_price' => 'The total cost is too large. Please check the quantity and unit price.',
            ]);
        }

        return $data;
    }

    private function formData(): array
    {
        return [
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'inventoryItems' => InventoryItem::orderBy('item_name')->get(),
            'units' => Purchase::UNITS,
            'statuses' => Purchase::STATUSES,
            'unitFactors' => Purchase::INVENTORY_UNIT_FACTORS,
        ];
    }

    private function savedMessage(Purchase $purchase, string $action, ?float $stockAdded): string
    {
        $message = "Purchase order {$purchase->po_number} {$action}.";

        if ($stockAdded !== null) {
            $item = $purchase->inventoryItem;
            $message .= ' ' . (float) $stockAdded . " {$item->unit} added to “{$item->item_name}” stock.";
        } elseif ($purchase->status === 'received' && ! $purchase->inventory_item_id) {
            $message .= ' It is not linked to an inventory item, so stock levels were not changed.';
        }

        return $message;
    }

    // Accept only real YYYY-MM-DD dates from the filter form
    private function dateFilter(?string $value): ?string
    {
        if (! $value || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('!Y-m-d', $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }

        // Reject impossible dates such as 2026-02-31
        return $date === $value ? $date : null;
    }
}
