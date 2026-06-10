<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'options',
        'card_note',
        'recipient_name',
        'recipient_phone',
        'delivery_date',
        'delivery_slot',
        'delivery_district',
        'delivery_neighborhood',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'options' => 'array',
            'delivery_date' => 'date',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function extraGifts(): HasMany
    {
        return $this->hasMany(CartItemExtraGift::class);
    }
}
