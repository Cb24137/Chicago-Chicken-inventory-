<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\WasteExpiryRecord;
use App\Services\AlertService;
use App\Services\ExecutiveDashboard;
use App\Support\ReportingPeriod;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, AlertService $alerts, ExecutiveDashboard $executive)
    {
        $user = $request->user();

        if (! $user->isAdmin() && ! $user->isStaff()) {
            abort(403, 'Unauthorized role.');
        }

        // XAMPP runs no scheduler, so due alerts are checked on the first visit each day
        $alerts->runDailyChecks();

        // Administrators: executive overview for a reporting period
        if ($user->isAdmin()) {
            return view('admin-dashboard', ['user' => $user] + $executive->build($user, ReportingPeriod::fromRequest($request)));
        }

        // Staff: today's operational work
        return view('staff-dashboard', ['user' => $user] + $this->operations() + [
            'notifications' => $user->notifications()->take(5)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    // Daily operations for the staff dashboard
    private function operations(): array
    {
        $stock = InventoryItem::query()
            ->selectRaw('COUNT(*) AS total')
            ->selectRaw('SUM(CASE WHEN quantity <= 0 THEN 1 ELSE 0 END) AS out_of_stock')
            ->selectRaw('SUM(CASE WHEN quantity > 0 AND quantity <= minimum_stock THEN 1 ELSE 0 END) AS low')
            ->first();

        $low = (int) $stock->low;
        $out = (int) $stock->out_of_stock;
        $inStock = (int) $stock->total - $low - $out;

        return [
            'stock' => [
                'total' => (int) $stock->total,
                'low' => $low,
                'out' => $out,
                'alerts' => $low + $out,
                'in_stock' => $inStock,
            ],
            'stockStatusChart' => [
                ['label' => 'In stock', 'value' => $inStock, 'tone' => 'good'],
                ['label' => 'Low stock', 'value' => $low, 'tone' => 'warning'],
                ['label' => 'Out of stock', 'value' => $out, 'tone' => 'critical'],
            ],
            'openOrderCount' => Purchase::open()->count(),
            'openOrderValue' => (float) Purchase::open()->sum('total_cost'),
            'lowStockItems' => InventoryItem::lowStock()->orderBy('quantity')->orderBy('item_name')->take(6)->get(),
            'ordersToReceive' => Purchase::with('supplier')->open()->orderBy('purchase_date')->orderBy('id')->take(6)->get(),
            'expiryWatch' => Purchase::expiryWatch()->orderBy('expiry_date')->take(6)->get(),
            'expiryWatchCount' => Purchase::expiryWatch()->count(),
            'expiryDays' => (int) config('inventory.expiry_alert_days'),
            'recentWaste' => WasteExpiryRecord::orderByDesc('record_date')->orderByDesc('id')->take(5)->get(),
            'wasteThisWeek' => WasteExpiryRecord::whereDate('record_date', '>=', today()->subDays(6)->toDateString())->count(),
            'recentActivities' => ActivityLog::with('user')->orderByDesc('id')->take(8)->get(),
        ];
    }
}
