<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Optional expiry date of a received delivery, and when its expiry alert was sent
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('stock_added_quantity');
            $table->timestamp('expiry_alert_sent_at')->nullable()->after('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['expiry_date', 'expiry_alert_sent_at']);
        });
    }
};
