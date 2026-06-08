<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Customers Table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->boolean('is_guest')->default(false);
            $table->timestamps();
        });

        // 2. Customer Addresses Table
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->onDelete('cascade');
            $table->string('title'); // e.g. Ev, İş
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');
            $table->string('company')->nullable();
            $table->text('address_line');
            $table->string('city')->default('Diyarbakır');
            $table->string('district'); // İlçe
            $table->string('neighborhood'); // Mahalle
            $table->timestamps();
        });

        // 3. Carts Table
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('cascade');
            $table->string('guest_token')->nullable()->unique();
            $table->string('coupon_code')->nullable();
            $table->timestamps();
        });

        // 4. Cart Items Table
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->json('options')->nullable(); // Selected options details
            $table->text('card_note')->nullable(); // Kart Notu
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            $table->date('delivery_date')->nullable();
            $table->string('delivery_slot')->nullable();
            $table->string('delivery_district')->nullable();
            $table->string('delivery_neighborhood')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
