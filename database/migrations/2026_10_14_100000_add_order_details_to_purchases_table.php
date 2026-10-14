<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Turn the basic purchases table into full purchase orders.
     * Only adds columns - existing purchase rows are kept and backfilled.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('po_number', 30)->nullable()->unique()->after('id');

            // Optional link to a Module 1 inventory item
            $table->foreignId('inventory_item_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('inventory_items')
                ->nullOnDelete();

            $table->decimal('unit_price', 10, 2)->default(0)->after('unit');
            $table->string('status', 20)->default('pending')->after('purchase_date');
            $table->text('notes')->nullable()->after('status');

            // Receiving workflow: stock is added to inventory only once
            $table->timestamp('received_at')->nullable()->after('notes');
            $table->timestamp('stock_added_at')->nullable()->after('received_at');
            $table->decimal('stock_added_quantity', 10, 2)->nullable()->after('stock_added_at');
        });

        // Backfill purchase rows recorded before this migration
        DB::table('purchases')
            ->whereNull('po_number')
            ->orderBy('id')
            ->get()
            ->each(function ($purchase) {
                $year = substr((string) ($purchase->purchase_date ?? $purchase->created_at ?? date('Y')), 0, 4);
                $quantity = (float) $purchase->quantity;

                DB::table('purchases')->where('id', $purchase->id)->update([
                    'po_number' => sprintf('PO-%s-%05d', $year, $purchase->id),
                    'unit_price' => $quantity > 0
                        ? round((float) $purchase->total_cost / $quantity, 2)
                        : 0,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['inventory_item_id']);
            $table->dropUnique(['po_number']);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'po_number',
                'inventory_item_id',
                'unit_price',
                'status',
                'notes',
                'received_at',
                'stock_added_at',
                'stock_added_quantity',
            ]);
        });
    }
};
