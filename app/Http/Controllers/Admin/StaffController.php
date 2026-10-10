<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class StaffController extends Controller
{
    // Display all staff members
    public function index()
    {
        $staff = User::where('role', 'staff')
            ->latest()
            ->paginate(10);

        return view('manage-staff', compact('staff'));
    }

    // Show create staff form
    public function create()
    {
        return view('create-staff');
    }

    // Store new staff account
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(5)
            ],
        ]);

        // New accounts receive staff role by default
        User::create($validated);

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff account created successfully!');
    }

    // Show Edit Staff Form
    public function edit(User $staff)
    {
        abort_unless($staff->isStaff(), 404);

        return view('edit-staff', compact('staff'));
    }

    // Update Staff Account
    public function update(Request $request, User $staff)
    {
        abort_unless($staff->isStaff(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                'max:255',
                \Illuminate\Validation\Rule::unique('users', 'email')
                    ->ignore($staff->id),
            ],

            'password' => [
                'nullable',
                'confirmed',
                Password::min(5),
            ],
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];

        // Change password only if entered
        if (!empty($validated['password'])) {
            $staff->password = $validated['password'];
        }

        $staff->save();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff updated successfully!');
    }

    // Delete Staff Account
    public function destroy(User $staff)
    {
        abort_unless($staff->isStaff(), 404);

        $staff->delete();

        return redirect()
            ->route('admin.staff.index')
            ->with('success', 'Staff deleted successfully!');
    }
}
