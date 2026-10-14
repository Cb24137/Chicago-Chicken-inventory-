<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

// Module 2B demo data: 49 purchase orders from the demo suppliers, spread
// over the last five months so the spending chart has data every month
// (41 received, 3 ordered, 3 pending, 2 cancelled).
//
// Seeding never changes inventory quantities. Received orders are past
// deliveries that are already part of today's stock levels, so they are
// marked as stocked - editing one can never add the same stock again.
// The pending and ordered orders can be received live during a demo with
// the normal "Receive" button, which adds their stock exactly once.
class PurchaseDemoSeeder extends Seeder
{
    // [days ago, supplier key, product, inventory item to link (or null), quantity, unit, unit price (RM), status, notes]
    public const ORDERS = [
        [160, 'fresh-chicken', 'Fresh whole chicken', 'Fresh Chicken', 60, 'kg', 9.20, 'received', 'Weekly fresh chicken delivery.'],
        [158, 'pahang-frozen', 'Frozen chicken (2 kg packs)', 'Frozen Chicken', 80, 'kg', 7.90, 'received', null],
        [155, 'beverage', 'Coca-Cola 320 ml cans', 'Coca-Cola', 240, 'pieces', 1.55, 'received', null],
        [152, 'bakery', 'Sesame burger buns', 'Burger Buns', 400, 'pieces', 0.52, 'received', null],
        [150, 'packaging', 'Paper cups 16 oz (1,000 per box)', null, 10, 'boxes', 38.00, 'received', null],
        [146, 'vegetables', 'Iceberg lettuce', 'Lettuce', 15, 'kg', 7.50, 'received', null],
        [143, 'cooking-oil', 'Palm cooking oil', 'Cooking Oil', 60, 'litres', 6.50, 'received', null],
        [139, 'fresh-chicken', 'Chicken wings', 'Chicken Wings', 40, 'kg', 14.00, 'received', null],
        [136, 'sauces', 'Mayonnaise (1 kg tubs)', 'Mayonnaise', 20, 'kg', 12.00, 'received', null],
        [131, 'kuantan-poultry', 'Chicken thighs', 'Chicken Thighs', 30, 'kg', 11.20, 'received', null],
        [128, 'pahang-frozen', 'Frozen french fries', 'French Fries', 50, 'kg', 7.50, 'received', null],
        [120, 'beverage', 'Mineral water 500 ml', 'Mineral Water', 480, 'pieces', 0.80, 'received', null],
        [117, 'fresh-chicken', 'Chicken breast fillet', 'Chicken Breast', 35, 'kg', 12.80, 'received', null],
        [113, 'bakery', 'Wheat flour', 'Flour', 50, 'kg', 3.10, 'received', null],
        [110, 'dairy', 'Cheddar cheese slices', 'Cheese Slices', 480, 'pieces', 0.42, 'received', null],
        [106, 'vegetables', 'Tomatoes', 'Tomatoes', 20, 'kg', 5.20, 'received', null],
        [103, 'packaging', 'Food packaging boxes (500 per box)', null, 8, 'boxes', 55.00, 'received', null],
        [99, 'fresh-chicken', 'Fresh whole chicken', 'Fresh Chicken', 70, 'kg', 9.40, 'received', 'Extra stock for the school holidays.'],
        [96, 'pahang-frozen', 'Chicken burger patties', 'Burger Patties', 300, 'pieces', 1.15, 'received', null],
        [92, 'beverage', 'Sprite 320 ml cans', 'Sprite', 240, 'pieces', 1.55, 'cancelled', 'Cancelled - supplier out of stock.'],
        [89, 'cooking-oil', 'Palm cooking oil', 'Cooking Oil', 80, 'litres', 6.60, 'received', null],
        [85, 'sauces', 'Tomato ketchup', 'Ketchup', 24, 'kg', 7.20, 'received', null],
        [82, 'sauces', 'Chili sauce', 'Chili Sauce', 24, 'kg', 6.50, 'received', null],
        [78, 'kuantan-poultry', 'Fresh whole chicken', 'Fresh Chicken', 50, 'kg', 9.30, 'received', 'Back-up supplier while the main supplier was fully booked.'],
        [75, 'vegetables', 'Yellow onions', 'Onions', 30, 'kg', 4.00, 'received', null],
        [71, 'grocery', 'Grade A eggs', 'Eggs', 360, 'pieces', 0.42, 'received', null],
        [64, 'fresh-chicken', 'Chicken wings', 'Chicken Wings', 45, 'kg', 14.20, 'received', null],
        [61, 'beverage', 'Orange juice', 'Orange Juice', 30, 'litres', 7.50, 'received', null],
        [57, 'packaging', 'Napkins (5,000 per box)', null, 12, 'boxes', 18.50, 'received', null],
        [54, 'dairy', 'Fresh milk', 'Fresh Milk', 20, 'litres', 6.90, 'received', null],
        [50, 'pahang-frozen', 'Frozen chicken (2 kg packs)', 'Frozen Chicken', 100, 'kg', 8.00, 'received', null],
        [47, 'spices', 'Chili powder', 'Chili Powder', 5, 'kg', 21.00, 'received', null],
        [43, 'grocery', 'Jasmine rice', 'Rice', 100, 'kg', 3.30, 'received', null],
        [40, 'fresh-chicken', 'Fresh whole chicken', 'Fresh Chicken', 65, 'kg', 9.50, 'received', null],
        [36, 'beverage', 'Iced lemon tea cans', 'Iced Lemon Tea', 240, 'pieces', 1.40, 'received', null],
        [33, 'packaging', 'Plastic containers 750 ml (300 per box)', null, 6, 'boxes', 42.00, 'cancelled', 'Cancelled - wrong container size quoted.'],
        [29, 'spices', 'Garlic powder', 'Garlic Powder', 3, 'kg', 27.00, 'received', null],
        [26, 'sauces', 'Mayonnaise (1 kg tubs)', 'Mayonnaise', 16, 'kg', 12.30, 'received', null],
        [22, 'kuantan-poultry', 'Chicken thighs', 'Chicken Thighs', 25, 'kg', 11.40, 'received', null],
        [19, 'bakery', 'Sesame burger buns', 'Burger Buns', 500, 'pieces', 0.55, 'received', null],
        [15, 'cooking-oil', 'Palm cooking oil', 'Cooking Oil', 40, 'litres', 6.80, 'received', null],
        [12, 'fresh-chicken', 'Chicken breast fillet', 'Chicken Breast', 30, 'kg', 13.00, 'received', null],
        [9, 'beverage', 'Coca-Cola 320 ml cans', 'Coca-Cola', 288, 'pieces', 1.60, 'received', null],
        [6, 'pahang-frozen', 'Frozen chicken (2 kg packs)', 'Frozen Chicken', 90, 'kg', 8.20, 'ordered', 'Restock - below minimum stock level.'],
        [5, 'vegetables', 'Iceberg lettuce', 'Lettuce', 12, 'kg', 8.00, 'ordered', null],
        [3, 'cooking-oil', 'Palm cooking oil', 'Cooking Oil', 60, 'litres', 6.80, 'ordered', 'Deliver to the back entrance before 10am.'],
        [2, 'pahang-frozen', 'Chicken burger patties', 'Burger Patties', 400, 'pieces', 1.20, 'pending', 'Urgent - out of stock.'],
        [1, 'beverage', 'Mineral water 500 ml', 'Mineral Water', 480, 'pieces', 0.85, 'pending', 'Urgent - out of stock.'],
        [0, 'spices', 'Garlic powder', 'Garlic Powder', 3, 'kg', 28.00, 'pending', null],
    ];

    public function run(): void
    {
        $supplierIds = [];

        foreach (SupplierDemoSeeder::SUPPLIERS as $key => [$name]) {
            $supplierIds[$key] = Supplier::where('name', $name)->value('id');
        }

        if (in_array(null, $supplierIds, true)) {
            $this->command?->warn('  Purchase orders: skipped - run SupplierDemoSeeder first.');

            return;
        }

        // The demo orders are added once, as a complete set
        if (Purchase::whereIn('supplier_id', $supplierIds)->exists()) {
            $this->command?->line('  Purchase orders: 0 added, the demo orders already exist');

            return;
        }

        $items = InventoryItem::whereIn('item_name', array_filter(array_column(self::ORDERS, 3)))
            ->get()
            ->keyBy('item_name');

        foreach (self::ORDERS as $index => [$daysAgo, $supplier, $product, $itemName, $quantity, $unit, $price, $status, $notes]) {
            $orderedAt = now()->subDays($daysAgo)->setTime(9, 0)->addMinutes($index * 3);
            $orderedAt = $orderedAt->isFuture() ? now() : $orderedAt;

            $item = $itemName ? $items->get($itemName) : null;

            // Only link an item that can receive this order's unit
            if ($item && Purchase::convertToInventoryUnit(1, $unit, $item->unit) === null) {
                $item = null;
            }

            $order = [
                'supplier_id' => $supplierIds[$supplier],
                'inventory_item_id' => $item?->id,
                'item_name' => $product,
                'quantity' => $quantity,
                'unit' => $unit,
                'unit_price' => $price,
                'total_cost' => Purchase::calculateTotal($quantity, $price),
                'purchase_date' => $orderedAt->toDateString(),
                'status' => $status,
                'notes' => $notes,
                'created_at' => $orderedAt,
                'updated_at' => $orderedAt,
            ];

            if ($status === 'received') {
                $receivedAt = $orderedAt->copy()->addDay()->setTime(11, 30);
                $receivedAt = $receivedAt->isFuture() ? now() : $receivedAt;

                $order['received_at'] = $receivedAt;
                $order['updated_at'] = $receivedAt;

                if ($item) {
                    $order['stock_added_at'] = $receivedAt;
                    $order['stock_added_quantity'] = Purchase::convertToInventoryUnit($quantity, $unit, $item->unit);
                }
            }

            // The Purchase model generates the PO number (e.g. PO-2026-00012)
            Purchase::forceCreate($order);
        }

        $statuses = collect(self::ORDERS)->countBy(fn ($order) => $order[7]);

        $this->command?->line(sprintf(
            '  Purchase orders: %d added (%d received, %d ordered, %d pending, %d cancelled)',
            count(self::ORDERS),
            $statuses['received'] ?? 0,
            $statuses['ordered'] ?? 0,
            $statuses['pending'] ?? 0,
            $statuses['cancelled'] ?? 0
        ));
    }
}
