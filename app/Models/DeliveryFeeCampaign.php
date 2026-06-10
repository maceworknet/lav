<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryFeeCampaign extends Model
{
    protected $fillable = [
        'name',
        'delivery_zone_id',
        'delivery_neighborhood_id',
        'type',
        'min_cart_total',
        'discount_type',
        'discount_value',
        'fixed_delivery_fee',
        'starts_at',
        'ends_at',
        'customer_message',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'min_cart_total' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'fixed_delivery_fee' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(DeliveryNeighborhood::class, 'delivery_neighborhood_id');
    }
}
