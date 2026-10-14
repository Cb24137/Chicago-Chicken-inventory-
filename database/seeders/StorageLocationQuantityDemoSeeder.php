<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StorageLocationQuantityDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasColumn('storage_locations', 'quantity')) {
            throw new \RuntimeException('Storage quantity column is missing. Run php artisan migrate first.');
        }

        // Assign sample quantities only to rows that have no quantity yet.
        $ids = DB::table('storage_locations')->whereNull('quantity')->pluck('id');
        foreach ($ids as $id) {
            DB::table('storage_locations')->where('id', $id)
                ->update(['quantity' => random_int(10, 100)]);
        }
    }
}