<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    /**
     * Helper to create a test customer.
     */
    private function createCustomer(string $email = 'test@example.com'): Customer
    {
        return Customer::create([
            'first_name' => 'Can',
            'last_name' => 'Yılmaz',
            'email' => $email,
            'phone' => '05553332211',
            'password' => Hash::make('password123'),
        ]);
    }

    /**
     * Test a customer can add an address.
     */
    public function test_customer_can_add_address(): void
    {
        $customer = $this->createCustomer();

        $addressData = [
            'title' => 'Ev Adresi',
            'first_name' => 'Can',
            'last_name' => 'Yılmaz',
            'phone' => '05553332211',
            'company' => 'My Corp',
            'city' => 'Diyarbakır',
            'district' => 'Sur',
            'neighborhood' => 'Cami Kebir Mah.',
            'address_line' => 'Gazi Cad. No: 12',
        ];

        $response = $this->actingAs($customer, 'customer')
            ->post('/hesabim/adres-ekle', $addressData);

        $response->assertRedirect('/hesabim?tab=addresses');
        $response->assertSessionHas('success', 'Yeni adresiniz başarıyla eklendi.');

        $this->assertDatabaseHas('customer_addresses', [
            'customer_id' => $customer->id,
            'title' => 'Ev Adresi',
            'district' => 'Sur',
        ]);
    }

    /**
     * Test a customer can update an existing address.
     */
    public function test_customer_can_update_address(): void
    {
        $customer = $this->createCustomer('can@example.com');
        $address = $customer->addresses()->create([
            'title' => 'Eski Adres',
            'first_name' => 'Can',
            'last_name' => 'Yılmaz',
            'phone' => '05553332211',
            'company' => 'My Corp',
            'city' => 'Diyarbakır',
            'district' => 'Sur',
            'neighborhood' => 'Cami Kebir Mah.',
            'address_line' => 'Gazi Cad. No: 12',
        ]);

        $updatedData = [
            'title' => 'Yeni Evim',
            'first_name' => 'Caner',
            'last_name' => 'Yılmaz',
            'phone' => '05553332200',
            'company' => 'My Corp Corp',
            'city' => 'Diyarbakır',
            'district' => 'Kayapınar',
            'neighborhood' => 'Dicle Kent Mah.',
            'address_line' => '75. Yol Cad. No: 5',
        ];

        $response = $this->actingAs($customer, 'customer')
            ->put("/hesabim/adres-guncelle/{$address->id}", $updatedData);

        $response->assertRedirect('/hesabim?tab=addresses');
        $response->assertSessionHas('success', 'Adresiniz başarıyla güncellendi.');

        $this->assertDatabaseHas('customer_addresses', [
            'id' => $address->id,
            'title' => 'Yeni Evim',
            'first_name' => 'Caner',
            'district' => 'Kayapınar',
        ]);

        $this->assertDatabaseMissing('customer_addresses', [
            'id' => $address->id,
            'title' => 'Eski Adres',
        ]);
    }

    /**
     * Test a customer can delete an existing address.
     */
    public function test_customer_can_delete_address(): void
    {
        $customer = $this->createCustomer('delete@example.com');
        $address = $customer->addresses()->create([
            'title' => 'Silinecek Adres',
            'first_name' => 'Can',
            'last_name' => 'Yılmaz',
            'phone' => '05553332211',
            'city' => 'Diyarbakır',
            'district' => 'Sur',
            'neighborhood' => 'Cami Kebir Mah.',
            'address_line' => 'Gazi Cad. No: 12',
        ]);

        $response = $this->actingAs($customer, 'customer')
            ->delete("/hesabim/adres-sil/{$address->id}");

        $response->assertRedirect('/hesabim?tab=addresses');
        $response->assertSessionHas('success', 'Adres başarıyla silindi.');

        $this->assertDatabaseMissing('customer_addresses', [
            'id' => $address->id,
        ]);
    }
}
