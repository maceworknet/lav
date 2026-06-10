<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_fee_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->onDelete('cascade');
            $table->foreignId('delivery_neighborhood_id')->nullable()->constrained('delivery_neighborhoods')->onDelete('cascade');
            $table->string('type'); // free_delivery, fixed_fee, discount
            $table->decimal('min_cart_total', 10, 2)->default(0.00);
            $table->string('discount_type')->nullable(); // fixed, percent
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->decimal('fixed_delivery_fee', 10, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('customer_message')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_fee_campaigns');
    }
};
