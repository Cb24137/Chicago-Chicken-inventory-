<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
    }

    public function test_staff_can_create_a_supplier_with_contact_details_only(): void
    {
        $this->actingAs($this->staff)->post(route('suppliers.store'), [
            'name' => 'Fresh Farm',
            'phone' => '012-345 6789',
            'email' => 'orders@freshfarm.test',
            'address' => 'Jalan Ampang, Kuala Lumpur',
            'products_supplied' => 'Frozen chicken',
            // Purchase fields no longer belong to suppliers and are ignored
            'quantity' => 99,
            'unit' => 'kg',
            'total_cost' => 500,
        ])->assertRedirect(route('suppliers.index'));

        $row = DB::table('suppliers')->first();

        $this->assertSame('Fresh Farm', $row->name);
        $this->assertSame('Frozen chicken', $row->products_supplied);
        $this->assertNull($row->quantity);
        $this->assertNull($row->unit);
        $this->assertNull($row->total_cost);
    }

    public function test_updating_a_supplier_keeps_historical_quantity_unit_and_cost(): void
    {
        $supplier = Supplier::create(['name' => 'Old Supplier']);
        DB::table('suppliers')->where('id', $supplier->id)->update([
            'quantity' => 10,
            'unit' => 'kg',
            'total_cost' => 100,
        ]);

        $this->actingAs($this->staff)->put(route('suppliers.update', $supplier), [
            'name' => 'Renamed Supplier',
            'phone' => '011',
        ])->assertRedirect(route('suppliers.index'));

        $this->assertDatabaseHas('suppliers', [
            'id' => $supplier->id,
            'name' => 'Renamed Supplier',
            'quantity' => 10,
            'unit' => 'kg',
            'total_cost' => 100,
        ]);
    }

    public function test_supplier_screens_no_longer_show_purchase_fields(): void
    {
        $supplier = Supplier::create(['name' => 'Fresh Farm']);
        DB::table('suppliers')->where('id', $supplier->id)->update(['total_cost' => 123.45]);

        $this->actingAs($this->staff)->get(route('suppliers.index'))
            ->assertOk()
            ->assertSee('Products Supplied')
            ->assertDontSee('Total Cost')
            ->assertDontSee('123.45');

        foreach ([route('suppliers.create'), route('suppliers.edit', $supplier)] as $url) {
            $this->actingAs($this->staff)->get($url)
                ->assertOk()
                ->assertSee('name="products_supplied"', false)
                ->assertDontSee('name="quantity"', false)
                ->assertDontSee('name="unit"', false)
                ->assertDontSee('name="total_cost"', false);
        }
    }

    public function test_supplier_details_are_validated(): void
    {
        $this->actingAs($this->staff)
            ->post(route('suppliers.store'), ['name' => '', 'email' => 'not-an-email'])
            ->assertSessionHasErrors(['name', 'email']);

        $this->assertSame(0, Supplier::count());
    }

    public function test_suppliers_can_be_searched(): void
    {
        Supplier::create(['name' => 'Fresh Farm', 'products_supplied' => 'Chicken']);
        Supplier::create(['name' => 'Drinks Co', 'products_supplied' => 'Soft drinks']);

        $this->actingAs($this->staff)->get(route('suppliers.index', ['search' => 'drinks']))
            ->assertOk()
            ->assertSee('Drinks Co')
            ->assertDontSee('Fresh Farm');
    }

    public function test_supplier_without_orders_can_be_deleted(): void
    {
        $supplier = Supplier::create(['name' => 'Fresh Farm']);

        $this->actingAs($this->staff)->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('success');

        $this->assertModelMissing($supplier);
    }

    public function test_supplier_with_purchase_orders_cannot_be_deleted(): void
    {
        $supplier = Supplier::create(['name' => 'Fresh Farm']);
        Purchase::create([
            'supplier_id' => $supplier->id,
            'item_name' => 'Chicken',
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => 10,
            'total_cost' => 10,
            'purchase_date' => '2026-10-01',
            'status' => 'pending',
        ]);

        $this->actingAs($this->staff)->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'))
            ->assertSessionHas('error');

        $this->assertModelExists($supplier);
    }

    public function test_summary_cards_follow_the_database(): void
    {
        $order = fn (Supplier $supplier, string $status, float $total) => Purchase::create([
            'supplier_id' => $supplier->id, 'item_name' => 'Chicken', 'quantity' => 1, 'unit' => 'kg',
            'unit_price' => $total, 'total_cost' => $total, 'purchase_date' => '2026-10-01', 'status' => $status,
        ]);
        $summary = fn (array $query = []) => $this->actingAs($this->staff)->get(route('suppliers.index', $query))->assertOk()->viewData('summary');

        $farm = Supplier::create(['name' => 'Fresh Farm']);
        $drinks = Supplier::create(['name' => 'Drinks Co']);
        $idle = Supplier::create(['name' => 'Idle Co']);
        $order($farm, 'received', 100);
        $pending = $order($farm, 'pending', 50);
        $order($drinks, 'cancelled', 999);   // counted as an order, but not in the order value

        $this->assertSame(['suppliers' => 3, 'orders' => 3, 'withOrders' => 2, 'orderValue' => 150.0], $summary());
        $this->actingAs($this->staff)->get(route('suppliers.index'))
            ->assertSee('Total suppliers')
            ->assertSee('RM 150.00')
            ->assertSee('1 without any orders yet')
            ->assertSee('order value, not money paid');

        // The search changes the table, not the totals
        $this->assertSame(3, $summary(['search' => 'Fresh'])['suppliers']);

        // Adding and deleting suppliers
        $this->actingAs($this->staff)->post(route('suppliers.store'), ['name' => 'New Bakery'])->assertRedirect(route('suppliers.index'));
        $this->assertSame(4, $summary()['suppliers']);
        $this->actingAs($this->staff)->delete(route('suppliers.destroy', $idle))->assertRedirect(route('suppliers.index'));
        $this->assertSame(3, $summary()['suppliers']);

        // Creating a purchase order (2 kg x RM 30.00)
        $this->actingAs($this->staff)->post(route('purchases.store'), [
            'supplier_id' => $drinks->id, 'item_name' => 'Cola', 'quantity' => 2, 'unit' => 'kg',
            'unit_price' => 30, 'purchase_date' => '2026-10-02', 'status' => 'ordered',
        ])->assertRedirect(route('purchases.index'));
        $this->assertSame(['suppliers' => 3, 'orders' => 4, 'withOrders' => 2, 'orderValue' => 210.0], $summary());

        // Cancelling an order removes it from the order value
        $this->actingAs($this->staff)->put(route('purchases.update', $pending), [
            'supplier_id' => $farm->id, 'item_name' => 'Chicken', 'quantity' => 1, 'unit' => 'kg',
            'unit_price' => 50, 'purchase_date' => '2026-10-01', 'status' => 'cancelled',
        ])->assertRedirect(route('purchases.index'));
        $this->assertSame(160.0, $summary()['orderValue']);
    }
}
