<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\StorageLocationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
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
    // MODULE 1: FOOD & BEVERAGE INVENTORY
    // ========================================

    Route::get('/inventory', [InventoryItemController::class, 'index'])
        ->name('inventory.index');
    
    // Show Add Inventory Form
    Route::get('/inventory/create', [InventoryItemController::class, 'create'])
        ->name('inventory.create');

    // Save New Inventory Item
    Route::post('/inventory', [InventoryItemController::class, 'store'])
        ->name('inventory.store');
    
    // Show Edit Inventory Form
    Route::get('/inventory/{id}/edit', [InventoryItemController::class, 'edit'])
        ->name('inventory.edit');

    // Update Inventory Item
    Route::put('/inventory/{id}', [InventoryItemController::class, 'update'])
        ->name('inventory.update');
    
    // Delete Inventory Item
    Route::delete('/inventory/{id}', [InventoryItemController::class, 'destroy'])
        ->name('inventory.destroy');


    // ========================================
    // MODULE 2: SUPPLIER & PURCHASE MANAGEMENT
    // ========================================

    // Supplier Management CRUD
    Route::resource(
        'suppliers',
        SupplierController::class
    )->except(['show']);

    // Confirm a purchase order was received (adds stock once)
    Route::patch('/purchases/{purchase}/receive', [
        PurchaseController::class,
        'receive'
    ])->name('purchases.receive');

    // Purchase Order Management CRUD
    Route::resource(
        'purchases',
        PurchaseController::class
    );


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

    // ========================================
    // MODULE 4: WASTE & EXPIRY MANAGEMENT
    // ========================================

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
    // REPORTS & ANALYTICS
    // (staff: inventory, storage and waste reports; admins: all)
    // ========================================

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/{report}', [ReportController::class, 'show'])
        ->name('reports.show');

    Route::get('/reports/{report}/export/{format}', [ReportController::class, 'export'])
        ->name('reports.export');

    // ========================================
    // NOTIFICATION CENTER
    // ========================================

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');


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
