<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Setting;
use App\Services\CartService;
use App\Services\DeliveryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutService
{
    protected CartService $cartService;
    protected DeliveryService $deliveryService;

    public function __construct(CartService $cartService, DeliveryService $deliveryService)
    {
        $this->cartService = $cartService;
        $this->deliveryService = $deliveryService;
    }

    /**
     * Create a new order from the cart.
     */
    public function createOrderFromCart(Cart $cart, array $data, ?int $customerId = null): Order
    {
        if ($cart->items->count() === 0) {
            throw new \Exception('Sepetiniz boş olduğu için sipariş oluşturulamaz.');
        }

        // 1. Get Totals
        $totals = $this->cartService->getTotals($cart);

        // 2. Validate Minimum Order Amount
        $minOrderSetting = Setting::where('key', 'min_order_amount')->first();
        $minOrderAmount = $minOrderSetting ? (float) $minOrderSetting->value : 300.00;

        if ($totals['subtotal'] < $minOrderAmount) {
            throw new \Exception("Sipariş oluşturabilmek için minimum sepet tutarı ₺" . number_format($minOrderAmount, 2) . " olmalıdır.");
        }

        // 3. Validate Delivery Parameters
        // Using first item's delivery settings since they are set for the order
        $firstItem = $cart->items->first();
        $deliveryValidation = $this->deliveryService->validateDelivery(
            $firstItem->delivery_district ?? $data['recipient_district'],
            $firstItem->delivery_neighborhood ?? $data['recipient_neighborhood'],
            $firstItem->delivery_date?->toDateString() ?? $data['delivery_date'],
            $firstItem->delivery_slot ?? $data['delivery_slot']
        );

        if (!$deliveryValidation['valid']) {
            throw new \Exception($deliveryValidation['message']);
        }

        // 4. Database Transaction
        return DB::transaction(function () use ($cart, $data, $totals, $customerId, $firstItem) {
            // Generate Order Number: LAV-YYYYMMDD-XXXX (unique)
            $datePrefix = 'LAV-' . date('Ymd');
            $uniqueId = strtoupper(substr(uniqid(), -5));
            $orderNumber = "{$datePrefix}-{$uniqueId}";

            // Create Order
            $order = Order::create([
                'customer_id' => $customerId,
                'order_number' => $orderNumber,
                'status' => 'pending_payment',
                
                // Sender Info
                'sender_name' => $data['sender_name'],
                'sender_phone' => $data['sender_phone'],
                'sender_email' => $data['sender_email'],
                
                // Recipient Info
                'recipient_name' => $data['recipient_name'],
                'recipient_phone' => $data['recipient_phone'],
                'recipient_address' => $data['recipient_address'],
                'recipient_city' => 'Diyarbakır',
                'recipient_district' => $firstItem->delivery_district ?? $data['recipient_district'],
                'recipient_neighborhood' => $firstItem->delivery_neighborhood ?? $data['recipient_neighborhood'],
                
                // Delivery Slot
                'delivery_date' => $firstItem->delivery_date ?? $data['delivery_date'],
                'delivery_slot' => $firstItem->delivery_slot ?? $data['delivery_slot'],
                
                // Card Note
                'card_note' => $data['card_note'] ?? $firstItem->card_note ?? null,
                'card_note_signature' => $data['card_note_signature'] ?? null,
                
                // Invoice Details
                'invoice_type' => $data['invoice_type'] ?? 'personal',
                'invoice_details' => $data['invoice_details'] ?? null,
                
                // Financials
                'subtotal' => $totals['subtotal'],
                'delivery_fee' => $totals['delivery_fee'],
                'discount_amount' => $totals['discount_amount'],
                'total' => $totals['total'],
                
                'coupon_code' => $totals['coupon_code'],
                'admin_note' => $data['admin_note'] ?? null,
            ]);

            // Create Order Items
            foreach ($cart->items as $item) {
                $productPrice = (float) ($item->product->discount_price ?? $item->product->price);
                
                // Sum selected options modifications
                $optionsModifier = 0.00;
                if (is_array($item->options)) {
                    foreach ($item->options as $opt) {
                        $optionsModifier += (float) ($opt['price_modifier'] ?? 0);
                    }
                }

                $unitPrice = $productPrice + $optionsModifier;
                
                $itemGiftsTotal = 0.00;
                if ($item->extraGifts) {
                    foreach ($item->extraGifts as $cartGift) {
                        $itemGiftsTotal += (float) $cartGift->price_snapshot * $cartGift->quantity;
                    }
                }
                
                $itemTotal = ($unitPrice * $item->quantity) + $itemGiftsTotal;

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name,
                    'sku' => $item->product->sku,
                    'quantity' => $item->quantity,
                    'price' => $unitPrice,
                    'options' => $item->options,
                    'total' => $itemTotal,
                ]);

                // Copy extra gifts from cart item to order item
                if ($item->extraGifts) {
                    foreach ($item->extraGifts as $cartGift) {
                        $orderItem->extraGifts()->create([
                            'gift_product_id' => $cartGift->gift_product_id,
                            'name_snapshot' => $cartGift->name_snapshot,
                            'price_snapshot' => $cartGift->price_snapshot,
                            'quantity' => $cartGift->quantity,
                        ]);
                    }
                }
            }

            // Coupon Usage Logging
            if ($order->coupon_code) {
                $coupon = Coupon::where('code', $order->coupon_code)->first();
                if ($coupon) {
                    $coupon->increment('used_count');
                    
                    CouponUsage::create([
                        'coupon_id' => $coupon->id,
                        'order_id' => $order->id,
                        'customer_email' => $order->sender_email,
                    ]);
                }
            }

            // Create Order Status History
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending_payment',
                'note' => 'Sipariş oluşturuldu, ödeme bekleniyor.',
                'changed_by' => 'Customer',
            ]);

            // Clear Cart
            $cart->items()->delete();
            $cart->update(['coupon_code' => null]);

            Log::info("Order created successfully: {$orderNumber} for Cart ID: {$cart->id}");

            return $order;
        });
    }
}
