<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    protected $fillable = [
        'city',
        'district',
        'is_active',
        'base_delivery_fee',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'base_delivery_fee' => 'decimal:2',
            'sort_order' => 'integer',
        ];
    }

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(DeliveryNeighborhood::class);
    }
}
