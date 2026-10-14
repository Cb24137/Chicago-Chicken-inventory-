<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Notifications\InventoryAlert;
use App\Support\Quiet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $otherAdmin;
    private User $staff;
    private User $otherStaff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin One']);
        $this->otherAdmin = User::factory()->create(['role' => 'admin', 'name' => 'Admin Two']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff One']);
        $this->otherStaff = User::factory()->create(['role' => 'staff', 'name' => 'Staff Two']);
    }

    // Titles of the notifications a user has received
    private function titles(User $user): array
    {
        return $user->notifications()->get()->pluck('data.title')->sort()->values()->all();
    }

    private function alert(string $category, string $title, string $message, ?string $url = '/inventory'): InventoryAlert
    {
        return new InventoryAlert($category, 'warning', $title, $message, $url);
    }

    private function updateItem(InventoryItem $item, float $quantity): void
    {
        $this->actingAs($this->staff)->put(route('inventory.update', $item->id), [
            'item_name' => $item->item_name,
            'category' => $item->category,
            'unit' => $item->unit,
            'quantity' => $quantity,
            'minimum_stock' => (float) $item->minimum_stock,
            'unit_price' => (float) $item->unit_price,
        ])->assertRedirect(route('inventory.index'));
    }

    private function orderData(Supplier $supplier, string $status, array $extra = []): array
    {
        return array_merge([
            'supplier_id' => $supplier->id,
            'item_name' => 'Chicken breast',
            'quantity' => 10,
            'unit' => 'kg',
            'unit_price' => 12,
            'purchase_date' => now()->toDateString(),
            'status' => $status,
        ], $extra);
    }

    // ---------- When alerts are sent ----------

    public function test_low_stock_alerts_are_sent_once_and_re_armed_after_restocking(): void
    {
        $item = Quiet::run(fn () => InventoryItem::create([
            'item_name' => 'Chicken Wings', 'category' => 'Food', 'unit' => 'kg',
            'quantity' => 20, 'minimum_stock' => 10, 'unit_price' => 14,
        ]));

        $this->updateItem($item, 8);    // reaches the minimum: alert
        $this->updateItem($item, 6);    // still low: no repeat

        $this->assertSame(['Low stock'], $this->titles($this->admin));
        $this->assertSame(['Low stock'], $this->titles($this->otherStaff));
        $this->assertSame([], $this->titles($this->staff), 'The person who made the change is not notified');

        $alert = $this->admin->notifications()->first()->data;
        $this->assertSame('stock', $alert['category']);
        $this->assertSame('warning', $alert['severity']);
        $this->assertSame('Warning: Chicken Wings has reached its minimum stock level (8 kg left, minimum 10 kg).', $alert['message']);
        $this->assertSame(route('inventory.index', ['search' => 'Chicken Wings'], false), $alert['url']);

        $this->updateItem($item, 0);    // runs out: critical alert
        $this->updateItem($item, 25);   // restocked: alert re-armed, nothing sent
        $this->assertNull($item->fresh()->stock_alert_level);
        $this->updateItem($item, 9);    // low again: alerted again

        $this->assertSame(['Low stock', 'Low stock', 'Out of stock'], $this->titles($this->admin));
        $this->assertSame('critical', $this->admin->notifications()->where('data->title', 'Out of stock')->first()->data['severity']);
    }

    public function test_purchase_order_alerts_reach_the_right_people(): void
    {
        $supplier = Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm']));

        // Created by staff: administrators are told
        $this->actingAs($this->staff)->post(route('purchases.store'), $this->orderData($supplier, 'pending'))
            ->assertRedirect(route('purchases.index'));
        $order = Purchase::firstOrFail();

        $this->assertSame(['New purchase order'], $this->titles($this->admin));
        $this->assertSame(['New purchase order'], $this->titles($this->otherAdmin));
        $this->assertSame([], $this->titles($this->otherStaff));

        $alert = $this->admin->notifications()->first()->data;
        $this->assertStringContainsString("New purchase order {$order->po_number} has been created", $alert['message']);
        $this->assertSame(route('purchases.show', $order, false), $alert['url']);

        // Placed by an administrator: the other administrators are told
        $this->actingAs($this->admin)->put(route('purchases.update', $order), $this->orderData($supplier, 'ordered'))
            ->assertRedirect(route('purchases.index'));

        $this->assertSame(['New purchase order'], $this->titles($this->admin));
        $this->assertSame(['New purchase order', 'Order placed'], $this->titles($this->otherAdmin));

        // Received by staff: everyone else is told
        $this->actingAs($this->staff)->patch(route('purchases.receive', $order))->assertSessionHas('success');

        $this->assertSame(['New purchase order', 'Order received'], $this->titles($this->admin));
        $this->assertSame(['Order received'], $this->titles($this->otherStaff));
        $this->assertSame([], $this->titles($this->staff));

        // Cancelled: administrators only
        $second = Quiet::run(fn () => Purchase::create($this->orderData($supplier, 'pending', ['total_cost' => 120])));
        $this->actingAs($this->otherAdmin)->put(route('purchases.update', $second), $this->orderData($supplier, 'cancelled'))
            ->assertRedirect(route('purchases.index'));

        $this->assertContains('Order cancelled', $this->titles($this->admin));
        $this->assertNotContains('Order cancelled', $this->titles($this->otherStaff));
    }

    public function test_receiving_a_delivery_close_to_expiry_alerts_everyone_once(): void
    {
        $supplier = Quiet::run(fn () => Supplier::create(['name' => 'Dairy Co']));
        $order = Quiet::run(fn () => Purchase::create($this->orderData($supplier, 'ordered', [
            'item_name' => 'Fresh milk', 'unit' => 'litres', 'total_cost' => 120,
        ])));

        $this->actingAs($this->staff)->patch(route('purchases.receive', $order), [
            'expiry_date' => now()->addDays(2)->toDateString(),
        ])->assertSessionHas('success');

        $order->refresh();
        $this->assertSame(now()->addDays(2)->toDateString(), $order->expiry_date->toDateString());
        $this->assertNotNull($order->expiry_alert_sent_at);

        $expiry = fn (User $user) => $user->notifications()->where('data->category', 'expiry')->get();

        $this->assertCount(1, $expiry($this->admin));
        $this->assertCount(1, $expiry($this->otherStaff));
        $this->assertCount(0, $expiry($this->staff));
        $this->assertStringContainsString(
            "Fresh milk from {$order->po_number} expires in 2 days",
            $expiry($this->admin)->first()->data['message']
        );

        // The daily check never repeats an expiry alert
        $this->artisan('inventory:check-alerts')->expectsOutput('0 notification(s) sent.')->assertSuccessful();
        $this->assertCount(1, $expiry($this->admin));

        // A corrected expiry date is watched again
        $this->actingAs($this->admin)->put(route('purchases.update', $order), $this->orderData($supplier, 'received', [
            'item_name' => 'Fresh milk', 'unit' => 'litres', 'expiry_date' => now()->addDay()->toDateString(),
        ]))->assertRedirect(route('purchases.index'));

        $this->assertCount(2, $expiry($this->otherStaff));
        $this->assertTrue($expiry($this->otherStaff)->contains(fn ($alert) => str_contains($alert->data['message'], 'expires tomorrow')));
    }

    public function test_receive_rejects_an_invalid_expiry_date(): void
    {
        $supplier = Quiet::run(fn () => Supplier::create(['name' => 'Dairy Co']));
        $order = Quiet::run(fn () => Purchase::create($this->orderData($supplier, 'ordered', ['total_cost' => 120])));

        $this->actingAs($this->staff)->patch(route('purchases.receive', $order), ['expiry_date' => '31/12/2026'])
            ->assertSessionHasErrors('expiry_date');

        $this->assertSame('ordered', $order->fresh()->status);
    }

    public function test_the_daily_check_alerts_everyone_once_including_the_first_visitor(): void
    {
        Quiet::run(function () {
            InventoryItem::create(['item_name' => 'Lettuce', 'category' => 'Food', 'unit' => 'kg', 'quantity' => 2, 'minimum_stock' => 5, 'unit_price' => 8]);
            InventoryItem::create(['item_name' => 'Rice', 'category' => 'Food', 'unit' => 'kg', 'quantity' => 50, 'minimum_stock' => 5, 'unit_price' => 3]);

            $supplier = Supplier::create(['name' => 'Dairy Co']);
            Purchase::create($this->orderData($supplier, 'received', [
                'item_name' => 'Cream', 'total_cost' => 120, 'expiry_date' => now()->addDay()->toDateString(),
            ]));
        });

        $this->assertSame(0, DatabaseNotification::count());

        // The daily check is not anyone's action, so the first visitor is notified too
        $this->actingAs($this->staff)->get(route('dashboard'))->assertOk();

        foreach ([$this->admin, $this->otherAdmin, $this->staff, $this->otherStaff] as $user) {
            $this->assertSame(['Approaching expiry', 'Low stock'], $this->titles($user), $user->name);
        }

        // Later visits and the command never repeat them
        $this->actingAs($this->admin)->get(route('dashboard'))->assertOk();
        $this->artisan('inventory:check-alerts')->expectsOutput('0 notification(s) sent.')->assertSuccessful();

        $this->assertSame(8, DatabaseNotification::count());
    }

    public function test_waste_records_and_staff_accounts_alert_administrators(): void
    {
        $this->actingAs($this->staff)->post(route('waste-expiry.store'), [
            'item_name' => 'Milk', 'quantity' => 2, 'unit' => 'litre',
            'reason' => 'expired', 'record_date' => now()->toDateString(),
        ])->assertRedirect(route('waste-expiry.index'));

        $this->assertSame(['Waste recorded'], $this->titles($this->admin));
        $this->assertSame(['Waste recorded'], $this->titles($this->otherAdmin));
        $this->assertSame([], $this->titles($this->otherStaff));

        // Accounts created in the app get the "staff" role from the database default
        $this->actingAs($this->admin)->post(route('admin.staff.store'), [
            'name' => 'New Cashier', 'email' => 'cashier@example.com',
            'password' => 'secret123', 'password_confirmation' => 'secret123',
        ])->assertRedirect(route('admin.staff.index'));

        $cashier = User::where('email', 'cashier@example.com')->firstOrFail();
        $this->assertSame('staff', $cashier->role);

        $this->actingAs($this->admin)->delete(route('admin.staff.destroy', $cashier))
            ->assertRedirect(route('admin.staff.index'));

        $this->assertSame(['Staff account created', 'Staff account removed', 'Waste recorded'], $this->titles($this->otherAdmin));
        $this->assertSame(['Waste recorded'], $this->titles($this->admin), 'The admin who made the change is not notified');
        $this->assertSame([], $this->titles($this->otherStaff));
    }

    // ---------- Notification Center ----------

    public function test_the_notification_center_lists_only_your_own_notifications(): void
    {
        $this->staff->notify($this->alert('stock', 'Low stock', 'Warning: Lettuce is low.'));
        $this->staff->notify($this->alert('purchase', 'Order received', 'PO-2026-00001 received.', '/purchases'));
        $this->otherStaff->notify($this->alert('stock', 'Low stock', 'Alert meant for someone else.'));

        $this->staff->notifications()->where('data->title', 'Order received')->first()->markAsRead();

        $this->actingAs($this->staff)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Warning: Lettuce is low.')
            ->assertSee('PO-2026-00001 received.')
            ->assertDontSee('Alert meant for someone else.');

        // (The top bar's bell also lists recent read notifications, so check the page's list itself)
        $listed = fn (array $query) => $this->actingAs($this->staff)->get(route('notifications.index', $query))
            ->assertOk()
            ->viewData('notifications')
            ->pluck('data.message')
            ->all();

        $this->assertSame(['Warning: Lettuce is low.'], $listed(['filter' => 'unread']));
        $this->assertSame(['PO-2026-00001 received.'], $listed(['category' => 'purchase']));
        $this->assertSame([], $listed(['filter' => 'unread', 'category' => 'purchase']));

        $unread = $this->actingAs($this->staff)->get(route('notifications.index', ['filter' => 'unread']));
        $this->assertSame(1, $unread->viewData('unreadCount'));
        $this->assertSame(2, $unread->viewData('totalCount'));

        // The staff-account category is for administrators only
        $response = $this->actingAs($this->staff)->get(route('notifications.index', ['category' => 'staff']))->assertOk();
        $this->assertNull($response->viewData('category'));
        $this->assertArrayNotHasKey('staff', $response->viewData('categories'));
        $this->assertArrayHasKey('staff', $this->actingAs($this->admin)->get(route('notifications.index'))->viewData('categories'));
    }

    public function test_other_users_notifications_cannot_be_opened_or_changed(): void
    {
        $this->otherStaff->notify($this->alert('stock', 'Low stock', 'Not yours.'));
        $theirs = $this->otherStaff->notifications()->first();

        $this->actingAs($this->staff)->get(route('notifications.open', $theirs->id))->assertNotFound();
        $this->actingAs($this->staff)->patch(route('notifications.read', $theirs->id))->assertNotFound();
        $this->actingAs($this->staff)->post(route('notifications.read-all'));

        $this->assertNull($theirs->fresh()->read_at);
        $this->actingAs($this->staff)->patch(route('notifications.read', 'not-a-real-id'))->assertNotFound();
    }

    public function test_opening_a_notification_marks_it_read_and_only_follows_links_inside_the_app(): void
    {
        $this->staff->notify($this->alert('purchase', 'Order received', 'Local link', '/purchases/1'));
        $this->staff->notify($this->alert('stock', 'Low stock', 'External link', 'https://example.com/phishing'));
        $this->staff->notify($this->alert('stock', 'Low stock', 'Protocol-relative link', '//example.com'));

        $find = fn (string $message) => $this->staff->notifications()->where('data->message', $message)->first();

        $this->actingAs($this->staff)->get(route('notifications.open', $find('Local link')->id))
            ->assertRedirect('/purchases/1');
        $this->assertNotNull($find('Local link')->read_at);

        foreach (['External link', 'Protocol-relative link'] as $message) {
            $this->actingAs($this->staff)->get(route('notifications.open', $find($message)->id))
                ->assertRedirect(route('notifications.index'));
        }

        $this->assertSame(0, $this->staff->unreadNotifications()->count());
    }

    public function test_mark_as_read_and_mark_all_as_read(): void
    {
        foreach (range(1, 3) as $number) {
            $this->staff->notify($this->alert('stock', "Alert {$number}", "Message {$number}"));
        }
        $this->otherStaff->notify($this->alert('stock', 'Theirs', 'Theirs'));
        $first = $this->staff->notifications()->where('data->title', 'Alert 1')->first();

        $this->actingAs($this->staff)->from(route('notifications.index'))
            ->patch(route('notifications.read', $first->id))
            ->assertRedirect(route('notifications.index'))
            ->assertSessionHas('success', 'Notification marked as read.');
        $this->assertNotNull($first->fresh()->read_at);

        $this->actingAs($this->staff)->from(route('dashboard'))
            ->post(route('notifications.read-all'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', '2 notifications marked as read.');

        $this->assertSame(0, $this->staff->unreadNotifications()->count());
        $this->assertSame(3, $this->staff->notifications()->count(), 'Read notifications stay as history');
        $this->assertSame(1, $this->otherStaff->unreadNotifications()->count());
    }

    public function test_the_bell_shows_the_unread_count_and_latest_alerts(): void
    {
        foreach (range(1, 3) as $number) {
            $this->staff->notify($this->alert('stock', "Alert {$number}", "Bell message {$number}"));
        }

        $this->actingAs($this->staff)->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Notifications, 3 unread')
            ->assertSee('<span class="ccc-bell-count" aria-hidden="true">3</span>', false)
            ->assertSee('Bell message 2')
            ->assertSee('ccc-nav-count', false)
            ->assertSee(route('notifications.open', $this->staff->notifications()->first()->id), false);

        $this->actingAs($this->staff)->post(route('notifications.read-all'));

        $this->actingAs($this->staff)->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Notifications, 0 unread')
            ->assertDontSee('ccc-bell-count', false)
            ->assertDontSee('ccc-nav-count', false);
    }

    public function test_guests_cannot_reach_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->post(route('notifications.read-all'))->assertRedirect(route('login'));
    }
}
