<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'image_path',
        'is_main',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'is_main' => 'boolean',
            'order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the resolved URL for the product image, or null if the file does not exist on disk.
     */
    public function getUrlAttribute(): ?string
    {
        $path = $this->image_path;
        if (empty($path)) {
            return null;
        }

        // Clean leading slash for local file checks
        $cleanPath = ltrim($path, '/');

        // Check if it's a seeded asset path (starts with assets/)
        if (str_starts_with($cleanPath, 'assets/')) {
            if (file_exists(public_path($cleanPath))) {
                return asset($cleanPath);
            }
            return null; // Return null so the view renders the gradient placeholder instead of a broken image
        }

        // Check if it exists in storage/app/public/
        if (file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // Check if it exists in public/ directly
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        return null;
    }
}
