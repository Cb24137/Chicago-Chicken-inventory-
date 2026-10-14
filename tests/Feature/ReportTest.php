<?php

namespace Tests\Feature;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WasteExpiryRecord;
use App\Support\Quiet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin One']);
        $this->staff = User::factory()->create(['role' => 'staff', 'name' => 'Staff One']);
    }

    // Summary tiles as label => value
    private function summary(TestResponse $response): array
    {
        return collect($response->viewData('report')['summary'])->pluck('value', 'label')->all();
    }

    private function table(TestResponse $response, int $index = 0): array
    {
        return $response->viewData('report')['tables'][$index]['rows'];
    }

    private function order(Supplier $supplier, string $status, float $total, int $daysAgo = 0): Purchase
    {
        return Quiet::run(fn () => Purchase::create([
            'supplier_id' => $supplier->id,
            'item_name' => "Order {$status} {$total}",
            'quantity' => 1,
            'unit' => 'kg',
            'unit_price' => $total,
            'total_cost' => $total,
            'purchase_date' => now()->subDays($daysAgo)->toDateString(),
            'status' => $status,
        ]));
    }

    private function item(string $name, string $category, float $quantity, float $minimum, float $price = 5, string $unit = 'kg'): InventoryItem
    {
        return Quiet::run(fn () => InventoryItem::create([
            'item_name' => $name, 'category' => $category, 'unit' => $unit,
            'quantity' => $quantity, 'minimum_stock' => $minimum, 'unit_price' => $price,
        ]));
    }

    // ---------- Access ----------

    public function test_staff_can_open_the_operational_reports_only(): void
    {
        $this->actingAs($this->staff)->get(route('reports.index'))->assertRedirect(route('reports.show', 'inventory'));

        foreach (['inventory', 'storage', 'waste'] as $report) {
            $this->actingAs($this->staff)->get(route('reports.show', $report))->assertOk();
            $this->actingAs($this->staff)->get(route('reports.export', [$report, 'csv']))->assertOk();
        }

        foreach (['purchases', 'suppliers', 'summary'] as $report) {
            $this->actingAs($this->staff)->get(route('reports.show', $report))->assertForbidden();
            $this->actingAs($this->staff)->get(route('reports.export', [$report, 'csv']))->assertForbidden();
            $this->actingAs($this->staff)->get(route('reports.export', [$report, 'pdf']))->assertForbidden();
        }

        $response = $this->actingAs($this->staff)->get(route('reports.show', 'inventory'));
        $this->assertSame(['inventory', 'storage', 'waste'], array_keys($response->viewData('reports')));
        $response->assertSee('Purchase, supplier and operational summary reports are available to administrators.');
    }

    public function test_admins_can_open_every_report(): void
    {
        foreach (['inventory', 'purchases', 'suppliers', 'storage', 'waste', 'summary'] as $report) {
            $this->actingAs($this->admin)->get(route('reports.show', $report))
                ->assertOk()
                ->assertSee('Chicago Chicken City');
        }

        $this->actingAs($this->admin)->get('/reports/payroll')->assertNotFound();
        $this->actingAs($this->admin)->get('/reports/inventory/export/xlsx')->assertNotFound();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get(route('reports.show', 'inventory'))->assertRedirect(route('login'));
        $this->get(route('reports.export', ['inventory', 'csv']))->assertRedirect(route('login'));
    }

    // ---------- Figures ----------

    public function test_purchase_report_separates_spent_committed_and_cancelled_orders(): void
    {
        $farm = Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm']));
        $drinks = Quiet::run(fn () => Supplier::create(['name' => 'Drinks Co']));

        $this->order($farm, 'received', 100);
        $this->order($farm, 'pending', 40, 5);
        $this->order($drinks, 'ordered', 60, 2);
        $this->order($drinks, 'cancelled', 999, 1);
        $this->order($farm, 'received', 500, 400);      // outside the period

        $period = ['from' => now()->subDays(10)->toDateString(), 'to' => now()->toDateString()];
        $response = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'purchases'] + $period))->assertOk();

        $this->assertSame([
            'Purchase orders' => '4',
            'Spent' => 'RM 100.00',
            'Committed' => 'RM 100.00',
            'Cancelled orders' => '1',
        ], $this->summary($response));

        // Purchases by supplier: [supplier, orders, received, spent, committed]
        $this->assertSame([
            ['Fresh Farm', 2, 1, 100.0, 40.0],
            ['Drinks Co', 1, 0, 0.0, 60.0],
        ], $this->table($response));

        $this->assertCount(4, $this->table($response, 2), 'All orders in the period are listed, cancelled included');

        // Supplier and status filters
        $bySupplier = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'purchases', 'supplier_id' => $drinks->id] + $period));
        $this->assertSame('2', $this->summary($bySupplier)['Purchase orders']);
        $this->assertSame('RM 60.00', $this->summary($bySupplier)['Committed']);
        $this->assertSame(['Supplier' => 'Drinks Co'], $bySupplier->viewData('filterSummary'));

        $byStatus = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'purchases', 'status' => 'received'] + $period));
        $this->assertSame(['Order received 100'], array_column($this->table($byStatus, 2), 3));
    }

    public function test_supplier_report_counts_orders_and_spending_in_the_period(): void
    {
        $farm = Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm', 'phone' => '012-345 6789']));
        Quiet::run(fn () => Supplier::create(['name' => 'Unused Supplier']));

        $this->order($farm, 'received', 80, 3);
        $this->order($farm, 'pending', 20, 1);
        $this->order($farm, 'cancelled', 300, 1);

        $response = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'suppliers', 'from' => now()->subMonth()->toDateString(), 'to' => now()->toDateString()]));

        $this->assertSame('2', $this->summary($response)['Suppliers']);
        $this->assertSame('1', $this->summary($response)['Ordered from']);
        $this->assertSame('2', $this->summary($response)['Purchase orders']);
        $this->assertSame('RM 80.00', $this->summary($response)['Spent']);

        $farmRow = $this->table($response)[0];
        $this->assertSame(['Fresh Farm', '012-345 6789'], array_slice($farmRow, 0, 2));
        $this->assertSame([2, 80.0], [$farmRow[4], $farmRow[5]]);
        $this->assertSame(now()->subDays(1)->toDateString(), $farmRow[6]->toDateString());
    }

    public function test_inventory_report_filters_by_category_and_stock_status(): void
    {
        $this->item('Chicken', 'Food', 2, 5, 10);           // low
        $this->item('Cola', 'Beverage', 50, 10, 2);          // in stock
        $this->item('Paper Cups', 'Packaging', 0, 100, 0.04, 'pcs');   // out of stock

        $all = $this->actingAs($this->staff)->get(route('reports.show', 'inventory'))->assertOk();
        $this->assertSame([
            'Inventory items' => '3',
            'Stock value' => 'RM 120.00',
            'Low stock' => '1',
            'Out of stock' => '1',
        ], $this->summary($all));
        $this->assertSame(['Low stock', 'In stock', 'Out of stock'], array_column($this->table($all), 7));

        $food = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'inventory', 'category' => 'Food']));
        $this->assertSame(['Chicken'], array_column($this->table($food), 0));
        $this->assertSame(['Category' => 'Food'], $food->viewData('filterSummary'));

        $restock = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'inventory', 'stock_status' => 'restock']));
        $this->assertSame(['Chicken', 'Paper Cups'], array_column($this->table($restock), 0));

        $out = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'inventory', 'stock_status' => 'out']));
        $this->assertSame(['Paper Cups'], array_column($this->table($out), 0));
    }

    public function test_storage_report_filters_by_type_and_status(): void
    {
        Quiet::run(function () {
            StorageLocation::forceCreate(['name' => 'Walk-in Freezer', 'type' => 'Freezer', 'is_active' => true, 'quantity' => 40]);
            StorageLocation::forceCreate(['name' => 'Old Shelf', 'type' => 'Dry Storage', 'is_active' => false, 'quantity' => 5]);
        });

        $all = $this->actingAs($this->staff)->get(route('reports.show', 'storage'))->assertOk();
        $this->assertSame(['Storage locations' => '2', 'Active' => '1', 'Inactive' => '1', 'Recorded quantity' => '45'], $this->summary($all));

        $inactive = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'storage', 'location_status' => 'inactive']));
        $this->assertSame(['Old Shelf'], array_column($this->table($inactive), 0));

        $freezers = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'storage', 'type' => 'Freezer']));
        $this->assertSame(['Walk-in Freezer'], array_column($this->table($freezers), 0));
    }

    public function test_waste_report_estimates_cost_from_matching_inventory_prices(): void
    {
        $this->item('Fresh Milk', 'Beverage', 10, 2, 7.20, 'litre');
        $this->item('Chicken', 'Food', 10, 2, 9.50);

        Quiet::run(function () {
            $record = fn (string $name, float $quantity, string $unit, string $reason, int $daysAgo = 0) => WasteExpiryRecord::create([
                'item_name' => $name, 'quantity' => $quantity, 'unit' => $unit,
                'reason' => $reason, 'record_date' => now()->subDays($daysAgo)->toDateString(),
            ]);

            $record('Fresh Milk', 2, 'litre', 'expired');        // 2 x RM 7.20 = RM 14.40
            $record('chicken', 500, 'g', 'spoiled', 1);          // 0.5 kg x RM 9.50 = RM 4.75
            $record('Mystery Sauce', 3, 'pcs', 'damaged', 2);    // no matching item: not priced
            $record('Fresh Milk', 1, 'litre', 'expired', 200);   // outside the period
        });

        $period = ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()];
        $response = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'waste'] + $period))->assertOk();
        $summary = collect($response->viewData('report')['summary'])->keyBy('label');

        $this->assertSame('3', $summary['Waste records']['value']);
        $this->assertSame('1', $summary['Expired products']['value']);
        $this->assertSame('2', $summary['Damaged or spoiled']['value']);
        $this->assertSame('RM 19.15', $summary['Estimated cost']['value']);
        $this->assertSame('Priced 2 of 3 records', $summary['Estimated cost']['meta']);
        $this->assertSame([14.4, 4.75, null], array_column($this->table($response, 1), 6));

        $expired = $this->actingAs($this->staff)->get(route('reports.show', ['report' => 'waste', 'reason' => 'expired'] + $period));
        $this->assertSame(['Fresh Milk'], array_column($this->table($expired, 1), 1));
    }

    // ---------- Validation ----------

    public function test_invalid_dates_and_filters_are_rejected(): void
    {
        $invalid = [
            ['from' => 'yesterday'],
            ['from' => '2026-02-31'],
            ['from' => '2026-10-10', 'to' => '2026-10-01'],
            ['category' => 'Furniture'],
            ['stock_status' => 'everything'],
        ];

        foreach ($invalid as $query) {
            $this->actingAs($this->admin)
                ->from(route('reports.show', 'purchases'))
                ->get(route('reports.show', ['report' => 'inventory'] + $query))
                ->assertRedirect(route('reports.show', 'purchases'))
                ->assertSessionHasErrors();
        }

        $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'purchases', 'supplier_id' => 999]))
            ->assertSessionHasErrors('supplier_id');
        $this->actingAs($this->admin)->get(route('reports.export', ['report' => 'waste', 'format' => 'csv', 'reason' => 'stolen']))
            ->assertSessionHasErrors('reason');
    }

    public function test_filters_only_apply_to_their_own_report(): void
    {
        $farm = Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm']));
        $this->order($farm, 'received', 100);

        // "category" belongs to the inventory report, so the purchase report ignores it
        $response = $this->actingAs($this->admin)->get(route('reports.show', ['report' => 'purchases', 'category' => 'Food']))->assertOk();

        $this->assertSame([], $response->viewData('filterSummary'));
        $this->assertArrayNotHasKey('category', $response->viewData('filters')->query());
        $this->assertSame('1', $this->summary($response)['Purchase orders']);
    }

    // ---------- Page and exports ----------

    public function test_report_page_shows_the_header_presets_and_export_links(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('reports.show', ['report' => 'purchases', 'from' => '2026-01-01', 'to' => '2026-03-31']))
            ->assertOk();

        $response->assertSee('Purchase Report')
            ->assertSee('01 Jan 2026 to 31 Mar 2026')
            ->assertSee('Prepared by')
            ->assertSee('Admin One')
            ->assertSee('This month')
            ->assertSee('Last 3 months')
            ->assertSee('data-print', false)
            ->assertSee(route('reports.export', ['report' => 'purchases', 'format' => 'csv', 'from' => '2026-01-01', 'to' => '2026-03-31']))
            ->assertSee(route('reports.export', ['report' => 'purchases', 'format' => 'pdf', 'from' => '2026-01-01', 'to' => '2026-03-31']));

        // Reports that show today's stock do not carry a date range
        $this->actingAs($this->admin)->get(route('reports.show', 'inventory'))
            ->assertSee(route('reports.export', ['report' => 'inventory', 'format' => 'csv']))
            ->assertDontSee('Quick ranges');
    }

    public function test_csv_export_includes_the_report_details_and_escapes_formulas(): void
    {
        Quiet::run(fn () => Supplier::create(['name' => '=HYPERLINK("http://evil.example","Click")', 'phone' => '+60 12-345 6789']));
        Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm']));

        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', ['report' => 'suppliers', 'format' => 'csv', 'from' => '2026-01-01', 'to' => '2026-12-31']))
            ->assertOk()
            ->assertDownload('chicago-chicken-city-suppliers-report-' . now()->format('Y-m-d') . '.csv');

        $this->assertStringStartsWith('text/csv', $response->headers->get('Content-Type'));

        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv, 'Starts with a byte order mark for Excel');
        $this->assertStringContainsString('Chicago Chicken City', $csv);
        $this->assertStringContainsString('Supplier Report', $csv);
        $this->assertStringContainsString('Period,"01 Jan 2026 to 31 Dec 2026"', $csv);
        $this->assertStringContainsString('by Admin One', $csv);
        $this->assertStringContainsString('Supplier,Phone,Email', $csv);

        // Values that look like formulas are made safe for spreadsheet apps
        $this->assertStringContainsString("'=HYPERLINK", $csv);
        $this->assertStringContainsString("'+60 12-345 6789", $csv);
        $this->assertDoesNotMatchRegularExpression('/(^|,)"?[=+]/m', $csv);

        $filtered = $this->actingAs($this->staff)
            ->get(route('reports.export', ['report' => 'inventory', 'format' => 'csv', 'category' => 'Food']))
            ->streamedContent();
        $this->assertStringContainsString('Filters,"Category: Food"', $filtered);
    }

    public function test_pdf_export_downloads_a_real_pdf(): void
    {
        $farm = Quiet::run(fn () => Supplier::create(['name' => 'Fresh Farm']));
        $this->order($farm, 'received', 100);
        $this->item('Chicken', 'Food', 2, 5);

        $response = $this->actingAs($this->admin)
            ->get(route('reports.export', ['report' => 'purchases', 'format' => 'pdf']))
            ->assertOk()
            ->assertDownload('chicago-chicken-city-purchases-report-' . now()->format('Y-m-d') . '.pdf');

        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringStartsWith('%PDF-', $response->getContent());

        $this->actingAs($this->staff)
            ->get(route('reports.export', ['report' => 'inventory', 'format' => 'pdf']))
            ->assertOk()
            ->assertDownload();
    }
}
