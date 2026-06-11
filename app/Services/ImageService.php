<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    /**
     * public diskindeki bir görseli WebP formatına çevirir.
     *
     * Başarılıysa yeni (webp) dosya yolunu döner ve orijinali siler.
     * Dönüştürülemiyorsa (desteklenmeyen format, hata vb.) null döner — orijinal korunur.
     */
    public function convertToWebp(string $relativePath, int $quality = 82): ?string
    {
        $disk = Storage::disk('public');

        if (!$disk->exists($relativePath)) {
            return null;
        }

        $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

        // Zaten webp ise veya dönüştürülmesi istenmeyen formatlar (svg vektörel, gif animasyonlu olabilir)
        if (in_array($extension, ['webp', 'svg', 'gif', 'ico'], true)) {
            return null;
        }

        if (!function_exists('imagewebp')) {
            Log::warning('GD imagewebp desteği yok; WebP dönüşümü atlandı.');
            return null;
        }

        $absolutePath = $disk->path($relativePath);

        try {
            $image = match ($extension) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($absolutePath),
                'png' => @imagecreatefrompng($absolutePath),
                'bmp' => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($absolutePath) : false,
                default => false,
            };

            if (!$image) {
                return null;
            }

            // PNG şeffaflığını koru
            if ($extension === 'png') {
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
            }

            $newRelativePath = preg_replace('/\.' . preg_quote($extension, '/') . '$/i', '.webp', $relativePath);
            if ($newRelativePath === $relativePath) {
                $newRelativePath = $relativePath . '.webp';
            }

            $newAbsolutePath = $disk->path($newRelativePath);

            if (!imagewebp($image, $newAbsolutePath, $quality)) {
                imagedestroy($image);
                return null;
            }

            imagedestroy($image);

            // Orijinal dosyayı kaldır
            $disk->delete($relativePath);

            return $newRelativePath;
        } catch (\Throwable $e) {
            Log::error("WebP dönüşüm hatası ({$relativePath}): " . $e->getMessage());
            return null;
        }
    }
}
