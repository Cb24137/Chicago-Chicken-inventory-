<?php

namespace Database\Seeders;

use App\Models\WasteExpiryRecord;
use Illuminate\Database\Seeder;

// Module 4 demo data: 19 waste and expiry records over the last five
// months. Waste records store the item name as text (they are not linked
// to inventory items), so seeding never deducts stock from inventory.
class WasteExpiryDemoSeeder extends Seeder
{
    // [days ago, item, quantity, unit, reason, days past expiry when found (null = no expiry date), description]
    public const RECORDS = [
        [150, 'Fresh Chicken', 3.5, 'kg', 'spoiled', null, 'Left outside the chiller during delivery; temperature rose above 5°C.'],
        [140, 'Tomatoes', 2, 'kg', 'spoiled', null, 'Overripe and mouldy tomatoes removed during the morning check.'],
        [131, 'Fresh Milk', 2, 'litre', 'expired', 2, 'Found past its expiry date during stock rotation.'],
        [120, 'Burger Buns', 24, 'pcs', 'damaged', null, 'Packaging crushed during delivery.'],
        [110, 'Chili Sauce', 2, 'bottle', 'expired', 3, 'Opened bottles kept past their use-by date.'],
        [98, 'Lettuce', 1.5, 'kg', 'spoiled', null, 'Wilted leaves found in the vegetable refrigerator.'],
        [90, 'Coca-Cola', 12, 'pcs', 'damaged', null, 'Dented and leaking cans after a carton fell from the shelf.'],
        [80, 'French Fries', 4, 'kg', 'damaged', null, 'Torn freezer bags; the fries were freezer-burnt.'],
        [70, 'Cheese Slices', 40, 'pcs', 'expired', 1, 'Expired packs found at the back of the dairy chiller.'],
        [62, 'Breaded chicken trimmings', 3, 'kg', 'other', null, 'Cooking preparation waste from trimming and breading.'],
        [55, 'Chicken Wings', 2, 'kg', 'spoiled', null, 'Thawed too early and not used within 24 hours.'],
        [45, 'Orange Juice', 3, 'litre', 'expired', 2, 'Cartons past their expiry date.'],
        [38, 'Mayonnaise', 1, 'kg', 'spoiled', null, 'Tub left unsealed overnight (improper storage).'],
        [30, 'Fried Chicken', 5, 'kg', 'other', null, 'Unsold fried chicken discarded at closing after its holding time.'],
        [24, 'Onions', 2.5, 'kg', 'spoiled', null, 'Soft and sprouting onions removed from dry storage.'],
        [18, 'Burger Patties', 20, 'pcs', 'damaged', null, 'Freezer door left open overnight; patties partly thawed.'],
        [12, 'Mineral Water', 6, 'pcs', 'damaged', null, 'Cracked bottles from a damaged carton.'],
        [7, 'Fresh Chicken', 2, 'kg', 'expired', 1, 'Past its use-by date during the first-in, first-out stock check.'],
        [3, 'Lettuce', 1, 'kg', 'spoiled', null, 'Brown leaves removed when inspecting the new delivery.'],
    ];

    public function run(): void
    {
        $added = 0;

        foreach (self::RECORDS as [$daysAgo, $item, $quantity, $unit, $reason, $daysExpired, $description]) {
            // Item + description identify each demo record, so nothing is added twice
            if (WasteExpiryRecord::where('item_name', $item)->where('description', $description)->exists()) {
                continue;
            }

            $recordedAt = now()->subDays($daysAgo)->setTime(17, 30);

            WasteExpiryRecord::forceCreate([
                'item_name' => $item,
                'quantity' => $quantity,
                'unit' => $unit,
                'reason' => $reason,
                'expiry_date' => $daysExpired === null ? null : $recordedAt->copy()->subDays($daysExpired)->toDateString(),
                'record_date' => $recordedAt->toDateString(),
                'description' => $description,
                'created_at' => $recordedAt,
                'updated_at' => $recordedAt,
            ]);

            $added++;
        }

        $this->command?->line(sprintf('  Waste & expiry records: %d added, %d already existed', $added, count(self::RECORDS) - $added));
    }
}
