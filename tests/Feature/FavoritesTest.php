<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Favorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoritesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Test guest can toggle favorites.
     */
    public function test_guest_can_toggle_favorites(): void
    {
        $product = Product::first();
        $this->assertNotNull($product);

        // First click: Add to favorites
        $response = $this->postJson(route('favorites.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'added',
            'count' => 1,
        ]);

        $this->assertDatabaseHas('favorites', [
            'product_id' => $product->id,
            'customer_id' => null,
        ]);

        // Second click: Remove from favorites
        $response = $this->postJson(route('favorites.toggle'), [
            'product_id' => $product->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'removed',
            'count' => 0,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'product_id' => $product->id,
        ]);
    }

    /**
     * Test authenticated customer can toggle favorites.
     */
    public function test_customer_can_toggle_favorites(): void
    {
        $product = Product::first();
        
        $customer = Customer::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '05553332211',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->postJson(route('favorites.toggle'), [
                'product_id' => $product->id,
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'status' => 'added',
            'count' => 1,
        ]);

        $this->assertDatabaseHas('favorites', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'guest_token' => null,
        ]);
    }

    /**
     * Test guest favorites migrate to customer account upon registration/login.
     */
    public function test_guest_favorites_migrate_to_customer(): void
    {
        $product = Product::first();

        // 1. Simulate guest favoriting a product
        $this->withSession(['guest_token' => 'test_guest_token']);
        $response = $this->postJson(route('favorites.toggle'), [
            'product_id' => $product->id,
        ]);
        $response->assertStatus(200);

        $this->assertDatabaseHas('favorites', [
            'product_id' => $product->id,
            'guest_token' => 'test_guest_token',
            'customer_id' => null,
        ]);

        // 2. Perform registration
        $registerData = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '05553332222',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Ensure session persists guest_token
        session(['guest_token' => 'test_guest_token']);

        $response = $this->post('/kayit', $registerData);
        $response->assertRedirect('/hesabim');

        // 3. Verify favorites migrated to customer
        $customer = Customer::where('email', 'jane@example.com')->first();
        $this->assertNotNull($customer);

        $this->assertDatabaseHas('favorites', [
            'product_id' => $product->id,
            'customer_id' => $customer->id,
            'guest_token' => null,
        ]);

        $this->assertDatabaseMissing('favorites', [
            'product_id' => $product->id,
            'guest_token' => 'test_guest_token',
        ]);
    }
}
