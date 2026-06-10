<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOrderNotification extends Model
{
    protected $fillable = [
        'order_id',
        'admin_user_id',
        'type',
        'title',
        'message',
        'is_seen',
        'seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_seen' => 'boolean',
            'seen_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function adminUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }
}
