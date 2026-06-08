<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Automatically seed the database before each test.
     */
    protected $seed = true;

    /**
     * Test homepage access.
     */
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Lav');
    }

    /**
     * Test category details page access.
     */
    public function test_category_page_loads_successfully(): void
    {
        $response = $this->get('/kategori/guller');
        $response->assertStatus(200);
        $response->assertSee('Güller');
    }

    /**
     * Test product details page access.
     */
    public function test_product_page_loads_successfully(): void
    {
        $response = $this->get('/urun/101-kirmizi-gul-buketi');
        $response->assertStatus(200);
        $response->assertSee('101 Kırmızı Gül Buketi');
    }

    /**
     * Test dynamic sitemap generation.
     */
    public function test_sitemap_xml_generates_successfully(): void
    {
        $response = $this->get('/sitemap.xml');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('urlset');
    }

    /**
     * Test dynamic robots.txt.
     */
    public function test_robots_txt_works_successfully(): void
    {
        $response = $this->get('/robots.txt');
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('User-agent');
        $response->assertSee('Sitemap');
    }

    /**
     * Test blog listing page.
     */
    public function test_blog_listing_page_loads_successfully(): void
    {
        $response = $this->get('/blog');
        $response->assertStatus(200);
        $response->assertSee('Rehber');
    }

    /**
     * Test static page.
     */
    public function test_static_page_loads_successfully(): void
    {
        $response = $this->get('/sayfa/hakkimizda');
        $response->assertStatus(200);
        $response->assertSee('Hakkımızda');
    }

    /**
     * Test guest can view login form.
     */
    public function test_guest_can_view_login_form(): void
    {
        $response = $this->get('/giris');
        $response->assertStatus(200);
        $response->assertSee('Giriş Yap');
    }

    public function test_guest_can_view_register_form(): void
    {
        $response = $this->get('/kayit');
        $response->assertRedirect('/giris');
    }

    /**
     * Test guest is redirected when trying to access dashboard.
     */
    public function test_guest_cannot_view_dashboard(): void
    {
        $response = $this->get('/hesabim');
        $response->assertRedirect('/giris');
    }

    /**
     * Test customer registration, login, and dashboard access.
     */
    public function test_customer_can_register_and_access_dashboard(): void
    {
        $registerData = [
            'first_name' => 'Yaser',
            'last_name' => 'Demir',
            'email' => 'yaser@example.com',
            'phone' => '05554443322',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/kayit', $registerData);
        $response->assertRedirect('/hesabim');

        $this->assertDatabaseHas('customers', [
            'email' => 'yaser@example.com',
            'first_name' => 'Yaser'
        ]);

        $response = $this->get('/hesabim');
        $response->assertStatus(200);
        $response->assertSee('Genel Bakış');
        $response->assertSee('Siparişlerim');
        $response->assertSee('Adreslerim');
    }
}

