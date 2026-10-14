<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\StorageLocation;
use App\Models\Supplier;
use App\Models\WasteExpiryRecord;
use App\Support\Quiet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

// Adds realistic demonstration data to all four modules:
//
//   php artisan db:seed --class=ChicagoChickenDemoSeeder
//
// Safe to run more than once: records that already exist are skipped,
// and existing records (including user accounts) are never changed or
// deleted. Runs only when APP_ENV is "local" or "testing".
class ChicagoChickenDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('Demo data can only be added when APP_ENV is "local" or "testing".');

            return;
        }

        $connection = DB::connection();
        $this->command?->info("Adding demo data to \"{$connection->getDatabaseName()}\" ({$connection->getDriverName()})");

        $before = $this->counts();

        // Loading demo data is not a staff action, so it creates no activity
        // entries or notifications (current low stock is still picked up by
        // the next daily alert check). One transaction: all or nothing.
        Quiet::run(fn () => DB::transaction(fn () => $this->call([
            InventoryDemoSeeder::class,
            SupplierDemoSeeder::class,
            StorageLocationDemoSeeder::class,
            PurchaseDemoSeeder::class,
            WasteExpiryDemoSeeder::class,
        ])));

        $after = $this->counts();

        $this->command?->table(
            ['Records', 'Before', 'After', 'Added'],
            collect($before)->map(fn ($count, $label) => [$label, $count, $after[$label], $after[$label] - $count])->values()->all()
        );
    }

    private function counts(): array
    {
        return [
            'Inventory items' => InventoryItem::count(),
            'Suppliers' => Supplier::count(),
            'Purchase orders' => Purchase::count(),
            'Storage locations' => StorageLocation::count(),
            'Waste & expiry records' => WasteExpiryRecord::count(),
        ];
    }
}
