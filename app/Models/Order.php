<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'order_number',
        'status',
        'sender_name',
        'sender_phone',
        'sender_email',
        'recipient_name',
        'recipient_phone',
        'recipient_address',
        'recipient_city',
        'recipient_district',
        'recipient_neighborhood',
        'delivery_date',
        'delivery_slot',
        'card_note',
        'card_note_signature',
        'invoice_type',
        'invoice_details',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'coupon_code',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'delivery_date' => 'date',
            'invoice_details' => 'array',
            'subtotal' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Order $order) {
            if ($order->wasChanged('status')) {
                // 1. Log Order Status History automatically
                try {
                    $user = auth()->user();
                    $changedBy = $user ? $user->name : 'System';

                    $statusLabels = [
                        'pending_payment' => 'Ödeme Bekliyor',
                        'payment_failed' => 'Ödeme Başarısız',
                        'paid' => 'Ödendi / Yeni',
                        'preparing' => 'Hazırlanıyor',
                        'assigned_to_courier' => 'Kuryede',
                        'on_delivery' => 'Dağıtımda',
                        'delivered' => 'Teslim Edildi',
                        'cancelled' => 'İptal Edildi',
                        'refunded' => 'İade Edildi',
                    ];
                    $label = $statusLabels[$order->status] ?? $order->status;

                    \App\Models\OrderStatusHistory::create([
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'note' => "Sipariş durumu '{$label}' olarak güncellendi.",
                        'changed_by' => $changedBy,
                    ]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("OrderStatusHistory log error: " . $e->getMessage());
                }

                // 2. Trigger notifications
                try {
                    app(\App\Services\NotificationService::class)->sendOrderNotifications($order, $order->status);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Notification trigger error for Order {$order->order_number}: " . $e->getMessage());
                }

                // 3. Inventory (stock) management
                try {
                    $activeStatuses = ['paid', 'preparing', 'assigned_to_courier', 'on_delivery', 'delivered'];
                    
                    $oldStatus = $order->getOriginal('status');
                    
                    $wasActive = in_array($oldStatus, $activeStatuses);
                    $isActive = in_array($order->status, $activeStatuses);

                    if (!$wasActive && $isActive) {
                        // Decrease stock
                        foreach ($order->items as $item) {
                            $product = $item->product;
                            if ($product) {
                                $product->decrement('stock', $item->quantity);
                                if ($product->stock <= 0) {
                                    $product->update(['stock_status' => false]);
                                }
                            }
                        }
                    } elseif ($wasActive && !$isActive) {
                        // Refund/Cancel: Increase stock back
                        foreach ($order->items as $item) {
                            $product = $item->product;
                            if ($product) {
                                $product->increment('stock', $item->quantity);
                                if ($product->stock > 0) {
                                    $product->update(['stock_status' => true]);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error("Stock update error for Order {$order->order_number}: " . $e->getMessage());
                }
            }
        });

        static::created(function (Order $order) {
            // Deduct stock if order is created as active/paid directly
            try {
                $activeStatuses = ['paid', 'preparing', 'assigned_to_courier', 'on_delivery', 'delivered'];
                if (in_array($order->status, $activeStatuses)) {
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->decrement('stock', $item->quantity);
                            if ($product->stock <= 0) {
                                $product->update(['stock_status' => false]);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Stock decrease on creation error for Order {$order->order_number}: " . $e->getMessage());
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function paymentTransactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }
}
