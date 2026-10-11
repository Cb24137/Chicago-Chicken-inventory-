<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'unit',
                'total_cost'
            ]);
        });
    }
};
