<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('waste_expiry_records', function (Blueprint $table) {
        $table->id();
        $table->string('item_name');
        $table->decimal('quantity', 10, 2);
        $table->string('unit', 30);
        $table->string('reason', 30);
        $table->date('expiry_date')->nullable();
        $table->date('record_date');
        $table->text('description')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_expiry_records');
    }
};
