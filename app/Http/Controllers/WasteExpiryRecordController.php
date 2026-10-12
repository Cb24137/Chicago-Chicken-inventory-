<?php

namespace App\Http\Controllers;

use App\Models\WasteExpiryRecord;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WasteExpiryRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = WasteExpiryRecord::query();

        if ($request->filled('search')) {
            $query->where(
                'item_name',
                'like',
                '%' . $request->input('search') . '%'
            );
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->input('reason'));
        }

        $records = $query->latest('record_date')
            ->paginate(10)
            ->withQueryString();

        $totalRecords = WasteExpiryRecord::count();

        $expiredCount = WasteExpiryRecord::whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', now()->toDateString())
            ->count();

        $spoiledCount = WasteExpiryRecord::where('reason', 'spoiled')->count();
        $damagedCount = WasteExpiryRecord::where('reason', 'damaged')->count();

        return view('waste-expiry.index', compact(
            'records',
            'totalRecords',
            'expiredCount',
            'spoiledCount',
            'damagedCount'
        ));
    }

    public function create()
    {
        return view('waste-expiry.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:30'],
            'reason' => [
                'required',
                Rule::in(['expired', 'spoiled', 'damaged', 'other']),
            ],
            'expiry_date' => ['nullable', 'date'],
            'record_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        WasteExpiryRecord::create($validated);

        return redirect()
            ->route('waste-expiry.index')
            ->with('success', 'Waste record created successfully.');
    }

    public function show(WasteExpiryRecord $wasteExpiryRecord)
    {
        return view('waste-expiry.show', compact('wasteExpiryRecord'));
    }

    public function edit(WasteExpiryRecord $wasteExpiryRecord)
    {
        return view('waste-expiry.edit', compact('wasteExpiryRecord'));
    }

    public function update(
        Request $request,
        WasteExpiryRecord $wasteExpiryRecord
    ) {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:30'],
            'reason' => [
                'required',
                Rule::in(['expired', 'spoiled', 'damaged', 'other']),
            ],
            'expiry_date' => ['nullable', 'date'],
            'record_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $wasteExpiryRecord->update($validated);

        return redirect()
            ->route('waste-expiry.index')
            ->with('success', 'Waste record updated successfully.');
    }

    public function destroy(WasteExpiryRecord $wasteExpiryRecord)
    {
        $wasteExpiryRecord->delete();

        return redirect()
            ->route('waste-expiry.index')
            ->with('success', 'Waste record deleted successfully.');
    }
}
