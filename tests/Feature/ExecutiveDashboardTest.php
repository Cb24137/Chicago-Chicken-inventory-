<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use App\Notifications\InventoryAlert;
use App\Support\Quiet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// The administrators' executive dashboard and the Operational Summary report.
// "Today" is fixed at 14 Oct 2026, so "This month" is 1-14 Oct 2026 and it is
// compared with 1-14 Sep 2026.
class ExecutiveDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(Carbon::parse('2026-10-14 10:00:00'));
        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin One']);
    }

    // A small business whose figures are easy to check by hand
    private function seedBusiness(): Purchase
    {
        return Quiet::run(function () {
            $item = fn ($name, $category, $unit, $quantity, $minimum, $price) => InventoryItem::create([
                'item_name' => $name, 'category' => $category, 'unit' => $unit,
                'quantity' => $quantity, 'minimum_stock' => $minimum, 'unit_price' => $price,
            ]);
            $item('Chicken', 'Food', 'kg', 10, 5, 9.50);              // RM 95.00
            $item('Rice', 'Food', 'kg', 4, 5, 3.00);                  // RM 12.00, low stock
            $item('Cola', 'Beverage', 'pcs', 0, 24, 1.50);            // out of stock
            $item('Paper Cups', 'Packaging', 'pcs', 1000, 200, 0.04); // RM 40.00

            $farm = Supplier::create(['name' => 'Fresh Farm']);
            $drinks = Supplier::create(['name' => 'Drinks Co']);
            $paper = Supplier::create(['name' => 'Paper Co']);
            Supplier::create(['name' => 'Idle Supplier']);

            $order = fn (Supplier $supplier, string $date, string $status, float $total, array $extra = []) => Purchase::create(array_merge([
                'supplier_id' => $supplier->id, 'item_name' => "Order {$date}", 'quantity' => 1, 'unit' => 'kg',
                'unit_price' => $total, 'total_cost' => $total, 'purchase_date' => $date, 'status' => $status,
            ], $extra));

            // 1-14 Oct: RM 600 received, RM 130 awaiting delivery, RM 999 cancelled
            $order($farm, '2026-10-03', 'received', 300);
            $order($farm, '2026-10-10', 'received', 100);
            $order($farm, '2026-10-12', 'pending', 50);
            $order($drinks, '2026-10-05', 'received', 200);
            $order($drinks, '2026-10-08', 'cancelled', 999);
            $oldest = $order($drinks, '2026-10-01', 'ordered', 80);

            // September (1-14 Sep: RM 150) and last year
            $order($paper, '2026-09-05', 'received', 150);
            $order($paper, '2026-09-20', 'received', 400);
            $order($farm, '2026-09-25', 'received', 10, ['item_name' => 'Fresh milk', 'expiry_date' => '2026-10-16']);
            $order($farm, '2025-10-05', 'received', 70);

            $waste = fn ($name, $quantity, $unit, $reason, $date) => WasteExpiryRecord::create([
                'item_name' => $name, 'quantity' => $quantity, 'unit' => $unit, 'reason' => $reason, 'record_date' => $date,
            ]);
            $waste('Chicken', 2, 'kg', 'spoiled', '2026-10-06');          // RM 19.00
            $waste('Mystery Sauce', 1, 'pcs', 'damaged', '2026-10-09');   // no matching price
            $waste('Chicken', 1, 'kg', 'expired', '2026-09-07');          // RM 9.50

            return $oldest;
        });
    }

    private function dashboard(array $query = []): TestResponse
    {
        return $this->actingAs($this->admin)->get(route('dashboard', $query))->assertOk()->assertViewIs('admin-dashboard');
    }

    private function insight(TestResponse $response, string $title): array
    {
        return collect($response->viewData('insights'))->firstWhere('title', $title);
    }

    // ---------- Layout and roles ----------

    public function test_admins_get_the_executive_dashboard_and_staff_keep_their_workspace(): void
    {
        $this->dashboard()
            ->assertSee('Chicago Chicken City — Management Overview')
            ->assertSee('Monitor purchasing expenses, inventory performance, supplier activity, and operational efficiency.')
            ->assertSee('Key figures')
            ->assertSee('Business analytics')
            ->assertSee('Management insights')
            ->assertSee('Important notifications')
            ->assertSee('Reports &amp; analytics', false)
            ->assertSee('Staff management')
            ->assertDontSee('Quick actions')
            ->assertDontSee('Low Stock Alerts')
            ->assertDontSee('Orders to Receive');

        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('dashboard', ['period' => 'this_year']))
            ->assertOk()
            ->assertViewIs('staff-dashboard')
            ->assertSee('Quick actions')
            ->assertSee('Low Stock Alerts')
            ->assertDontSee('Management Overview')
            ->assertDontSee('Management insights');
    }

    public function test_admin_and_staff_sidebars_are_different(): void
    {
        $this->actingAs($this->admin)->get(route('inventory.index'))
            ->assertSee('Executive Dashboard')
            ->assertSee('Business Management')
            ->assertSee('Inventory Overview')
            ->assertSee('Management console')
            ->assertSee(route('admin.staff.index'), false)
            ->assertDontSee('Inventory Modules');

        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)->get(route('inventory.index'))
            ->assertSee('Inventory Modules')
            ->assertSee('Food &amp; Beverage Inventory', false)
            ->assertDontSee('Business Management')
            ->assertDontSee('Executive Dashboard')
            ->assertDontSee('Staff Management');
    }

    // ---------- KPIs ----------

    public function test_key_figures_use_live_data_for_this_month(): void
    {
        $oldest = $this->seedBusiness();
        $response = $this->dashboard();
        $kpis = $response->viewData('kpis');

        $this->assertSame('1–14 Oct 2026', $response->viewData('period')->label());
        $this->assertSame('1–14 Sep 2026', $response->viewData('previous')->label());

        // Inventory value = quantity x unit price
        $this->assertSame(147.0, $kpis['inventoryValue']['value']);
        $this->assertSame(4, $kpis['inventoryValue']['items']);
        $this->assertSame(['items' => 2, 'value' => 107.0, 'restock' => 1], $kpis['inventoryValue']['byCategory']['Food']);

        // Expenditure = received orders only (pending, ordered and cancelled are excluded)
        $this->assertSame(['value' => 600.0, 'orders' => 3, 'previous' => 150.0, 'change' => 300.0], $kpis['expenditure']);

        // Waste cost is estimated where a matching price exists
        $this->assertSame(
            ['records' => 2, 'cost' => 19.0, 'priced' => 1, 'previous' => 9.5, 'previousRecords' => 1, 'change' => 100.0],
            $kpis['wasteCost']
        );

        $this->assertSame(2, $kpis['awaiting']['count']);
        $this->assertSame(130.0, $kpis['awaiting']['value']);
        $this->assertSame(13, $kpis['awaiting']['oldestDays']);
        $this->assertTrue($kpis['awaiting']['oldest']->is($oldest));

        $this->assertSame(['total' => 4, 'active' => 2], $kpis['suppliers']);
        $this->assertSame(
            ['out' => 1, 'low' => 1, 'expired' => 0, 'expiring' => 1, 'critical' => 1, 'total' => 3, 'expiryTracked' => true],
            $kpis['alerts']
        );

        $response->assertSee('RM 147.00')
            ->assertSee('RM 600.00')
            ->assertSee('300.0% vs 1–14 Sep 2026')
            ->assertSee('RM 19.00')
            ->assertSee('100.0% vs 1–14 Sep 2026')
            ->assertSee('RM 130.00')
            ->assertSee('Oldest placed 13 days ago');
    }

    public function test_reporting_periods_and_their_comparisons(): void
    {
        $this->seedBusiness();

        $lastMonth = $this->dashboard(['period' => 'last_month']);
        $this->assertSame('1–30 Sep 2026', $lastMonth->viewData('period')->label());
        $this->assertSame('1–31 Aug 2026', $lastMonth->viewData('previous')->label());
        $this->assertSame(560.0, $lastMonth->viewData('kpis')['expenditure']['value']);
        $this->assertNull($lastMonth->viewData('kpis')['expenditure']['change']);
        $lastMonth->assertSee('Nothing to compare in 1–31 Aug 2026');

        $thisYear = $this->dashboard(['period' => 'this_year']);
        $this->assertSame('1 Jan – 14 Oct 2026', $thisYear->viewData('period')->label());
        $this->assertSame('1 Jan – 14 Oct 2025', $thisYear->viewData('previous')->label());
        $this->assertSame(1160.0, $thisYear->viewData('kpis')['expenditure']['value']);
        $this->assertSame(70.0, $thisYear->viewData('kpis')['expenditure']['previous']);
        $this->assertSame(1557.1, $thisYear->viewData('kpis')['expenditure']['change']);

        $lastThree = $this->dashboard(['period' => 'last_3_months']);
        $this->assertSame('1 Aug – 14 Oct 2026', $lastThree->viewData('period')->label());
        $this->assertSame(1160.0, $lastThree->viewData('kpis')['expenditure']['value']);

        // A custom range is compared with the same number of days just before it
        $custom = $this->dashboard(['period' => 'custom', 'from' => '2026-10-01', 'to' => '2026-10-07']);
        $this->assertSame('custom', $custom->viewData('period')->key);
        $this->assertSame('24–30 Sep 2026', $custom->viewData('previous')->label());
        $this->assertSame(['value' => 500.0, 'orders' => 2, 'previous' => 10.0, 'change' => 4900.0], $custom->viewData('kpis')['expenditure']);
        $custom->assertSee('Custom range · 1–7 Oct 2026');
    }

    public function test_invalid_reporting_periods_are_rejected(): void
    {
        $invalid = [
            [['period' => 'forever'], 'period'],
            [['period' => 'custom'], 'from'],
            [['period' => 'custom', 'from' => '2026-10-10', 'to' => '2026-10-01'], 'to'],
            [['period' => 'custom', 'from' => '2026-02-31', 'to' => '2026-03-01'], 'from'],
        ];

        foreach ($invalid as [$query, $field]) {
            $this->actingAs($this->admin)
                ->from(route('dashboard'))
                ->get(route('dashboard', $query))
                ->assertRedirect(route('dashboard'))
                ->assertSessionHasErrors($field);
        }
    }

    // ---------- Charts ----------

    public function test_charts_use_real_data_and_highlight_the_selected_period(): void
    {
        $this->seedBusiness();
        $charts = $this->dashboard()->viewData('charts');

        // 12 months up to the end of the period; October is the selected period
        $this->assertCount(12, $charts['expenditure']);
        $this->assertSame('2025-11', $charts['expenditure'][0]['month']);
        $this->assertSame(['month' => '2026-10', 'value' => 600.0, 'emphasis' => true], array_intersect_key($charts['expenditure'][11], ['month' => 0, 'value' => 0, 'emphasis' => 0]));
        $this->assertSame(['month' => '2026-09', 'value' => 560.0, 'emphasis' => false], array_intersect_key($charts['expenditure'][10], ['month' => 0, 'value' => 0, 'emphasis' => 0]));
        $this->assertSame(19.0, $charts['wasteCost'][11]['value']);
        $this->assertSame(9.5, $charts['wasteCost'][10]['value']);

        $this->assertSame([
            ['label' => 'Food', 'value' => 107.0, 'note' => '73%'],
            ['label' => 'Beverage', 'value' => 0.0, 'note' => '0%'],
            ['label' => 'Packaging', 'value' => 40.0, 'note' => '27%'],
            ['label' => 'Other', 'value' => 0.0, 'note' => '0%'],
        ], $charts['categoryValue']);

        $this->assertSame([
            ['label' => 'Fresh Farm', 'value' => 400.0, 'note' => '67%'],
            ['label' => 'Drinks Co', 'value' => 200.0, 'note' => '33%'],
        ], $charts['supplierSpend']);

        $thisYear = $this->dashboard(['period' => 'this_year'])->viewData('charts');
        $this->assertCount(10, array_filter(array_column($thisYear['expenditure'], 'emphasis')));
    }

    // ---------- Insights ----------

    public function test_management_insights_state_their_figures_and_basis(): void
    {
        $oldest = $this->seedBusiness();
        $response = $this->dashboard();

        $this->assertSame('2 items need replenishing: 1 out of stock and 1 at or below the minimum level.', $this->insight($response, 'Replenishment needed')['text']);
        $this->assertSame('RM 600.00 was spent in 1–14 Oct 2026, up 300.0% from RM 150.00 in 1–14 Sep 2026.', $this->insight($response, 'Purchase expenditure')['text']);
        $this->assertSame(
            'Fresh Farm accounts for 66.7% of purchase expenditure in 1–14 Oct 2026 (RM 400.00 of RM 600.00, 2 orders). 2 suppliers delivered received orders in this period.',
            $this->insight($response, 'Largest supplier')['text']
        );
        $this->assertSame(
            '2 waste records in 1–14 Oct 2026 (estimated RM 19.00), compared with 1 (estimated RM 9.50) in 1–14 Sep 2026.',
            $this->insight($response, 'Waste')['text']
        );
        $this->assertSame('warning', $this->insight($response, 'Waste')['tone']);
        $this->assertSame(
            "2 purchase orders worth RM 130.00 are pending or ordered. The oldest, {$oldest->po_number}, was placed 13 days ago.",
            $this->insight($response, 'Orders awaiting delivery')['text']
        );
        $this->assertSame('1 received delivery needs attention: 0 expired in the last 7 days and 1 expires within 3 days.', $this->insight($response, 'Expiry watch')['text']);

        $response->assertSee('Based on: Received purchase orders, by purchase date');
    }

    public function test_an_empty_database_gives_honest_empty_states(): void
    {
        $response = $this->dashboard();

        $this->assertSame('No inventory items have been added yet.', $this->insight($response, 'No inventory yet')['text']);
        $this->assertSame('No purchase orders were received in 1–14 Oct 2026 or in 1–14 Sep 2026.', $this->insight($response, 'Purchase expenditure')['text']);
        $this->assertStringStartsWith('No purchase orders were received', $this->insight($response, 'Largest supplier')['text']);
        $this->assertSame('No waste was recorded in 1–14 Oct 2026 or in 1–14 Sep 2026.', $this->insight($response, 'Waste')['text']);
        $this->assertSame('No purchase orders are waiting for delivery.', $this->insight($response, 'Orders awaiting delivery')['text']);
        $this->assertStringStartsWith('No expiry dates have been recorded', $this->insight($response, 'Expiry watch')['text']);

        $response->assertSee('No received orders yet')
            ->assertSee('No inventory value recorded')
            ->assertSee('No priced waste yet')
            ->assertSee('No received orders in this period')
            ->assertSee('No important notifications');
    }

    // ---------- Notifications, staff and reports ----------

    public function test_important_notifications_show_unread_critical_alerts_first(): void
    {
        $alert = fn (string $category, string $severity, string $title) => new InventoryAlert($category, $severity, $title, "{$title} message", '/dashboard');

        $this->admin->notify($alert('purchase', 'info', 'New purchase order'));      // not high priority
        $this->admin->notify($alert('stock', 'warning', 'Low stock'));
        $this->admin->notify($alert('stock', 'critical', 'Out of stock'));
        $this->admin->notify($alert('staff', 'info', 'Staff account created'));
        $this->admin->notify($alert('waste', 'warning', 'Waste recorded'));
        $this->admin->notifications()->where('data->title', 'Low stock')->first()->markAsRead();

        $otherAdmin = User::factory()->create(['role' => 'admin']);
        $otherAdmin->notify($alert('stock', 'critical', 'Someone else'));

        $response = $this->dashboard();

        $this->assertSame(
            ['Out of stock', 'Waste recorded', 'Staff account created', 'Low stock'],
            $response->viewData('importantNotifications')->pluck('data.title')->all()
        );
        $this->assertSame(4, $response->viewData('unreadCount'));
    }

    public function test_staff_management_panel_lists_accounts_and_access_levels(): void
    {
        $this->travelTo(Carbon::parse('2026-10-12 09:00'));
        User::factory()->create(['role' => 'staff', 'name' => 'Nur Aisyah']);
        $this->travelTo(Carbon::parse('2026-10-13 09:00'));
        User::factory()->create(['role' => 'staff', 'name' => 'Ali Hassan']);
        $this->travelTo(Carbon::parse('2026-10-14 10:00'));

        $response = $this->dashboard();
        $accounts = $response->viewData('staffAccounts');

        $this->assertSame(2, $accounts['staff']);
        $this->assertSame(1, $accounts['admins']);
        $this->assertSame(['Ali Hassan', 'Nur Aisyah'], $accounts['recent']->pluck('name')->all());

        $response->assertSee('Recently created staff accounts')
            ->assertSee('Added 13 Oct 2026')
            ->assertSee('Roles and access levels')
            ->assertSee('Purchase, supplier and operational summary reports')
            ->assertSee(route('admin.staff.create'), false);
    }

    public function test_report_cards_open_and_export_reports_for_the_period(): void
    {
        $this->seedBusiness();
        $response = $this->dashboard();
        $cards = collect($response->viewData('reportCards'))->keyBy('key');

        $this->assertSame(['inventory', 'purchases', 'suppliers', 'storage', 'waste', 'summary'], $cards->keys()->all());
        $this->assertSame('RM 600.00 spent · 3 received orders', $cards['purchases']['figure']);
        $this->assertSame('4 items · RM 147.00', $cards['inventory']['figure']);

        $response->assertSee(route('reports.show', ['report' => 'purchases', 'from' => '2026-10-01', 'to' => '2026-10-14']))
            ->assertSee(route('reports.export', ['report' => 'summary', 'format' => 'pdf', 'from' => '2026-10-01', 'to' => '2026-10-14']))
            ->assertSee(route('reports.export', ['report' => 'waste', 'format' => 'csv', 'from' => '2026-10-01', 'to' => '2026-10-14']))
            ->assertSee(route('reports.show', 'inventory'))
            ->assertSee(route('reports.export', ['report' => 'storage', 'format' => 'csv']));
    }

    // ---------- Operational Summary report ----------

    public function test_operational_summary_report_matches_the_dashboard_and_is_admin_only(): void
    {
        $this->seedBusiness();
        $period = ['from' => '2026-10-01', 'to' => '2026-10-14'];

        $response = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'summary'] + $period))->assertOk();
        $report = $response->viewData('report');

        $this->assertSame([
            'Inventory value' => 'RM 147.00',
            'Purchase expenditure' => 'RM 600.00',
            'Awaiting delivery' => 'RM 130.00',
            'Estimated waste cost' => 'RM 19.00',
        ], collect($report['summary'])->pluck('value', 'label')->all());

        $this->assertSame(['Purchase expenditure', 'RM 600.00', 'RM 150.00', 'Up 300.0%', 'Received orders, by purchase date'], $report['tables'][0]['rows'][0]);
        $this->assertSame([
            ['Pending', 1, 50.0, 'Awaiting delivery'],
            ['Ordered', 1, 80.0, 'Awaiting delivery'],
            ['Received', 3, 600.0, 'Expenditure'],
            ['Cancelled', 1, 999.0, 'Not counted'],
        ], $report['tables'][1]['rows']);
        $this->assertStringContainsString('Compared with 1–14 Sep 2026', $report['notes'][0]);

        $csv = $this->actingAs($this->admin)->get(route('reports.export', ['report' => 'summary', 'format' => 'csv'] + $period))->streamedContent();
        $this->assertStringContainsString('Operational Summary Report', $csv);
        $this->assertStringContainsString('"Up 300.0%"', $csv);

        $pdf = $this->actingAs($this->admin)->get(route('reports.export', ['report' => 'summary', 'format' => 'pdf'] + $period));
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());

        $staff = User::factory()->create(['role' => 'staff']);
        $this->actingAs($staff)->get(route('reports.show', 'summary'))->assertForbidden();
        $this->actingAs($staff)->get(route('reports.export', ['summary', 'pdf']))->assertForbidden();
    }
}
