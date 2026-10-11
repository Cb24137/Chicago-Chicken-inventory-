<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\WasteExpiryRecordController;

// ========================================
// HOME PAGE
// ========================================

Route::get('/', function (Request $request) {
    return $request->user()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// ========================================
// GUEST ROUTES
// ========================================

Route::middleware('guest')->group(function () {

    // Show Login Page
    Route::get('/login', [
        LoginController::class,
        'showLoginForm'
    ])->name('login');

    // Process Login
    Route::post('/login', [
        LoginController::class,
        'login'
    ]);
});

// ========================================
// AUTHENTICATED ROUTES
// ========================================

Route::middleware([
    'auth',
    \App\Http\Middleware\NoCacheMiddleware::class
])->group(function () {

    // ========================================
    // MODULE 2: SUPPLIER & PURCHASE MANAGEMENT
    // ========================================

    // Supplier Management CRUD
    Route::resource(
        'suppliers',
        SupplierController::class
    )->except(['show']);

    // Purchase Management CRUD
    Route::resource(
        'purchases',
        PurchaseController::class
    )->except(['show']);


    // ========================================
    // MODULE 3: STORAGE LOCATION MANAGEMENT
    // ========================================

    Route::resource(
        'storage-locations',
        StorageLocationController::class
    )
        ->except(['show'])
        ->parameters([
            'storage-locations' => 'storageLocation'
        ]);

// Module 4: Waste & Expiry Management
Route::resource('waste-expiry', WasteExpiryRecordController::class)
    ->parameters(['waste-expiry' => 'wasteExpiryRecord']);

    // ========================================
    // DASHBOARD
    // ========================================

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');


    // ========================================
    // LOGOUT
    // ========================================

    Route::post('/logout', [
        LoginController::class,
        'logout'
    ])->name('logout');


    // ========================================
    // ADMIN ONLY ROUTES
    // ========================================

    Route::middleware('admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            // Test Admin Access
            Route::get('/check', function () {
                return 'Admin access granted!';
            })->name('check');

            // View Staff List
            Route::get('/staff', [
                StaffController::class,
                'index'
            ])->name('staff.index');

            // Add Staff Form
            Route::get('/staff/create', [
                StaffController::class,
                'create'
            ])->name('staff.create');

            // Save New Staff
            Route::post('/staff', [
                StaffController::class,
                'store'
            ])->name('staff.store');

            // Edit Staff
            Route::get('/staff/{staff}/edit', [
                StaffController::class,
                'edit'
            ])->name('staff.edit');

            // Update Staff
            Route::put('/staff/{staff}', [
                StaffController::class,
                'update'
            ])->name('staff.update');

            // Delete Staff
            Route::delete('/staff/{staff}', [
                StaffController::class,
                'destroy'
            ])->name('staff.destroy');
        });


});
