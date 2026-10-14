<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\StorageLocation;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Modules 1, 3 and 4 keep working on the new shared layout
class ExistingModulesTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    public function test_inventory_items_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->staff)->post(route('inventory.store'), [
            'item_name' => 'Chicken',
            'category' => 'Food',
            'unit' => 'kg',
            'quantity' => 3,
            'minimum_stock' => 5,
            'unit_price' => 12,
        ])->assertRedirect(route('inventory.index'));

        $item = InventoryItem::firstOrFail();

        $this->actingAs($this->staff)->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Chicken')
            ->assertSee('Low Stock');

        $this->actingAs($this->staff)->get(route('inventory.edit', $item->id))->assertOk();

        $this->actingAs($this->staff)->put(route('inventory.update', $item->id), [
            'item_name' => 'Chicken Wings',
            'category' => 'Food',
            'unit' => 'kg',
            'quantity' => 20,
            'minimum_stock' => 5,
            'unit_price' => 12,
        ])->assertRedirect(route('inventory.index'));

        $this->assertSame('Chicken Wings', $item->fresh()->item_name);

        $this->actingAs($this->staff)->delete(route('inventory.destroy', $item->id))
            ->assertRedirect(route('inventory.index'));

        $this->assertModelMissing($item);
        $this->assertDatabaseHas('activity_logs', ['module' => 'inventory', 'action' => 'deleted']);
    }

    public function test_storage_locations_can_be_created_updated_and_deleted(): void
    {
        $this->actingAs($this->staff)->post(route('storage-locations.store'), [
            'name' => 'Freezer A',
            'type' => 'Freezer',
            'description' => 'Main freezer',
            'is_active' => '1',
        ])->assertRedirect(route('storage-locations.index'));

        $location = StorageLocation::firstOrFail();

        $this->actingAs($this->staff)->get(route('storage-locations.index'))->assertOk()->assertSee('Freezer A');
        $this->actingAs($this->staff)->get(route('storage-locations.edit', $location))->assertOk();

        $this->actingAs($this->staff)->put(route('storage-locations.update', $location), [
            'name' => 'Freezer B',
            'type' => 'Freezer',
            'is_active' => '0',
        ])->assertRedirect(route('storage-locations.index'));

        $this->assertFalse($location->fresh()->is_active);

        $this->actingAs($this->staff)->delete(route('storage-locations.destroy', $location))
            ->assertRedirect(route('storage-locations.index'));

        $this->assertModelMissing($location);
    }

    public function test_waste_records_can_be_created_viewed_updated_and_deleted(): void
    {
        $this->actingAs($this->staff)->post(route('waste-expiry.store'), [
            'item_name' => 'Milk',
            'quantity' => 2,
            'unit' => 'litre',
            'reason' => 'expired',
            'expiry_date' => '2026-10-01',
            'record_date' => '2026-10-05',
            'description' => 'Past expiry date',
        ])->assertRedirect(route('waste-expiry.index'));

        $record = WasteExpiryRecord::firstOrFail();

        $this->actingAs($this->staff)->get(route('waste-expiry.index'))->assertOk()->assertSee('Milk');
        $this->actingAs($this->staff)->get(route('waste-expiry.show', $record))->assertOk()->assertSee('Past expiry date');
        $this->actingAs($this->staff)->get(route('waste-expiry.edit', $record))->assertOk();

        $this->actingAs($this->staff)->put(route('waste-expiry.update', $record), [
            'item_name' => 'Fresh Milk',
            'quantity' => 3,
            'unit' => 'litre',
            'reason' => 'spoiled',
            'record_date' => '2026-10-05',
        ])->assertRedirect(route('waste-expiry.index'));

        $this->assertSame('spoiled', $record->fresh()->reason);

        $this->actingAs($this->staff)->delete(route('waste-expiry.destroy', $record))
            ->assertRedirect(route('waste-expiry.index'));

        $this->assertModelMissing($record);
    }

    public function test_module_pages_use_the_shared_layout_and_sidebar(): void
    {
        foreach (['inventory.create', 'storage-locations.create', 'waste-expiry.create', 'suppliers.create', 'purchases.create'] as $routeName) {
            $this->actingAs($this->staff)->get(route($routeName))
                ->assertOk()
                ->assertSee('id="ccc-sidebar"', false)
                ->assertSee('Purchase Orders')
                ->assertSee('Reports &amp; Analytics', false)
                ->assertSee('aria-current="page"', false);
        }
    }

    public function test_inventory_accepts_packaging_and_other_categories(): void
    {
        foreach (['Packaging' => 'Paper Cups', 'Other' => 'Dishwashing Liquid'] as $category => $name) {
            $this->actingAs($this->staff)->post(route('inventory.store'), [
                'item_name' => $name, 'category' => $category, 'unit' => 'pcs',
                'quantity' => 10, 'minimum_stock' => 2, 'unit_price' => 1,
            ])->assertRedirect(route('inventory.index'));
        }

        $this->assertEqualsCanonicalizing(['Other', 'Packaging'], InventoryItem::pluck('category')->all());

        $this->actingAs($this->staff)->post(route('inventory.store'), [
            'item_name' => 'Chair', 'category' => 'Furniture', 'unit' => 'pcs',
            'quantity' => 1, 'minimum_stock' => 1, 'unit_price' => 1,
        ])->assertSessionHasErrors('category');

        $this->actingAs($this->staff)->get(route('inventory.create'))
            ->assertSee('<option value="Packaging"', false)
            ->assertSee('<option value="Other"', false);
    }

    public function test_inventory_list_can_be_searched_and_filtered(): void
    {
        foreach ([
            ['Chicken Wings', 'Food', 2, 5],        // low
            ['Chicken Breast', 'Food', 20, 5],      // in stock
            ['Paper Cups', 'Packaging', 0, 100],    // out of stock
            ['Cola', 'Beverage', 50, 10],           // in stock
        ] as [$name, $category, $quantity, $minimum]) {
            InventoryItem::create([
                'item_name' => $name, 'category' => $category, 'unit' => 'pcs',
                'quantity' => $quantity, 'minimum_stock' => $minimum, 'unit_price' => 1,
            ]);
        }

        $names = fn (array $query) => $this->actingAs($this->staff)->get(route('inventory.index', $query))
            ->assertOk()
            ->viewData('inventoryItems')
            ->pluck('item_name')
            ->all();

        $this->assertSame(['Chicken Breast', 'Chicken Wings'], $names(['search' => 'chicken']));
        $this->assertSame(['Paper Cups'], $names(['category' => 'Packaging']));
        $this->assertSame(['Chicken Wings', 'Paper Cups'], $names(['stock' => 'restock']));
        $this->assertSame(['Chicken Wings'], $names(['stock' => 'low']));
        $this->assertSame(['Paper Cups'], $names(['stock' => 'out']));
        $this->assertSame(['Chicken Breast', 'Cola'], $names(['stock' => 'in_stock']));
        $this->assertSame(['Chicken Wings'], $names(['search' => 'chicken', 'stock' => 'low']));
        $this->assertCount(4, $names(['category' => 'Furniture', 'stock' => 'everything']), 'Unknown values are ignored');

        // The summary cards always count the whole inventory
        $response = $this->actingAs($this->staff)->get(route('inventory.index', ['category' => 'Packaging']));
        $this->assertSame(4, $response->viewData('summary')['items']);
        $this->assertSame(2, $response->viewData('summary')['low']);
        $response->assertSee('Showing 1 of 4 inventory items.')->assertSee('Out of Stock');

        $this->actingAs($this->staff)->get(route('inventory.index', ['search' => 'nothing like this']))
            ->assertSee('No inventory items match your filters');
    }

    public function test_storage_locations_can_be_searched_and_filtered(): void
    {
        StorageLocation::create(['name' => 'Walk-in Freezer', 'type' => 'Freezer', 'is_active' => true]);
        StorageLocation::create(['name' => 'Chiller Fridge', 'type' => 'Refrigerator', 'is_active' => true]);
        StorageLocation::create(['name' => 'Old Freezer', 'type' => 'Freezer', 'is_active' => false]);

        $names = fn (array $query) => $this->actingAs($this->staff)->get(route('storage-locations.index', $query))
            ->assertOk()
            ->viewData('locations')
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $this->assertSame(['Old Freezer', 'Walk-in Freezer'], $names(['type' => 'Freezer']));
        $this->assertSame(['Old Freezer'], $names(['status' => 'inactive']));
        $this->assertSame(['Walk-in Freezer'], $names(['search' => 'walk', 'status' => 'active']));
        $this->assertCount(3, $names(['type' => 'Garage', 'status' => 'broken']), 'Unknown values are ignored');

        $this->actingAs($this->staff)->get(route('storage-locations.index', ['search' => 'nothing like this']))
            ->assertSee('No storage locations match your filters');
    }

    public function test_waste_form_can_be_prefilled_from_the_expiry_watch(): void
    {
        $this->actingAs($this->staff)
            ->get(route('waste-expiry.create', [
                'item_name' => 'Fresh milk', 'unit' => 'litre', 'reason' => 'expired', 'expiry_date' => '2026-10-12',
            ]))
            ->assertOk()
            ->assertSee('value="Fresh milk"', false)
            ->assertSee('<option value="litre" selected>', false)
            ->assertSee('<option value="expired" selected>', false)
            ->assertSee('value="2026-10-12"', false);

        // Invalid values are ignored instead of breaking the form
        $this->actingAs($this->staff)
            ->get(route('waste-expiry.create', ['unit' => 'barrel', 'reason' => 'stolen', 'expiry_date' => '2026-02-31']))
            ->assertOk()
            ->assertDontSee('selected>', false)
            ->assertDontSee('2026-02-31');
    }

    public function test_storage_summary_cards_and_quantities_follow_the_database(): void
    {
        $store = fn (string $name, string $type, string $active, ?int $quantity) => $this->actingAs($this->staff)
            ->post(route('storage-locations.store'), compact('name', 'type') + ['is_active' => $active, 'quantity' => $quantity])
            ->assertRedirect(route('storage-locations.index'));
        $summary = fn (array $query = []) => $this->actingAs($this->staff)->get(route('storage-locations.index', $query))->assertOk()->viewData('summary');

        $store('Freezer A', 'Freezer', '1', 40);
        $store('Dry Shelf', 'Dry Storage', '0', 25);
        $store('Chiller', 'Refrigerator', '1', null);   // quantity is optional

        $this->assertSame(['total' => 3, 'active' => 2, 'items' => 65, 'counted' => 2, 'inactive' => 1], $summary());
        $this->actingAs($this->staff)->get(route('storage-locations.index'))
            ->assertSee('Recorded items')
            ->assertSee('Item counts entered on 2 of 3 locations')
            ->assertSee('40 items');

        // Filters change the table, not the totals
        $this->assertSame(3, $summary(['status' => 'inactive'])['total']);

        // Updating status and quantity
        $chiller = StorageLocation::where('name', 'Chiller')->firstOrFail();
        $this->actingAs($this->staff)->get(route('storage-locations.edit', $chiller))->assertOk()->assertSee('name="quantity"', false);
        $this->actingAs($this->staff)->put(route('storage-locations.update', $chiller), [
            'name' => 'Chiller', 'type' => 'Refrigerator', 'is_active' => '0', 'quantity' => 10,
        ])->assertRedirect(route('storage-locations.index'));
        $this->assertSame(['total' => 3, 'active' => 1, 'items' => 75, 'counted' => 3, 'inactive' => 2], $summary());

        // Only whole, non-negative numbers are accepted
        foreach ([-5, 2.5, 'lots'] as $invalid) {
            $this->actingAs($this->staff)->put(route('storage-locations.update', $chiller), [
                'name' => 'Chiller', 'type' => 'Refrigerator', 'is_active' => '1', 'quantity' => $invalid,
            ])->assertSessionHasErrors('quantity');
        }
        $this->assertSame(10, $chiller->fresh()->quantity);

        // Deleting a location
        $this->actingAs($this->staff)->delete(route('storage-locations.destroy', $chiller))->assertRedirect(route('storage-locations.index'));
        $this->assertSame(['total' => 2, 'active' => 1, 'items' => 65, 'counted' => 2, 'inactive' => 1], $summary());
    }
}
