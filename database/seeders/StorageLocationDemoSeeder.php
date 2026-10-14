<?php

namespace Database\Seeders;

use App\Models\StorageLocation;
use Illuminate\Database\Seeder;

// Module 3 demo data: 12 storage locations (10 active, 2 inactive).
// Names are different from StorageLocationSeeder, so both can be run
// without creating near-duplicate locations. Storage locations are not
// linked to inventory items in this app, so no links are created.
class StorageLocationDemoSeeder extends Seeder
{
    // [name, type, description, active, items stored]
    public const LOCATIONS = [
        ['Walk-in Freezer', 'Freezer', 'Main walk-in freezer at -18°C for frozen chicken, patties and fries.', true, 140],
        ['Frozen Chicken Freezer', 'Freezer', 'Chest freezer reserved for marinated and frozen chicken portions.', true, 85],
        ['Meat Refrigerator', 'Refrigerator', 'Chiller at 0-4°C for fresh chicken delivered daily.', true, 60],
        ['Vegetable Refrigerator', 'Refrigerator', 'Chiller for lettuce, tomatoes and onions.', true, 35],
        ['Dairy Chiller', 'Refrigerator', 'Cheese slices, fresh milk and opened sauces.', true, 42],
        ['Front Counter Drinks Fridge', 'Refrigerator', 'Glass-door fridge for canned drinks and bottled water.', true, 120],
        ['Cold Storage Room', 'Refrigerator', 'Walk-in chiller for bulk deliveries before they are portioned.', true, 75],
        ['Dry Goods Store Room', 'Dry Storage', 'Shelving for rice, flour and breadcrumbs.', true, 90],
        ['Kitchen Ingredient Shelf', 'Dry Storage', 'Spices and seasonings used during daily preparation.', true, 26],
        ['Takeaway Packaging Cabinet', 'Dry Storage', 'Paper cups, food boxes and napkins for the takeaway counter.', true, 180],
        ['Overflow Chest Freezer', 'Freezer', 'Standby freezer used during festive seasons; currently switched off.', false, 0],
        ['Old Display Chiller', 'Refrigerator', 'Out of service while waiting for a compressor repair.', false, 0],
    ];

    public function run(): void
    {
        $added = 0;
        $firstAdded = now()->subDays(172)->setTime(11, 0);

        foreach (self::LOCATIONS as $index => [$name, $type, $description, $active, $itemsStored]) {
            // Location names are unique; never touch an existing one
            if (StorageLocation::where('name', $name)->exists()) {
                continue;
            }

            StorageLocation::forceCreate([
                'name' => $name,
                'type' => $type,
                'description' => $description,
                'is_active' => $active,
                'quantity' => $itemsStored,
                'created_at' => $firstAdded->copy()->addMinutes($index * 10),
                'updated_at' => $firstAdded->copy()->addMinutes($index * 10),
            ]);

            $added++;
        }

        $this->command?->line(sprintf('  Storage locations: %d added, %d already existed', $added, count(self::LOCATIONS) - $added));
    }
}
