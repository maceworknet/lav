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
        Schema::dropIfExists('order_item_extra_gifts');
        Schema::dropIfExists('cart_item_extra_gifts');
        Schema::dropIfExists('extra_gift_product');
        Schema::dropIfExists('extra_gifts');

        Schema::create('product_extra_gift', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('gift_product_id')->constrained('products')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['product_id', 'gift_product_id']);
        });

        Schema::dropIfExists('cart_item_extra_gifts');
        Schema::create('cart_item_extra_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained('cart_items')->onDelete('cascade');
            $table->foreignId('gift_product_id')->constrained('products')->onDelete('cascade');
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::dropIfExists('order_item_extra_gifts');
        Schema::create('order_item_extra_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('gift_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_item_extra_gifts');
        Schema::dropIfExists('cart_item_extra_gifts');
        Schema::dropIfExists('product_extra_gift');
    }
};
