<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'care_instructions',
        'delivery_info',
        'price',
        'discount_price',
        'stock_status',
        'stock',
        'is_featured',
        'is_weekly',
        'is_best_seller',
        'is_new',
        'same_day_delivery',
        'free_delivery',
        'supports_card_note',
        'meta_title',
        'meta_description',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'stock_status' => 'boolean',
            'stock' => 'integer',
            'is_featured' => 'boolean',
            'is_weekly' => 'boolean',
            'is_best_seller' => 'boolean',
            'is_new' => 'boolean',
            'same_day_delivery' => 'boolean',
            'free_delivery' => 'boolean',
            'supports_card_note' => 'boolean',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('order');
    }

    public function mainImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_main', true);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    // Helper attribute to get final display price
    public function getFinalPriceAttribute(): float
    {
        return (float) ($this->discount_price ?? $this->price);
    }

    // Helper to check if product is discounted
    public function getIsDiscountedAttribute(): bool
    {
        return !is_null($this->discount_price) && $this->discount_price < $this->price;
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function isFavoritedByCurrentUser(): bool
    {
        $guestToken = session('guest_token');
        $customerId = auth('customer')->id();

        if ($customerId) {
            return $this->favorites()->where('customer_id', $customerId)->exists();
        }

        if ($guestToken) {
            return $this->favorites()->where('guest_token', $guestToken)->exists();
        }

        return false;
    }
}
