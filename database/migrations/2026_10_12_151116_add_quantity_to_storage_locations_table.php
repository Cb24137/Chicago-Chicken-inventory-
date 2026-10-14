<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('storage_locations', 'quantity')) {
            Schema::table('storage_locations', function (Blueprint $table) {
                $table->unsignedInteger('quantity')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('storage_locations', 'quantity')) {
            Schema::table('storage_locations', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};