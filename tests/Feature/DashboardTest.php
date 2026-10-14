<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// The staff dashboard: today's operational work.
// The administrators' executive dashboard is covered by ExecutiveDashboardTest.
class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function item(string $name, string $category, float $quantity, float $minimum): InventoryItem
    {
        return InventoryItem::create([
            'item_name' => $name,
            'category' => $category,
            'unit' => 'kg',
            'quantity' => $quantity,
            'minimum_stock' => $minimum,
            'unit_price' => 5,
        ]);
    }

    private function purchase(Supplier $supplier, string $date, float $total, string $status = 'pending', array $extra = []): Purchase
    {
        return Purchase::create(array_merge([
            'supplier_id' => $supplier->id,
            'item_name' => 'Order on ' . $date,
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => $total,
            'total_cost' => $total,
            'purchase_date' => $date,
            'status' => $status,
        ], $extra));
    }

    public function test_staff_dashboard_focuses_on_daily_operations(): void
    {
        $this->item('Chicken', 'Food', 2, 5);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertViewIs('staff-dashboard')
            ->assertViewMissing('kpis')
            ->assertViewMissing('staffAccounts')
            ->assertSee("Today's operations", false)
            ->assertSee('Quick actions')
            ->assertSee('Orders to receive')
            ->assertSee('Expiring soon')
            ->assertSee('Your notifications')
            ->assertDontSee('Management Overview')
            ->assertDontSee('Management insights')
            ->assertDontSee('Manage Staff');
    }

    public function test_low_stock_alerts_list_out_of_stock_items_first(): void
    {
        $chicken = $this->item('Chicken', 'Food', 2, 5);
        $this->item('Rice', 'Food', 10, 10);
        $this->item('Patties', 'Food', 0, 5);
        $this->item('Cola', 'Beverage', 50, 10);

        $staff = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($staff)->get(route('dashboard'))->assertOk();

        $this->assertSame(['Patties', 'Chicken', 'Rice'], $response->viewData('lowStockItems')->pluck('item_name')->all());
        $this->assertSame(['total' => 4, 'low' => 2, 'out' => 1, 'alerts' => 3, 'in_stock' => 1], $response->viewData('stock'));
        $this->assertSame([1, 2, 1], array_column($response->viewData('stockStatusChart'), 'value'));

        $response->assertSee('0 kg in stock · minimum 5 kg')
            ->assertSee('Out of stock')
            ->assertSee('Low stock')
            ->assertSee('View data table')
            ->assertSee(route('purchases.create', ['inventory_item_id' => $chicken->id]), false)
            ->assertSee(route('inventory.index', ['stock' => 'restock']), false);
    }

    public function test_orders_to_receive_lists_open_orders_oldest_first(): void
    {
        $supplier = Supplier::create(['name' => 'Fresh Farm']);

        foreach (range(1, 7) as $daysAgo) {
            $this->purchase($supplier, now()->subDays($daysAgo)->toDateString(), 10 * $daysAgo, $daysAgo % 2 ? 'pending' : 'ordered');
        }

        $this->purchase($supplier, now()->subDays(20)->toDateString(), 5, 'received');
        $this->purchase($supplier, now()->subDays(30)->toDateString(), 5, 'cancelled');

        $staff = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($staff)->get(route('dashboard'))->assertOk();
        $orders = $response->viewData('ordersToReceive');

        $this->assertSame(7, $response->viewData('openOrderCount'));
        $this->assertCount(6, $orders);
        $this->assertEquals(70, (float) $orders->first()->total_cost);   // oldest first
        $this->assertTrue($orders->every(fn ($order) => $order->canBeReceived()));

        $response->assertSee(route('purchases.show', $orders->first()) . '#receive', false);
    }

    public function test_expiry_watch_lists_received_deliveries_close_to_expiry(): void
    {
        $supplier = Supplier::create(['name' => 'Dairy Co']);

        $this->purchase($supplier, now()->subDays(5)->toDateString(), 10, 'received', ['item_name' => 'Milk', 'expiry_date' => now()->addDays(2)->toDateString()]);
        $this->purchase($supplier, now()->subDays(9)->toDateString(), 10, 'received', ['item_name' => 'Cream', 'expiry_date' => now()->subDays(3)->toDateString()]);
        $this->purchase($supplier, now()->subDays(5)->toDateString(), 10, 'received', ['item_name' => 'Cheese', 'expiry_date' => now()->addDays(10)->toDateString()]);
        $this->purchase($supplier, now()->subDays(30)->toDateString(), 10, 'received', ['item_name' => 'Butter', 'expiry_date' => now()->subDays(10)->toDateString()]);
        $this->purchase($supplier, now()->toDateString(), 10, 'pending', ['item_name' => 'Yogurt', 'expiry_date' => now()->addDay()->toDateString()]);

        $staff = User::factory()->create(['role' => 'staff']);
        $response = $this->actingAs($staff)->get(route('dashboard'))->assertOk();

        $this->assertSame(['Cream', 'Milk'], $response->viewData('expiryWatch')->pluck('item_name')->all());
        $this->assertSame(2, $response->viewData('expiryWatchCount'));

        $response->assertSee('Expires in 2 days')
            ->assertSee('Expired 3 days ago')
            ->assertSee('reason=expired', false);
    }

    public function test_empty_staff_dashboard_shows_empty_states(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('All stock levels are healthy')
            ->assertSee('No orders waiting')
            ->assertSee('No notifications yet')
            ->assertSee('No activity recorded yet');
    }

    public function test_recent_activity_lists_real_changes_from_the_modules(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Aisyah']);

        $this->actingAs($staff)->post(route('suppliers.store'), ['name' => 'Fresh Farm'])
            ->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $staff->id,
            'module' => 'supplier',
            'action' => 'created',
            'description' => 'Added supplier “Fresh Farm”',
        ]);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertSee('Added supplier “Fresh Farm”')
            ->assertSee('Aisyah');

        $this->assertSame(1, ActivityLog::count());
    }
}
