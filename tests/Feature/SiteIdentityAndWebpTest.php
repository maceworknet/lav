<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteIdentityAndWebpTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_uploaded_jpeg_is_automatically_converted_to_webp(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => UploadedFile::fake()->image('buket-foto.jpg', 400, 300),
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $filePath = $response->json('media.file_path');

        $this->assertStringEndsWith('.webp', $filePath);
        Storage::disk('public')->assertExists($filePath);

        // Orijinal jpg silinmiş olmalı
        $originalPath = preg_replace('/\.webp$/', '.jpg', $filePath);
        Storage::disk('public')->assertMissing($originalPath);

        // Veritabanı kaydı da webp'yi göstermeli
        $this->assertDatabaseHas('media', ['file_path' => $filePath]);
        $this->assertStringContainsString('webp', $response->json('media.file_type') ?? 'webp');
    }

    public function test_uploaded_png_is_converted_to_webp(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => UploadedFile::fake()->image('seffaf-logo.png', 200, 200),
        ]);

        $response->assertStatus(200);
        $this->assertStringEndsWith('.webp', $response->json('media.file_path'));
    }

    public function test_webp_upload_is_not_reconverted(): void
    {
        Storage::fake('public');

        $admin = User::where('email', 'admin@lav.com')->firstOrFail();

        // Önce gerçek bir webp dosyası üret
        $img = imagecreatetruecolor(50, 50);
        $tmp = tempnam(sys_get_temp_dir(), 'webp') . '.webp';
        imagewebp($img, $tmp);
        imagedestroy($img);

        $response = $this->actingAs($admin)->post('/admin/api/media/upload', [
            'file' => new UploadedFile($tmp, 'zaten-webp.webp', 'image/webp', null, true),
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
        $this->assertStringEndsWith('.webp', $response->json('media.file_path'));
        Storage::disk('public')->assertExists($response->json('media.file_path'));

        @unlink($tmp);
    }

    public function test_favicon_and_analytics_are_rendered_from_settings(): void
    {
        Setting::updateOrCreate(['key' => 'favicon'], ['value' => 'media/test-favicon.webp', 'group' => 'header']);
        Setting::updateOrCreate(['key' => 'google_analytics_code'], ['value' => '<script data-test="ga-tracking">/* GA */</script>', 'group' => 'seo']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('storage/media/test-favicon.webp', false);
        $response->assertSee('data-test="ga-tracking"', false);
    }

    public function test_footer_logo_and_description_are_rendered_from_settings(): void
    {
        Setting::updateOrCreate(['key' => 'footer_logo'], ['value' => 'media/footer-logo.webp', 'group' => 'footer']);
        Setting::updateOrCreate(['key' => 'footer_description'], ['value' => 'Özel footer tanıtım metni testi.', 'group' => 'footer']);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('storage/media/footer-logo.webp', false);
        $response->assertSee('Özel footer tanıtım metni testi.');
    }

    public function test_footer_falls_back_to_text_logo_when_not_set(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        // Footer logosu ayarlanmadıysa yazı tabanlı marka görünmeli
        $response->assertSee('Çiçekçilik');
    }
}
