<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Delivery Zones Table
        Schema::create('delivery_zones', function (Blueprint $table) {
            $table->id();
            $table->string('city')->default('Diyarbakır');
            $table->string('district')->unique(); // e.g. Kayapınar
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Delivery Neighborhoods Table
        Schema::create('delivery_neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_zone_id')->constrained('delivery_zones')->onDelete('cascade');
            $table->string('name'); // e.g. Diclekent Mah.
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('min_order_amount', 10, 2)->default(0.00);
            $table->decimal('free_delivery_threshold', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Delivery Slots Table
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. Sabah (09:00 - 12:00)
            $table->time('start_time');
            $table->time('end_time');
            $table->time('cutoff_time')->nullable(); // Cutoff time for same-day delivery
            $table->integer('capacity')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_neighborhoods');
        Schema::dropIfExists('delivery_zones');
        Schema::dropIfExists('delivery_slots');
    }
};
