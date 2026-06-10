<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_gifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('extra_gift_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('extra_gift_id')->constrained('extra_gifts')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('cart_item_extra_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_item_id')->constrained('cart_items')->onDelete('cascade');
            $table->foreignId('extra_gift_id')->constrained('extra_gifts')->onDelete('cascade');
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        Schema::create('order_item_extra_gifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_item_id')->constrained('order_items')->onDelete('cascade');
            $table->foreignId('extra_gift_id')->nullable()->constrained('extra_gifts')->onDelete('set null');
            $table->string('name_snapshot');
            $table->decimal('price_snapshot', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_item_extra_gifts');
        Schema::dropIfExists('cart_item_extra_gifts');
        Schema::dropIfExists('extra_gift_product');
        Schema::dropIfExists('extra_gifts');
    }
};
