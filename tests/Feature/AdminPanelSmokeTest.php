<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function admin(): User
    {
        return User::where('email', 'admin@lav.com')->firstOrFail();
    }

    public function test_admin_dashboard_loads(): void
    {
        // Widget içerikleri Livewire ile sonradan yüklendiği için yalnızca sayfanın açıldığını doğrula
        $this->actingAs($this->admin())
            ->get('/admin')
            ->assertStatus(200)
            ->assertSee('Lav Çiçekçilik');
    }

    public function test_media_library_page_loads(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/media')
            ->assertStatus(200)
            ->assertSee('Medya Kütüphanesi')
            ->assertSee('Toplu Seç');
    }

    public function test_mail_templates_page_loads(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/mail-templates')
            ->assertStatus(200)
            ->assertSee('Mail Şablonları')
            ->assertSee('Sipariş Alındı (Müşteri)');
    }

    public function test_mail_template_edit_page_loads(): void
    {
        $template = \App\Models\MailTemplate::where('key', 'order_paid')->firstOrFail();

        $this->actingAs($this->admin())
            ->get("/admin/mail-templates/{$template->id}/edit")
            ->assertStatus(200)
            ->assertSee('Kullanılabilir değişkenler');
    }

    public function test_orders_page_loads_with_navigation_badge(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/orders')
            ->assertStatus(200);
    }

    public function test_product_edit_page_loads_with_tabs(): void
    {
        $product = \App\Models\Product::where('slug', '101-kirmizi-gul-buketi')->firstOrFail();

        $this->actingAs($this->admin())
            ->get("/admin/products/{$product->id}/edit")
            ->assertStatus(200)
            ->assertSee('Genel Bilgiler')
            ->assertSee('Görseller')
            ->assertSee('Ekstra Hediyeler')
            ->assertSee('Stok ve Teslimat');
    }

    public function test_all_resource_edit_pages_load(): void
    {
        $admin = $this->admin();

        $category = \App\Models\Category::firstOrFail();
        $blogPost = \App\Models\BlogPost::firstOrFail();
        $coupon = \App\Models\Coupon::firstOrFail();
        $zone = \App\Models\DeliveryZone::firstOrFail();
        $slot = \App\Models\DeliverySlot::firstOrFail();
        $menu = \App\Models\Menu::firstOrFail();
        $page = \App\Models\Page::where('slug', 'ana-sayfa')->firstOrFail();

        $urls = [
            "/admin/categories/{$category->id}/edit",
            "/admin/blog-posts/{$blogPost->id}/edit",
            "/admin/coupons/{$coupon->id}/edit",
            "/admin/delivery-zones/{$zone->id}/edit",
            "/admin/delivery-slots/{$slot->id}/edit",
            "/admin/menus/{$menu->id}/edit",
            "/admin/pages/{$page->id}/edit",
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertStatus(200);
        }
    }

    public function test_order_edit_page_loads_with_tabs(): void
    {
        $order = \App\Models\Order::create([
            'order_number' => 'LAV-UI-TEST-1',
            'status' => 'paid',
            'sender_name' => 'Test',
            'sender_phone' => '05001112233',
            'sender_email' => 'test@example.com',
            'recipient_name' => 'Alıcı',
            'recipient_phone' => '05001112234',
            'recipient_address' => 'Adres',
            'recipient_district' => 'Kayapınar',
            'recipient_neighborhood' => 'Diclekent Mah.',
            'delivery_date' => date('Y-m-d'),
            'delivery_slot' => 'Sabah (09:00 - 12:00)',
            'subtotal' => 100,
            'total' => 100,
        ]);

        $this->actingAs($this->admin())
            ->get("/admin/orders/{$order->id}/edit")
            ->assertStatus(200)
            ->assertSee('Genel Bakış')
            ->assertSee('Gönderici ve Alıcı')
            ->assertSee('Sipariş İçeriği');
    }

    public function test_settings_page_loads_with_smtp_tab(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/manage-settings')
            ->assertStatus(200)
            ->assertSee('E-Posta (SMTP)')
            ->assertSee('Kapalı Günler');
    }
}
