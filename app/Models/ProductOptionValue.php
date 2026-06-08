<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductOptionValue extends Model
{
    protected $fillable = [
        'product_option_id',
        'label',
        'price_modifier',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'order' => 'integer',
        ];
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }
}
