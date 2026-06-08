<?php

namespace Tests\Unit;

use App\Models\ProductImage;
use Tests\TestCase;
use Illuminate\Support\Facades\Storage;

class ProductImageTest extends TestCase
{
    /**
     * Test image URL resolution.
     */
    public function test_image_url_resolution(): void
    {
        // 1. Seeded image that doesn't exist
        $img1 = new ProductImage(['image_path' => '/assets/images/non_existent_image_123.webp']);
        $this->assertNull($img1->url);

        // 2. Local asset that exists in public (e.g. favicon.ico)
        $img2 = new ProductImage(['image_path' => 'favicon.ico']);
        $this->assertNotNull($img2->url);
        $this->assertStringContainsString('favicon.ico', $img2->url);

        // 3. Stored image path that exists in storage
        $testFileName = 'test_image_' . time() . '.jpg';
        $fullPath = storage_path('app/public/' . $testFileName);
        
        // Ensure folder exists
        @mkdir(dirname($fullPath), 0755, true);
        file_put_contents($fullPath, 'fake-data');

        try {
            $img3 = new ProductImage(['image_path' => $testFileName]);
            $this->assertNotNull($img3->url);
            $this->assertStringContainsString('storage/' . $testFileName, $img3->url);
        } finally {
            // Clean up
            @unlink($fullPath);
        }
    }
}
