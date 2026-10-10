<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $totalStaff = User::where('role', 'staff')->count();

            return view('admin-dashboard', [
                'user' => $user,
                'totalStaff' => $totalStaff,
            ]);
        }

        if ($user->isStaff()) {
            return view('staff-dashboard', [
                'user' => $user,
            ]);
        }

        abort(403, 'Unauthorized role.');
    }
}