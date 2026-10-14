<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

// Module 2A demo data: 12 suppliers with fictional contact details.
// Every demo email uses the reserved ".example" domain, so demo suppliers
// are easy to recognise and never point at a real business inbox.
// Only supplier details are stored - quantities and costs belong to
// purchase orders (PurchaseDemoSeeder).
class SupplierDemoSeeder extends Seeder
{
    // key => [name, phone, email, address, products supplied]
    public const SUPPLIERS = [
        'fresh-chicken' => [
            'Fresh Chicken Supply Sdn. Bhd.', '09-517 2210', 'orders@freshchickensupply.example',
            'Lot 8, Jalan Industri Semambu 2, 25350 Kuantan, Pahang',
            'Fresh whole chicken, chicken wings, chicken breast',
        ],
        'kuantan-poultry' => [
            'Kuantan Poultry Supplier', '09-566 1834', 'sales@kuantanpoultry.example',
            'No. 21, Jalan Gebeng 1/4, 26080 Kuantan, Pahang',
            'Whole chicken, chicken thighs',
        ],
        'pahang-frozen' => [
            'Pahang Frozen Foods', '09-531 7702', 'orders@pahangfrozenfoods.example',
            'Lot 3, Kawasan Perindustrian Indera Mahkota, 25200 Kuantan, Pahang',
            'Frozen chicken, chicken burger patties, frozen french fries',
        ],
        'beverage' => [
            'Malaysia Beverage Distributor', '03-7725 4410', 'sales@mybeveragedistributor.example',
            'No. 9, Jalan Pelabur 23/1, 40300 Shah Alam, Selangor',
            'Soft drinks, mineral water, juices, canned tea',
        ],
        'vegetables' => [
            'Fresh Vegetable Trading', '05-491 2280', 'orders@freshvegetabletrading.example',
            'Lot 15, Kampung Raja, 39010 Cameron Highlands, Pahang',
            'Lettuce, tomatoes, onions',
        ],
        'cooking-oil' => [
            'Golden Cooking Oil Supplier', '03-3341 9050', 'sales@goldencookingoil.example',
            'No. 5, Jalan Kapar Industri 2, 42100 Klang, Selangor',
            'Palm cooking oil',
        ],
        'packaging' => [
            'Restaurant Packaging Solutions', '03-5880 6612', 'hello@restaurantpackaging.example',
            'No. 11, Jalan Utarid U5/14, 40150 Shah Alam, Selangor',
            'Paper cups, food packaging boxes, napkins, plastic containers',
        ],
        'sauces' => [
            'Premium Sauce Supplier', '09-573 4421', 'orders@premiumsauce.example',
            'No. 7, Jalan Tanah Putih 3, 25100 Kuantan, Pahang',
            'Mayonnaise, ketchup, chili sauce',
        ],
        'bakery' => [
            'Daily Bakery Products', '09-513 6680', 'orders@dailybakery.example',
            'No. 30, Jalan Besar, 25000 Kuantan, Pahang',
            'Burger buns, wheat flour, breadcrumbs',
        ],
        'dairy' => [
            'Local Dairy Distributor', '09-538 2219', 'sales@localdairy.example',
            'Lot 19, Jalan Air Putih 6, 25300 Kuantan, Pahang',
            'Cheese slices, fresh milk',
        ],
        'spices' => [
            'Spice Valley Trading', '09-516 9940', 'orders@spicevalley.example',
            'No. 2, Lorong Pasar Besar 4, 25000 Kuantan, Pahang',
            'Salt, black pepper, chili powder, garlic powder',
        ],
        'grocery' => [
            'Kuantan Rice & Egg Wholesale', '09-560 3307', 'orders@kuantanwholesale.example',
            'No. 16, Jalan Sri Kuantan 25, 25300 Kuantan, Pahang',
            'Jasmine rice, grade A eggs',
        ],
    ];

    public function run(): void
    {
        $added = 0;
        $firstAdded = now()->subDays(175)->setTime(10, 0);

        foreach (array_values(self::SUPPLIERS) as $index => [$name, $phone, $email, $address, $products]) {
            // Never touch a supplier that already exists
            if (Supplier::where('name', $name)->orWhere('email', $email)->exists()) {
                continue;
            }

            Supplier::forceCreate([
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'address' => $address,
                'products_supplied' => $products,
                'created_at' => $firstAdded->copy()->addHours($index),
                'updated_at' => $firstAdded->copy()->addHours($index),
            ]);

            $added++;
        }

        $this->command?->line(sprintf('  Suppliers: %d added, %d already existed', $added, count(self::SUPPLIERS) - $added));
    }
}
