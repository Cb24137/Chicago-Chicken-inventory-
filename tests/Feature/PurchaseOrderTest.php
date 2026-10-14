<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;
    private Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->supplier = Supplier::create(['name' => 'Fresh Farm']);
    }

    private function orderData(array $overrides = []): array
    {
        return array_merge([
            'supplier_id' => $this->supplier->id,
            'item_name' => 'Chicken breast',
            'quantity' => '12.5',
            'unit' => 'kg',
            'unit_price' => '8.40',
            'purchase_date' => '2026-10-01',
            'status' => 'pending',
            'notes' => 'Deliver before 9am',
        ], $overrides);
    }

    private function inventoryItem(float $quantity = 5, string $unit = 'kg'): InventoryItem
    {
        return InventoryItem::create([
            'item_name' => 'Chicken',
            'category' => 'Food',
            'unit' => $unit,
            'quantity' => $quantity,
            'minimum_stock' => 2,
            'unit_price' => 9,
        ]);
    }

    // ---------- Create ----------

    public function test_staff_can_create_a_purchase_order_with_an_automatic_total(): void
    {
        $this->actingAs($this->staff)
            ->post(route('purchases.store'), $this->orderData(['total_cost' => '1.00']))
            ->assertRedirect(route('purchases.index'))
            ->assertSessionHas('success');

        $purchase = Purchase::firstOrFail();

        // 12.5 x RM 8.40 - the total sent by the browser is ignored
        $this->assertSame('105.00', $purchase->total_cost);
        $this->assertSame('8.40', $purchase->unit_price);
        $this->assertSame('pending', $purchase->status);
        $this->assertSame('Deliver before 9am', $purchase->notes);
        $this->assertTrue($purchase->supplier->is($this->supplier));
        $this->assertMatchesRegularExpression('/^PO-\d{4}-\d{5}$/', $purchase->po_number);
    }

    public function test_purchase_order_numbers_are_unique(): void
    {
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());

        $numbers = Purchase::pluck('po_number');

        $this->assertCount(2, $numbers);
        $this->assertCount(2, $numbers->unique());
    }

    public function test_purchase_orders_are_validated(): void
    {
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'supplier_id' => '',
            'item_name' => '',
            'quantity' => '0',
            'unit' => 'tonnes',
            'unit_price' => '-1',
            'purchase_date' => 'not-a-date',
            'status' => 'shipped',
        ]))->assertSessionHasErrors([
            'supplier_id', 'item_name', 'quantity', 'unit', 'unit_price', 'purchase_date', 'status',
        ]);

        $this->assertSame(0, Purchase::count());
    }

    public function test_pending_orders_do_not_change_inventory_stock(): void
    {
        $item = $this->inventoryItem(5);

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'status' => 'ordered',
        ]));

        $this->assertSame('5.00', $item->fresh()->quantity);
        $this->assertNull(Purchase::first()->stock_added_at);
    }

    public function test_product_name_defaults_to_the_linked_inventory_item(): void
    {
        $item = $this->inventoryItem();

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'item_name' => '',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('Chicken', Purchase::first()->item_name);
    }

    public function test_units_that_cannot_go_into_the_inventory_item_are_rejected(): void
    {
        $item = $this->inventoryItem(5, 'kg');

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'unit' => 'boxes',
        ]))->assertSessionHasErrors('unit');

        $this->assertSame(0, Purchase::count());
    }

    // ---------- Read ----------

    public function test_index_supports_search_filters_and_pagination(): void
    {
        $other = Supplier::create(['name' => 'Drinks Co']);

        foreach (range(1, 11) as $i) {
            Purchase::create($this->orderData(['item_name' => "Chicken batch {$i}", 'total_cost' => 1]));
        }
        Purchase::create($this->orderData([
            'supplier_id' => $other->id,
            'item_name' => 'Cola',
            'status' => 'received',
            'purchase_date' => '2026-09-15',
            'total_cost' => 1,
        ]));

        $page = $this->actingAs($this->staff)->get(route('purchases.index'))->assertOk();
        $this->assertSame(12, $page->viewData('purchases')->total());
        $this->assertCount(10, $page->viewData('purchases')->items());

        $this->assertCount(2, $this->actingAs($this->staff)->get(route('purchases.index', ['page' => 2]))->viewData('purchases')->items());

        $byStatus = $this->actingAs($this->staff)->get(route('purchases.index', ['status' => 'received']))->viewData('purchases');
        $this->assertSame(['Cola'], collect($byStatus->items())->pluck('item_name')->all());

        $bySupplier = $this->actingAs($this->staff)->get(route('purchases.index', ['supplier_id' => $other->id]))->viewData('purchases');
        $this->assertSame(1, $bySupplier->total());

        $bySearch = $this->actingAs($this->staff)->get(route('purchases.index', ['search' => 'Drinks']))->viewData('purchases');
        $this->assertSame(['Cola'], collect($bySearch->items())->pluck('item_name')->all());

        $poNumber = Purchase::where('item_name', 'Chicken batch 3')->value('po_number');
        $byNumber = $this->actingAs($this->staff)->get(route('purchases.index', ['search' => $poNumber]))->viewData('purchases');
        $this->assertSame(['Chicken batch 3'], collect($byNumber->items())->pluck('item_name')->all());

        $byDate = $this->actingAs($this->staff)->get(route('purchases.index', ['from' => '2026-09-01', 'to' => '2026-09-30']))->viewData('purchases');
        $this->assertSame(['Cola'], collect($byDate->items())->pluck('item_name')->all());
    }

    public function test_index_and_show_pages_display_order_details(): void
    {
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->get(route('purchases.index'))
            ->assertOk()
            ->assertSee($purchase->po_number)
            ->assertSee('Fresh Farm')
            ->assertSee('105.00');

        $this->actingAs($this->staff)->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertSee($purchase->po_number)
            ->assertSee('RM 105.00')
            ->assertSee('Deliver before 9am');
    }

    public function test_orders_record_an_expiry_date_and_are_received_from_the_receive_panel(): void
    {
        $expiry = now()->addDays(2)->toDateString();

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData(['expiry_date' => $expiry]))
            ->assertRedirect(route('purchases.index'));
        $purchase = Purchase::firstOrFail();
        $this->assertSame($expiry, $purchase->expiry_date->toDateString());

        // The list's receive button opens the receive panel, where the expiry date can be checked
        $this->actingAs($this->staff)->get(route('purchases.index'))
            ->assertSee(route('purchases.show', $purchase) . '#receive', false);

        $this->actingAs($this->staff)->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertSee('id="receive"', false)
            ->assertSee('action="' . route('purchases.receive', $purchase) . '"', false)
            ->assertSee('value="' . $expiry . '"', false)
            ->assertSee('Not received yet');

        $this->actingAs($this->staff)->patch(route('purchases.receive', $purchase), ['expiry_date' => $expiry])
            ->assertSessionHas('success');

        $this->actingAs($this->staff)->get(route('purchases.show', $purchase))
            ->assertOk()
            ->assertDontSee('id="receive_expiry_date"', false)
            ->assertSee($purchase->fresh()->expiry_date->format('d M Y'))
            ->assertSee('Expires in 2 days');

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData(['expiry_date' => 'next week']))
            ->assertSessionHasErrors('expiry_date');
    }

    public function test_reorder_link_prefills_the_inventory_item(): void
    {
        $item = $this->inventoryItem();

        $this->actingAs($this->staff)
            ->get(route('purchases.create', ['inventory_item_id' => $item->id]))
            ->assertOk()
            ->assertSee('value="Chicken"', false)
            ->assertSee('<option value="kg" selected>', false);
    }

    // ---------- Update ----------

    public function test_editing_recalculates_the_total_cost(): void
    {
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->get(route('purchases.edit', $purchase))->assertOk();

        $this->actingAs($this->staff)->put(route('purchases.update', $purchase), $this->orderData([
            'quantity' => '3',
            'unit_price' => '10.25',
            'total_cost' => '9999',
            'status' => 'ordered',
        ]))->assertRedirect(route('purchases.index'));

        $purchase->refresh();
        $this->assertSame('30.75', $purchase->total_cost);
        $this->assertSame('ordered', $purchase->status);
    }

    // ---------- Delete ----------

    public function test_purchase_orders_can_be_deleted(): void
    {
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->delete(route('purchases.destroy', $purchase))
            ->assertRedirect(route('purchases.index'));

        $this->assertModelMissing($purchase);
    }

    // ---------- Receiving workflow ----------

    public function test_receiving_adds_stock_to_inventory_only_once(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'quantity' => '10',
        ]));
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->patch(route('purchases.receive', $purchase))
            ->assertSessionHas('success');

        $purchase->refresh();
        $this->assertSame('15.00', $item->fresh()->quantity);
        $this->assertSame('received', $purchase->status);
        $this->assertNotNull($purchase->received_at);
        $this->assertSame('10.00', $purchase->stock_added_quantity);

        // A second receive, or saving the order again, never adds more stock
        $this->actingAs($this->staff)->patch(route('purchases.receive', $purchase))
            ->assertSessionHas('error');
        $this->actingAs($this->staff)->put(route('purchases.update', $purchase), $this->orderData([
            'inventory_item_id' => $item->id,
            'quantity' => '10',
            'status' => 'received',
        ]));

        $this->assertSame('15.00', $item->fresh()->quantity);
        $this->assertDatabaseHas('activity_logs', ['module' => 'inventory', 'action' => 'received']);
    }

    public function test_creating_an_order_as_received_adds_stock(): void
    {
        $item = $this->inventoryItem(5);

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'quantity' => '2',
            'status' => 'received',
        ]));

        $this->assertSame('7.00', $item->fresh()->quantity);
    }

    public function test_received_grams_are_converted_to_kilograms(): void
    {
        $item = $this->inventoryItem(5, 'kg');

        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'quantity' => '500',
            'unit' => 'g',
            'unit_price' => '0.02',
            'status' => 'received',
        ]))->assertSessionHasNoErrors();

        $this->assertSame('5.50', $item->fresh()->quantity);
        $this->assertSame('10.00', Purchase::first()->total_cost);
    }

    public function test_stock_fields_are_locked_after_stock_is_added(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'quantity' => '10',
            'status' => 'received',
        ]));
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->put(route('purchases.update', $purchase), $this->orderData([
            'inventory_item_id' => '',
            'quantity' => '999',
            'unit' => 'g',
            'status' => 'cancelled',
            'unit_price' => '2',
        ]))->assertSessionHasNoErrors();

        $purchase->refresh();
        $this->assertSame('10.00', $purchase->quantity);
        $this->assertSame('kg', $purchase->unit);
        $this->assertSame('received', $purchase->status);
        $this->assertSame($item->id, $purchase->inventory_item_id);
        $this->assertSame('20.00', $purchase->total_cost);    // price can still change
        $this->assertSame('15.00', $item->fresh()->quantity);
    }

    public function test_orders_that_added_stock_cannot_be_deleted(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'status' => 'received',
        ]));
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->delete(route('purchases.destroy', $purchase))
            ->assertSessionHas('error');

        $this->assertModelExists($purchase);
    }

    public function test_cancelled_orders_cannot_be_received(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
            'status' => 'cancelled',
        ]));
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->patch(route('purchases.receive', $purchase))
            ->assertSessionHas('error');

        $this->assertSame('cancelled', $purchase->fresh()->status);
        $this->assertSame('5.00', $item->fresh()->quantity);
    }

    public function test_unlinked_orders_can_be_received_without_changing_stock(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData());
        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->staff)->patch(route('purchases.receive', $purchase))
            ->assertSessionHas('success');

        $purchase->refresh();
        $this->assertSame('received', $purchase->status);
        $this->assertNull($purchase->stock_added_at);
        $this->assertSame('5.00', $item->fresh()->quantity);
    }

    public function test_deleting_an_inventory_item_keeps_its_purchase_orders(): void
    {
        $item = $this->inventoryItem(5);
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData([
            'inventory_item_id' => $item->id,
        ]));

        $this->actingAs($this->staff)->delete(route('inventory.destroy', $item->id))
            ->assertRedirect(route('inventory.index'));

        $purchase = Purchase::firstOrFail();
        $this->assertNull($purchase->inventory_item_id);
        $this->assertSame('Chicken breast', $purchase->item_name);
    }
}
