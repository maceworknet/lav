<?php

namespace Tests\Feature;

use App\Models\AdminOrderNotification;
use App\Models\Order;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Setting;
use App\Services\DeliveryService;
use App\Services\OrderStatusService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ControlledImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function createOrder(string $status = 'pending_payment'): Order
    {
        return Order::create([
            'order_number' => 'LAV-TEST-' . strtoupper(substr(uniqid(), -5)),
            'status' => $status,
            'sender_name' => 'Test Gönderici',
            'sender_phone' => '05001112233',
            'sender_email' => 'sender@example.com',
            'recipient_name' => 'Test Alıcı',
            'recipient_phone' => '05001112234',
            'recipient_address' => 'Test Mah. Test Sok. No:1',
            'recipient_district' => 'Kayapınar',
            'recipient_neighborhood' => 'Diclekent Mah.',
            'delivery_date' => date('Y-m-d'),
            'delivery_slot' => 'Sabah (09:00 - 12:00)',
            'subtotal' => 500.00,
            'delivery_fee' => 50.00,
            'discount_amount' => 0.00,
            'total' => 550.00,
        ]);
    }

    public function test_inactive_page_blocks_are_hidden_on_homepage(): void
    {
        $homePage = Page::where('slug', 'ana-sayfa')->firstOrFail();

        // Seed edilen hero_slider bloğunu pasif yap
        $hero = PageBlock::where('page_id', $homePage->id)->where('type', 'hero_slider')->firstOrFail();
        $hero->update(['is_active' => false]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Diyarbakır\'ın En Taze Çiçekleri');

        // Aktif bloklar görünmeye devam etmeli
        $response->assertSee('Görsel Onay Sistemi');
    }

    public function test_static_pages_render_panel_managed_blocks(): void
    {
        $page = Page::create([
            'title' => 'Kampanyalar',
            'slug' => 'kampanyalar-test',
            'content' => '',
            'is_active' => true,
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'type' => 'trust_badges',
            'content' => [
                'items' => [
                    ['title' => 'Blok Test Rozeti', 'description' => 'Statik sayfada blok render testi', 'icon' => 'truck'],
                ],
            ],
            'order' => 1,
            'is_active' => true,
        ]);

        PageBlock::create([
            'page_id' => $page->id,
            'type' => 'trust_badges',
            'content' => [
                'items' => [
                    ['title' => 'Pasif Blok Rozeti', 'description' => 'Bu görünmemeli', 'icon' => 'truck'],
                ],
            ],
            'order' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/sayfa/kampanyalar-test');
        $response->assertStatus(200);
        $response->assertSee('Blok Test Rozeti');
        $response->assertDontSee('Pasif Blok Rozeti');
    }

    public function test_order_status_service_creates_history_and_admin_notification(): void
    {
        $order = $this->createOrder();

        app(OrderStatusService::class)->updateStatus($order, 'paid', 'System', 'Test ödemesi alındı.');

        $this->assertEquals('paid', $order->fresh()->status);

        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('admin_order_notifications', [
            'order_id' => $order->id,
            'type' => 'new_order',
            'is_seen' => false,
        ]);

        // Geçmiş kaydı çift oluşmamalı (observer tek kaynak)
        $this->assertEquals(1, $order->statusHistories()->where('status', 'paid')->count());
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'status' => 'paid',
            'note' => 'Test ödemesi alındı.',
        ]);
    }

    public function test_admin_new_order_notification_is_not_duplicated(): void
    {
        $order = $this->createOrder();
        $service = app(OrderStatusService::class);

        // "Tüm siparişlerde" senaryosu: oluşturulurken bildirim + sonra ödendi
        $service->notifyAdminNewOrder($order);
        $service->updateStatus($order, 'paid', 'System');

        $this->assertEquals(
            1,
            AdminOrderNotification::where('order_id', $order->id)->where('type', 'new_order')->count()
        );

        // Aynı duruma tekrar geçiş bildirim/geçmiş üretmemeli
        $service->updateStatus($order->fresh(), 'paid', 'System');
        $this->assertEquals(
            1,
            AdminOrderNotification::where('order_id', $order->id)->where('type', 'new_order')->count()
        );
    }

    public function test_status_change_to_same_status_does_not_create_history(): void
    {
        $order = $this->createOrder('preparing');
        $service = app(OrderStatusService::class);

        $service->updateStatus($order, 'preparing', 'Admin');

        $this->assertEquals(0, $order->statusHistories()->count());
    }

    public function test_product_carousel_block_can_filter_by_category(): void
    {
        $homePage = Page::where('slug', 'ana-sayfa')->firstOrFail();
        $block = PageBlock::where('page_id', $homePage->id)->where('type', 'product_carousel')->firstOrFail();

        // "Kır Papatyaları Demeti" öne çıkan değil; varsayılanda görünmemeli
        $this->get('/')->assertDontSee('Kır Papatyaları Demeti');

        // Panelden kategori seçilmiş gibi blok içeriğini güncelle
        $category = \App\Models\Category::where('slug', 'papatyalar')->firstOrFail();
        $content = $block->content;
        $content['carousel_category_id'] = $category->id;
        $block->update(['content' => $content]);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Kır Papatyaları Demeti');
    }

    public function test_order_tracking_widget_renders_and_can_be_disabled(): void
    {
        // Varsayılan: aktif
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('otw-card', false);
        $response->assertSee('Sipariş Takip');

        // Panelden kapatılınca görünmemeli
        Setting::updateOrCreate(['key' => 'order_tracking_widget_active'], ['value' => '0', 'group' => 'general']);

        $this->get('/')->assertDontSee('otw-card');
    }

    public function test_closed_days_remove_all_delivery_slots(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 6, 10, 6, 0, 0, 'Europe/Istanbul')); // Çarşamba

        $service = app(DeliveryService::class);

        // Kapalı gün yokken slotlar dönmeli
        $slots = $service->getAvailableSlotsForDate('2026-06-10');
        $this->assertNotEmpty($slots);

        // Çarşamba (ISO 3) kapalı gün yapılınca slot dönmemeli
        Setting::updateOrCreate(['key' => 'closed_days'], ['value' => json_encode(['3']), 'group' => 'ecommerce']);

        $slots = $service->getAvailableSlotsForDate('2026-06-10');
        $this->assertEmpty($slots);

        // Perşembe açık olmalı
        $slots = $service->getAvailableSlotsForDate('2026-06-11');
        $this->assertNotEmpty($slots);

        Carbon::setTestNow();
    }
}
