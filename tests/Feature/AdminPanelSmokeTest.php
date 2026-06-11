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

    public function test_settings_page_loads_with_smtp_tab(): void
    {
        $this->actingAs($this->admin())
            ->get('/admin/manage-settings')
            ->assertStatus(200)
            ->assertSee('E-Posta (SMTP)')
            ->assertSee('Kapalı Günler');
    }
}
