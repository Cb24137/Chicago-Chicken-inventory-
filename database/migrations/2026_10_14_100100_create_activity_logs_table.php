<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // inventory, supplier, purchase, storage or waste
            $table->string('module', 30);

            // created, updated, deleted or received
            $table->string('action', 20);

            $table->string('subject_type');
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('description');
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
