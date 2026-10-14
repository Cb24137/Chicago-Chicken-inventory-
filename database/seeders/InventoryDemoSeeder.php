<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use Illuminate\Database\Seeder;

// Inventory demo data: 36 stock items for a fried chicken restaurant
// (food, beverages, packaging and other supplies). Most are well stocked,
// six are low and three are out of stock, so the dashboard's low-stock
// alerts have something to show. Stock status is not stored - the app
// works it out from quantity and minimum stock.
class InventoryDemoSeeder extends Seeder
{
    // [item name, category, unit, quantity in stock, minimum stock, unit price (RM)]
    public const ITEMS = [
        // Chicken
        ['Fresh Chicken', 'Food', 'kg', 48, 25, 9.50],
        ['Frozen Chicken', 'Food', 'kg', 12, 30, 8.20],          // low stock
        ['Chicken Wings', 'Food', 'kg', 35, 15, 14.50],
        ['Chicken Breast', 'Food', 'kg', 22, 15, 13.00],
        ['Chicken Thighs', 'Food', 'kg', 9, 12, 11.50],          // low stock

        // Burgers, sides and staples
        ['Burger Buns', 'Food', 'pcs', 240, 100, 0.55],
        ['Burger Patties', 'Food', 'pcs', 0, 80, 1.20],          // out of stock
        ['French Fries', 'Food', 'kg', 60, 25, 7.80],
        ['Rice', 'Food', 'kg', 50, 20, 3.40],
        ['Eggs', 'Food', 'pcs', 180, 90, 0.45],

        // Frying and coating
        ['Cooking Oil', 'Food', 'litre', 18, 40, 6.80],          // low stock
        ['Flour', 'Food', 'kg', 45, 20, 3.20],
        ['Breadcrumbs', 'Food', 'kg', 14, 10, 6.50],

        // Seasoning
        ['Salt', 'Food', 'kg', 20, 5, 1.80],
        ['Black Pepper', 'Food', 'kg', 3, 2, 45.00],
        ['Chili Powder', 'Food', 'kg', 4, 2, 22.00],
        ['Garlic Powder', 'Food', 'kg', 0, 1.5, 28.00],          // out of stock

        // Sauces
        ['Mayonnaise', 'Food', 'kg', 16, 8, 12.50],
        ['Ketchup', 'Food', 'kg', 20, 8, 7.50],
        ['Chili Sauce', 'Food', 'kg', 10, 8, 6.80],

        // Dairy and vegetables
        ['Cheese Slices', 'Food', 'pcs', 300, 120, 0.45],
        ['Lettuce', 'Food', 'kg', 3, 5, 8.00],                   // low stock
        ['Tomatoes', 'Food', 'kg', 10, 6, 5.50],
        ['Onions', 'Food', 'kg', 25, 10, 4.20],

        // Beverages
        ['Coca-Cola', 'Beverage', 'pcs', 144, 72, 1.65],
        ['Sprite', 'Beverage', 'pcs', 36, 72, 1.65],             // low stock
        ['Mineral Water', 'Beverage', 'pcs', 0, 48, 0.85],       // out of stock
        ['Orange Juice', 'Beverage', 'litre', 24, 10, 7.90],
        ['Fresh Milk', 'Beverage', 'litre', 12, 6, 7.20],
        ['Iced Lemon Tea', 'Beverage', 'pcs', 96, 48, 1.50],

        // Packaging (unit prices match the packaging supplier's demo orders,
        // e.g. RM 38.00 per box of 1,000 cups = RM 0.04 per cup)
        ['Paper Cups (16 oz)', 'Packaging', 'pcs', 1500, 500, 0.04],
        ['Food Packaging Boxes', 'Packaging', 'pcs', 800, 300, 0.11],
        ['Napkins (pack of 100)', 'Packaging', 'pcs', 60, 20, 0.37],
        ['Plastic Containers (750 ml)', 'Packaging', 'pcs', 120, 150, 0.14], // low stock

        // Other supplies
        ['Dishwashing Liquid', 'Other', 'litre', 8, 5, 9.50],
        ['Disposable Gloves (box of 100)', 'Other', 'pcs', 6, 4, 8.90],
    ];

    public function run(): void
    {
        $added = 0;
        $firstAdded = now()->subDays(170)->setTime(9, 0);

        foreach (self::ITEMS as $index => [$name, $category, $unit, $quantity, $minimum, $price]) {
            // Never touch an item that already exists (demo or user-created)
            if (InventoryItem::where('item_name', $name)->exists()) {
                continue;
            }

            InventoryItem::forceCreate([
                'item_name' => $name,
                'category' => $category,
                'unit' => $unit,
                'quantity' => $quantity,
                'minimum_stock' => $minimum,
                'unit_price' => $price,
                'created_at' => $firstAdded->copy()->addMinutes($index * 5),
                'updated_at' => now()->subDays($index % 7)->setTime(17, 0),
            ]);

            $added++;
        }

        $this->command?->line(sprintf('  Inventory items: %d added, %d already existed', $added, count(self::ITEMS) - $added));
    }
}
