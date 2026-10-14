<?php

namespace App\Providers;

use App\Http\Controllers\ReportController;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use App\Observers\ActivityObserver;
use App\Observers\AlertObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Shared pagination design used by every module table
        Paginator::defaultView('partials.pagination');
        Paginator::defaultSimpleView('partials.pagination');

        // Log activity from all four inventory modules
        foreach ([
            InventoryItem::class,
            Supplier::class,
            Purchase::class,
            StorageLocation::class,
            WasteExpiryRecord::class,
        ] as $model) {
            $model::observe(ActivityObserver::class);
        }

        // In-app notifications from real business actions
        foreach ([InventoryItem::class, Purchase::class, WasteExpiryRecord::class, User::class] as $model) {
            $model::observe(AlertObserver::class);
        }

        // Admins can open every report; staff only the operational ones
        Gate::define('view-report', fn (User $user, string $report) => $user->isAdmin()
            || in_array($report, ReportController::STAFF_REPORTS, true));

        // Notification bell and sidebar count on every signed-in page
        View::composer('layouts.app', function ($view) {
            $user = auth()->user();

            $view->with([
                'unreadNotificationCount' => $user ? $user->unreadNotifications()->count() : 0,
                'latestNotifications' => $user ? $user->notifications()->latest()->take(6)->get() : collect(),
            ]);
        });
    }
}
