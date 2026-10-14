<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Adds the Packaging and Other categories (existing Food and Beverage
    // items are unchanged) and remembers which stock alert was last sent,
    // so the same low-stock condition is never notified twice.
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('category', ['Food', 'Beverage', 'Packaging', 'Other'])->change();
            $table->string('stock_alert_level', 10)->nullable()->after('unit_price');
        });
    }

    public function down(): void
    {
        if (DB::table('inventory_items')->whereIn('category', ['Packaging', 'Other'])->exists()) {
            throw new RuntimeException('Some items use the Packaging or Other category. Change them before rolling back.');
        }

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->enum('category', ['Food', 'Beverage'])->change();
            $table->dropColumn('stock_alert_level');
        });
    }
};
