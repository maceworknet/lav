<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Cart;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Tests\TestCase;

class ExtraGiftTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function createCustomer(): Customer
    {
        return Customer::create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'gift_test@example.com',
            'phone' => '05553332211',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_extra_gifts_can_be_assigned_and_purchased(): void
    {
        // Mock current time to early morning to ensure all delivery slots are open
        Carbon::setTestNow(Carbon::create(date('Y'), date('m'), date('d'), 6, 0, 0, 'Europe/Istanbul'));

        $customer = $this->createCustomer();

        // 1. Create Extra Gift Category
        $category = Category::create([
            'name' => 'Ekstra Hediyeler',
            'slug' => 'ekstra-hediyeler',
            'is_active' => true,
        ]);

        // 2. Create Extra Gift Product
        $gift = Product::create([
            'name' => 'Kırmızı Balon',
            'slug' => 'kirmizi-balon',
            'sku' => 'GIFT-BALON-SKU',
            'description' => 'Uçan kırmızı balon',
            'price' => 50.00,
            'stock_status' => true,
        ]);
        $gift->categories()->attach($category->id);

        // 3. Create main Product
        $product = Product::create([
            'name' => 'Test Orkide',
            'slug' => 'test-orkide',
            'sku' => 'TEST-ORKIDE-SKU',
            'description' => 'Harika bir orkide',
            'price' => 500.00,
            'stock_status' => true,
        ]);

        // Assign gift to product
        $product->extraGifts()->attach($gift->id);

        // 4. Test Product detail page display
        $response = $this->get('/urun/' . $product->slug);
        $response->assertStatus(200);
        $response->assertSee('Kırmızı Balon');

        // 5. Add to cart with extra gift
        $cartService = app(CartService::class);
        $cart = $cartService->getOrCreateCart('guest_token_test', $customer->id);

        $cartItem = $cartService->addItem($cart, $product->id, 1, [], null, [], [$gift->id]);

        $this->assertDatabaseHas('cart_item_extra_gifts', [
            'cart_item_id' => $cartItem->id,
            'gift_product_id' => $gift->id,
            'price_snapshot' => 50.00
        ]);

        // Check cart totals
        $totals = $cartService->getTotals($cart);
        $this->assertEquals(550.00, $totals['subtotal']); // 500 product + 50 gift

        // 6. Test checkout snapshots
        $checkoutService = app(CheckoutService::class);
        $orderData = [
            'sender_name' => 'Sender',
            'sender_phone' => '05001112233',
            'sender_email' => 'sender@example.com',
            'recipient_name' => 'Recipient',
            'recipient_phone' => '05001112234',
            'recipient_address' => 'Test Mah. Test Sok.',
            'recipient_district' => 'Sur-Test',
            'recipient_neighborhood' => 'Cevat Paşa Mah. Test',
            'delivery_date' => date('Y-m-d'),
            'delivery_slot' => 'Sabah (09:00 - 12:00)',
            'card_note' => 'Mutlu Yıllar',
            'card_note_signature' => 'Ailen',
            'invoice_type' => 'personal',
            'invoice_details' => ['type' => 'personal'],
        ];

        // We need delivery neighborhood and zone to exist for checkout to succeed
        $zone = \App\Models\DeliveryZone::create([
            'city' => 'Diyarbakır',
            'district' => 'Sur-Test',
            'base_delivery_fee' => 50.00,
            'is_active' => true
        ]);
        $neighborhood = \App\Models\DeliveryNeighborhood::create([
            'delivery_zone_id' => $zone->id,
            'name' => 'Cevat Paşa Mah. Test',
            'delivery_fee' => 40.00,
            'is_active' => true
        ]);

        $order = $checkoutService->createOrderFromCart($cart, $orderData, $customer->id);

        $this->assertDatabaseHas('order_item_extra_gifts', [
            'name_snapshot' => 'Kırmızı Balon',
            'price_snapshot' => 50.00,
        ]);

        // Assert order subtotal includes the gift price (subtotal = product 500 + gift 50 = 550)
        $this->assertEquals(550.00, $order->subtotal);

        // Update the original gift price in database, past orders should not change
        $gift->update(['price' => 100.00]);
        $order->refresh();
        $this->assertEquals(550.00, $order->subtotal);

        // Clear mock time
        Carbon::setTestNow();
    }
}
