
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\InventoryItemController;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest Routes
Route::middleware('guest')->group(function () {

    Route::get('/login', [
        LoginController::class,
        'showLoginForm'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'login'
    ]);

});

// Authenticated Routes
Route::middleware(['auth', \App\Http\Middleware\NoCacheMiddleware::class])->group(function () {

     // Module 1: Food & Beverage Inventory Management
    Route::get('/inventory', [InventoryItemController::class, 'index'])
        ->name('inventory.index');
    
    // Show Add Inventory Form
    Route::get('/inventory/create', [InventoryItemController::class, 'create'])
        ->name('inventory.create');

    // Save New Inventory Item
    Route::post('/inventory', [InventoryItemController::class, 'store'])
        ->name('inventory.store');


    // Module 3: Storage Location Management
    Route::resource('storage-locations', StorageLocationController::class)
        ->except(['show'])
        ->parameters(['storage-locations' => 'storageLocation']);


    // Dashboard
    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

    // Logout
    Route::post('/logout', [
        LoginController::class,
        'logout'
    ])->name('logout');

    // ADMIN ONLY ROUTES
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
                StaffController::class, 'edit'
            ])->name('staff.edit');

            // Update Staff
            Route::put('/staff/{staff}', [
                StaffController::class, 'update'
            ])->name('staff.update');

            // Delete Staff
            Route::delete('/staff/{staff}', [
                StaffController::class, 'destroy'
            ])->name('staff.destroy');


        });

});

