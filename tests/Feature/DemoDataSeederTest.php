<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use Database\Seeders\ChicagoChickenDemoSeeder;
use Database\Seeders\InventoryDemoSeeder;
use Database\Seeders\PurchaseDemoSeeder;
use Database\Seeders\StorageLocationDemoSeeder;
use Database\Seeders\SupplierDemoSeeder;
use Database\Seeders\WasteExpiryDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class DemoDataSeederTest extends TestCase
{
    use RefreshDatabase;

    private function counts(): array
    {
        return [
            InventoryItem::count(),
            Supplier::count(),
            Purchase::count(),
            StorageLocation::count(),
            WasteExpiryRecord::count(),
        ];
    }

    public function test_demo_data_is_added_once_and_existing_records_are_untouched(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $passwordHash = $admin->password;
        $flour = InventoryItem::create([
            'item_name' => 'Flour', 'category' => 'Food', 'unit' => 'kg',
            'quantity' => 7, 'minimum_stock' => 1, 'unit_price' => 2,
        ]);
        $supplier = Supplier::create(['name' => 'My Own Supplier']);
        $activityBefore = ActivityLog::count();

        $this->seed(ChicagoChickenDemoSeeder::class);
        $afterFirstRun = $this->counts();

        $this->seed(ChicagoChickenDemoSeeder::class);

        $this->assertSame($afterFirstRun, $this->counts(), 'A second run must not add duplicates');
        $this->assertSame([
            count(InventoryDemoSeeder::ITEMS),            // 35 demo items + the existing Flour
            count(SupplierDemoSeeder::SUPPLIERS) + 1,
            count(PurchaseDemoSeeder::ORDERS),
            count(StorageLocationDemoSeeder::LOCATIONS),
            count(WasteExpiryDemoSeeder::RECORDS),
        ], $afterFirstRun);

        // Existing records keep their values
        $this->assertSame('7.00', $flour->fresh()->quantity);
        $this->assertSame('2.00', $flour->fresh()->unit_price);
        $this->assertSame('My Own Supplier', $supplier->fresh()->name);
        $this->assertSame($passwordHash, $admin->fresh()->password);
        $this->assertSame(1, User::count());

        // Loading demo data is not staff activity
        $this->assertSame($activityBefore, ActivityLog::count());
    }

    public function test_demo_purchase_orders_are_consistent(): void
    {
        $this->seed(ChicagoChickenDemoSeeder::class);

        $demoSupplierIds = Supplier::whereIn('email', array_column(SupplierDemoSeeder::SUPPLIERS, 2))->pluck('id');
        $purchases = Purchase::with('inventoryItem')->get();

        foreach ($purchases as $purchase) {
            $this->assertEquals(round($purchase->quantity * $purchase->unit_price, 2), (float) $purchase->total_cost, $purchase->po_number);
            $this->assertTrue($demoSupplierIds->contains($purchase->supplier_id), $purchase->po_number);
            $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{5}$/', $purchase->po_number);
            $this->assertFalse($purchase->purchase_date->isFuture(), $purchase->po_number);

            if ($purchase->inventoryItem) {
                $this->assertNotNull(Purchase::convertToInventoryUnit(1, $purchase->unit, $purchase->inventoryItem->unit));
            }

            // Past deliveries are marked as stocked; open orders can still be received once
            $this->assertSame(
                $purchase->status === 'received' && $purchase->inventory_item_id !== null,
                $purchase->hasAddedStock(),
                $purchase->po_number
            );
        }

        $this->assertEqualsCanonicalizing(['pending', 'ordered', 'received', 'cancelled'], $purchases->pluck('status')->unique()->all());

        // Suppliers hold contact details only - no quantities or costs
        $this->assertSame(0, Supplier::whereNotNull('quantity')->orWhereNotNull('total_cost')->count());

        // Seeding never changes stock levels
        foreach (InventoryDemoSeeder::ITEMS as [$name, , , $quantity]) {
            $this->assertEquals($quantity, (float) InventoryItem::where('item_name', $name)->value('quantity'), $name);
        }
    }

    public function test_dashboard_monitoring_uses_the_demo_data(): void
    {
        $this->seed(ChicagoChickenDemoSeeder::class);
        $admin = User::factory()->create(['role' => 'admin']);

        // Loading demo data never sends notifications
        $this->assertSame(0, DatabaseNotification::count());

        $response = $this->actingAs($admin)->get(route('dashboard'))->assertOk();

        // 36 items in all four categories: 6 low + 3 out of stock
        $kpis = $response->viewData('kpis');
        $this->assertSame(36, $kpis['inventoryValue']['items']);
        $this->assertSame(['Food', 'Beverage', 'Packaging', 'Other'], array_keys($kpis['inventoryValue']['byCategory']));
        $this->assertTrue(collect($kpis['inventoryValue']['byCategory'])->every(fn ($row) => $row['value'] > 0));
        $this->assertSame(['out' => 3, 'low' => 6], array_intersect_key($kpis['alerts'], ['out' => 0, 'low' => 0]));
        $this->assertSame(12, $kpis['suppliers']['total']);
        $this->assertSame(6, $kpis['awaiting']['count']);

        // Received purchases are spread across the chart months
        $this->assertGreaterThanOrEqual(5, collect($response->viewData('charts')['expenditure'])->where('value', '>', 0)->count());

        $response->assertSee('Critical: Burger Patties is out of stock.');

        // The first dashboard visit of the day alerts about the 9 items, once
        $this->assertSame(9, $admin->notifications()->where('data->category', 'stock')->count());
        $this->actingAs($admin)->get(route('dashboard'))->assertOk();
        $this->assertSame(9, $admin->notifications()->count());
    }

    public function test_receiving_a_demo_order_adds_its_stock_once(): void
    {
        $this->seed(ChicagoChickenDemoSeeder::class);
        $staff = User::factory()->create(['role' => 'staff']);
        $order = Purchase::where('item_name', 'Chicken burger patties')->where('status', 'pending')->firstOrFail();

        $this->actingAs($staff)->patch(route('purchases.receive', $order))->assertSessionHas('success');
        $this->actingAs($staff)->patch(route('purchases.receive', $order))->assertSessionHas('error');

        $this->assertSame('400.00', InventoryItem::where('item_name', 'Burger Patties')->value('quantity'));
    }

    public function test_demo_data_is_only_added_in_local_or_testing(): void
    {
        $this->app['env'] = 'production';

        (new ChicagoChickenDemoSeeder)->setContainer($this->app)->run();

        $this->assertSame([0, 0, 0, 0, 0], $this->counts());
    }
}
