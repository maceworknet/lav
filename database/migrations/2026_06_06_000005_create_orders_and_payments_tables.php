<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Orders Table
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->onDelete('set null');
            $table->string('order_number')->unique();
            $table->string('status')->default('pending_payment');
            
            // Sender Info
            $table->string('sender_name');
            $table->string('sender_phone');
            $table->string('sender_email');
            
            // Recipient Info
            $table->string('recipient_name');
            $table->string('recipient_phone');
            $table->text('recipient_address');
            $table->string('recipient_city')->default('Diyarbakır');
            $table->string('recipient_district');
            $table->string('recipient_neighborhood');
            
            // Delivery Time Info
            $table->date('delivery_date');
            $table->string('delivery_slot'); // e.g. Sabah (09:00 - 12:00)
            
            // Card Note
            $table->text('card_note')->nullable();
            $table->string('card_note_signature')->nullable();
            
            // Invoice details
            $table->string('invoice_type')->default('personal'); // personal, corporate
            $table->json('invoice_details')->nullable();
            
            // Financial details
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->default(0.00);
            $table->decimal('discount_amount', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            
            $table->string('coupon_code')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });

        // 2. Order Items Table
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_name');
            $table->string('sku');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2);
            $table->json('options')->nullable(); // Selected options at time of checkout
            $table->decimal('total', 10, 2);
            $table->timestamps();
        });

        // 3. Order Status Histories Table
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('status');
            $table->text('note')->nullable();
            $table->string('changed_by')->default('System');
            $table->timestamps();
        });

        // 4. Payment Transactions Table
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('payment_id')->nullable(); // iyzico paymentId
            $table->string('conversation_id')->nullable();
            $table->string('status'); // SUCCESS, FAILURE
            $table->decimal('amount', 10, 2);
            $table->string('card_type')->nullable();
            $table->string('card_association')->nullable();
            $table->string('card_family')->nullable();
            $table->integer('installment')->default(1);
            $table->string('error_code')->nullable();
            $table->text('error_message')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('order_status_histories');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};
