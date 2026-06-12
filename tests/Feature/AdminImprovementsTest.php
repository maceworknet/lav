<?php

namespace Tests\Feature;

use App\Mail\TemplatedMail;
use App\Models\MailTemplate;
use App\Models\Media;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminImprovementsTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    private function createOrder(string $status = 'pending_payment'): Order
    {
        return Order::create([
            'order_number' => 'LAV-MAIL-' . strtoupper(substr(uniqid(), -5)),
            'status' => $status,
            'sender_name' => 'Mail Gönderici',
            'sender_phone' => '05001112233',
            'sender_email' => 'musteri@example.com',
            'recipient_name' => 'Mail Alıcı',
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

    public function test_mail_template_placeholders_are_rendered(): void
    {
        $template = MailTemplate::where('key', 'order_paid')->firstOrFail();

        $rendered = $template->render([
            'sender_name' => 'Ahmet',
            'order_number' => 'LAV-123',
            'total' => '₺550,00',
            'delivery_date' => '11.06.2026',
            'delivery_slot' => 'Sabah',
            'tracking_url' => 'http://localhost/takip',
            'site_name' => 'Lav Çiçekçilik',
            'recipient_name' => 'Ayşe',
        ]);

        $this->assertStringContainsString('LAV-123', $rendered['subject']);
        $this->assertStringContainsString('Ahmet', $rendered['body']);
        $this->assertStringNotContainsString('{order_number}', $rendered['body']);
    }

    public function test_status_change_sends_templated_mail_to_customer_and_admin(): void
    {
        Mail::fake();

        Setting::updateOrCreate(
            ['key' => 'admin_notification_email'],
            ['value' => 'patron@example.com', 'group' => 'mail']
        );

        // Şablon panelden düzenlenmiş gibi konuyu değiştir
        MailTemplate::where('key', 'order_paid')->update([
            'subject' => 'ÖZEL KONU {order_number}',
        ]);

        $order = $this->createOrder();
        app(OrderStatusService::class)->updateStatus($order, 'paid', 'System');

        // Müşteriye giden mail panelden düzenlenen konuyu kullanmalı
        Mail::assertSent(TemplatedMail::class, function (TemplatedMail $mail) use ($order) {
            return $mail->templateKey === 'order_paid'
                && str_contains($mail->subjectLine, 'ÖZEL KONU')
                && str_contains($mail->subjectLine, $order->order_number)
                && $mail->hasTo('musteri@example.com');
        });

        // Site sahibine yeni sipariş maili gitmeli
        Mail::assertSent(TemplatedMail::class, function (TemplatedMail $mail) {
            return $mail->templateKey === 'admin_new_order'
                && $mail->hasTo('patron@example.com');
        });
    }

    public function test_html_template_sends_raw_html_body(): void
    {
        Mail::fake();

        MailTemplate::where('key', 'order_paid')->update([
            'is_html' => true,
            'body' => '<h1 style="color:red">Merhaba {sender_name}</h1><p>Sipariş: {order_number}</p>',
        ]);

        $order = $this->createOrder();
        app(OrderStatusService::class)->updateStatus($order, 'paid', 'System');

        Mail::assertSent(TemplatedMail::class, function (TemplatedMail $mail) use ($order) {
            return $mail->templateKey === 'order_paid'
                && $mail->isHtml === true
                && str_contains($mail->bodyContent, '<h1 style="color:red">Merhaba Mail Gönderici</h1>')
                && str_contains($mail->bodyContent, $order->order_number);
        });
    }

    public function test_plain_text_template_is_not_marked_html(): void
    {
        Mail::fake();

        $order = $this->createOrder('paid');
        app(OrderStatusService::class)->updateStatus($order, 'preparing', 'Admin');

        Mail::assertSent(TemplatedMail::class, function (TemplatedMail $mail) {
            return $mail->templateKey === 'order_preparing' && $mail->isHtml === false;
        });
    }

    public function test_inactive_template_is_not_sent(): void
    {
        Mail::fake();

        MailTemplate::where('key', 'order_preparing')->update(['is_active' => false]);

        $order = $this->createOrder('paid');
        app(OrderStatusService::class)->updateStatus($order, 'preparing', 'Admin');

        Mail::assertNotSent(TemplatedMail::class, function (TemplatedMail $mail) {
            return $mail->templateKey === 'order_preparing';
        });
    }

    public function test_media_can_be_uploaded_and_bulk_deleted(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        // Yükleme (sürükle-bırak ile aynı endpoint)
        $response = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => UploadedFile::fake()->image('cicek-foto.jpg', 600, 600),
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $mediaId = $response->json('media.id');
        $filePath = $response->json('media.file_path');

        Storage::disk('public')->assertExists($filePath);

        $second = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => UploadedFile::fake()->image('ikinci-foto.jpg'),
        ]);
        $secondId = $second->json('media.id');

        // Toplu silme
        $delete = $this->actingAs($admin)->postJson('/admin/api/media/delete', [
            'ids' => [$mediaId, $secondId],
        ]);

        $delete->assertStatus(200)->assertJson(['success' => true, 'deleted' => 2]);
        $this->assertDatabaseMissing('media', ['id' => $mediaId]);
        $this->assertDatabaseMissing('media', ['id' => $secondId]);
        Storage::disk('public')->assertMissing($filePath);
    }

    public function test_media_rename_updates_name(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        $upload = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => UploadedFile::fake()->image('eski-ad.jpg'),
        ]);
        $mediaId = $upload->json('media.id');

        $rename = $this->actingAs($admin)->postJson("/admin/api/media/{$mediaId}/rename", [
            'name' => 'Yeni Görsel Adı',
        ]);

        $rename->assertStatus(200)->assertJson(['success' => true]);
        $this->assertDatabaseHas('media', ['id' => $mediaId, 'name' => 'Yeni Görsel Adı']);
    }

    public function test_media_api_supports_pagination_and_search(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        Storage::disk('public')->put('media/ozel-gul.jpg', 'x');
        Media::create(['name' => 'Özel Gül Görseli', 'file_path' => 'media/ozel-gul.jpg']);

        Storage::disk('public')->put('media/orkide.jpg', 'x');
        Media::create(['name' => 'Orkide', 'file_path' => 'media/orkide.jpg']);

        $response = $this->actingAs($admin)->get('/admin/api/media?per_page=10&search=' . urlencode('Özel'));

        $response->assertStatus(200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertEquals('Özel Gül Görseli', $response->json('media.0.name'));
    }
}
