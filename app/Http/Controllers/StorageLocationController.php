<?php

namespace App\Http\Controllers;

use App\Models\StorageLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;

class StorageLocationController extends Controller
{
    // READ
    public function index()
    {
        $locations = StorageLocation::latest()
            ->paginate(10);

        return view('storage-index', compact('locations'));
    }

    // CREATE FORM
    public function create()
    {
        return view('storage-create');
    }

    // STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:storage_locations,name',
            ],
            'type' => [
                'required',
                Rule::in([
                    'Freezer',
                    'Refrigerator',
                    'Dry Storage',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        StorageLocation::create($validated);

        return redirect()
            ->route('storage-locations.index')
            ->with('success', 'Storage location created successfully.');
    }

    // EDIT FORM
    public function edit(StorageLocation $storageLocation)
    {
        return view('storage-edit', compact('storageLocation'));
    }

    // UPDATE
    public function update(
        Request $request,
        StorageLocation $storageLocation
    ) {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('storage_locations', 'name')
                    ->ignore($storageLocation->id),
            ],
            'type' => [
                'required',
                Rule::in([
                    'Freezer',
                    'Refrigerator',
                    'Dry Storage',
                ]),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'required',
                'boolean',
            ],
        ]);

        $storageLocation->update($validated);

        return redirect()
            ->route('storage-locations.index')
            ->with('success', 'Storage location updated successfully.');
    }

    // DELETE
    public function destroy(StorageLocation $storageLocation)
    {
        // Prevent deleting locations with assigned inventory
        // when the future assignments table is available.
        if (
            Schema::hasTable('storage_assignments') &&
            Schema::hasColumn(
                'storage_assignments',
                'storage_location_id'
            )
        ) {
            $hasAssignments = DB::table('storage_assignments')
                ->where('storage_location_id', $storageLocation->id)
                ->exists();

            if ($hasAssignments) {
                return back()->withErrors([
                    'location' =>
                        'Cannot delete a location with assigned inventory.',
                ]);
            }
        }

        try {
            $storageLocation->delete();
        } catch (QueryException $e) {
            return back()->withErrors([
                'location' =>
                    'This location is referenced by other records.',
            ]);
        }

        return redirect()
            ->route('storage-locations.index')
            ->with('success', 'Storage location deleted successfully.');
    }
}