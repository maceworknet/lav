<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryNeighborhood extends Model
{
    protected $fillable = [
        'delivery_zone_id',
        'name',
        'delivery_fee',
        'min_order_amount',
        'free_delivery_threshold',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee' => 'decimal:2',
            'min_order_amount' => 'decimal:2',
            'free_delivery_threshold' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }
}
