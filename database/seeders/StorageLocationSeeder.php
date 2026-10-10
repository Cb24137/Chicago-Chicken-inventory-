<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StorageLocation;

class StorageLocationSeeder extends Seeder
{
    public function run(): void
    {
        $locations = [
            [
                'name' => 'Freezer A',
                'type' => 'Freezer',
                'description' => 'Frozen chicken and meat',
                'is_active' => true,
            ],
            [
                'name' => 'Freezer B',
                'type' => 'Freezer',
                'description' => 'Backup frozen food storage',
                'is_active' => true,
            ],
            [
                'name' => 'Freezer C',
                'type' => 'Freezer',
                'description' => 'Frozen seafood and ingredients',
                'is_active' => true,
            ],
            [
                'name' => 'Chiller A',
                'type' => 'Refrigerator',
                'description' => 'Fresh vegetables and dairy',
                'is_active' => true,
            ],
            [
                'name' => 'Chiller B',
                'type' => 'Refrigerator',
                'description' => 'Chilled food ingredients',
                'is_active' => true,
            ],
            [
                'name' => 'Beverage Chiller',
                'type' => 'Refrigerator',
                'description' => 'Cold drinks and beverages',
                'is_active' => true,
            ],
            [
                'name' => 'Dry Storage A',
                'type' => 'Dry Storage',
                'description' => 'Rice, flour and dry ingredients',
                'is_active' => true,
            ],
            [
                'name' => 'Dry Storage B',
                'type' => 'Dry Storage',
                'description' => 'Spices and dry food supplies',
                'is_active' => true,
            ],
            [
                'name' => 'Packaging Storage',
                'type' => 'Dry Storage',
                'description' => 'Food packaging and containers',
                'is_active' => true,
            ],
            [
                'name' => 'Sauce Storage',
                'type' => 'Dry Storage',
                'description' => 'Shelf-stable sauces and condiments',
                'is_active' => true,
            ],
        ];

        foreach ($locations as $location) {
            StorageLocation::firstOrCreate(
                ['name' => $location['name']],
                [
                    'type' => $location['type'],
                    'description' => $location['description'],
                    'is_active' => $location['is_active'],
                ]
            );
        }
    }
}