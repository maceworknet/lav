<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Coupon;
use App\Models\DeliveryNeighborhood;
use App\Services\DeliveryService;

class CartService
{
    protected DeliveryService $deliveryService;

    public function __construct(DeliveryService $deliveryService)
    {
        $this->deliveryService = $deliveryService;
    }

    /**
     * Get or create a cart for a guest or authenticated customer.
     */
    public function getOrCreateCart(?string $guestToken = null, ?int $customerId = null): Cart
    {
        if ($customerId) {
            return Cart::firstOrCreate(['customer_id' => $customerId]);
        }

        if ($guestToken) {
            return Cart::firstOrCreate(['guest_token' => $guestToken]);
        }

        // Fallback: create a temporary guest token if none provided
        $newToken = 'cart_' . bin2hex(random_bytes(16));
        return Cart::create(['guest_token' => $newToken]);
    }

    /**
     * Merge guest cart into customer cart when customer logs in.
     */
    public function mergeCart(string $guestToken, int $customerId): Cart
    {
        $guestCart = Cart::where('guest_token', $guestToken)->first();
        $customerCart = Cart::firstOrCreate(['customer_id' => $customerId]);

        if ($guestCart && $guestCart->id !== $customerCart->id) {
            foreach ($guestCart->items as $item) {
                // Check if item already exists in customer cart
                $existingItem = $customerCart->items()
                    ->where('product_id', $item->product_id)
                    ->whereJsonContains('options', $item->options)
                    ->where('card_note', $item->card_note)
                    ->where('delivery_date', $item->delivery_date?->toDateString())
                    ->where('delivery_slot', $item->delivery_slot)
                    ->first();

                if ($existingItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $item->quantity
                    ]);
                    $item->delete();
                } else {
                    $item->update([
                        'cart_id' => $customerCart->id
                    ]);
                }
            }

            // Transfer coupon code if customer cart doesn't have one
            if ($guestCart->coupon_code && !$customerCart->coupon_code) {
                $customerCart->update(['coupon_code' => $guestCart->coupon_code]);
            }

            $guestCart->delete();
        }

        return $customerCart;
    }

    /**
     * Add an item to the cart.
     */
    public function addItem(
        Cart $cart, 
        int $productId, 
        int $quantity = 1, 
        array $options = [], 
        ?string $cardNote = null,
        array $deliveryDetails = [],
        array $extraGiftIds = []
    ): CartItem {
        $product = Product::findOrFail($productId);

        // Find all active cart items for this product
        $existingItems = $cart->items()
            ->where('product_id', $productId)
            ->whereJsonContains('options', $options)
            ->where('card_note', $cardNote)
            ->where('delivery_date', $deliveryDetails['delivery_date'] ?? null)
            ->where('delivery_slot', $deliveryDetails['delivery_slot'] ?? null)
            ->where('delivery_district', $deliveryDetails['delivery_district'] ?? null)
            ->where('delivery_neighborhood', $deliveryDetails['delivery_neighborhood'] ?? null)
            ->get();

        $matchedItem = null;
        foreach ($existingItems as $item) {
            $itemGiftIds = $item->extraGifts->pluck('gift_product_id')->toArray();
            sort($itemGiftIds);
            $inputGiftIds = $extraGiftIds;
            sort($inputGiftIds);
            
            if ($itemGiftIds === $inputGiftIds) {
                $matchedItem = $item;
                break;
            }
        }

        if ($matchedItem) {
            $matchedItem->update([
                'quantity' => $matchedItem->quantity + $quantity
            ]);
            return $matchedItem;
        }

        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'options' => $options,
            'card_note' => $cardNote,
            'recipient_name' => $deliveryDetails['recipient_name'] ?? null,
            'recipient_phone' => $deliveryDetails['recipient_phone'] ?? null,
            'delivery_date' => $deliveryDetails['delivery_date'] ?? null,
            'delivery_slot' => $deliveryDetails['delivery_slot'] ?? null,
            'delivery_district' => $deliveryDetails['delivery_district'] ?? null,
            'delivery_neighborhood' => $deliveryDetails['delivery_neighborhood'] ?? null,
        ]);

        // Save selected extra gifts snapshot
        foreach ($extraGiftIds as $giftId) {
            $gift = \App\Models\Product::find($giftId);
            if ($gift && $gift->stock_status) {
                $cartItem->extraGifts()->create([
                    'gift_product_id' => $gift->id,
                    'name_snapshot' => $gift->name,
                    'price_snapshot' => $gift->discount_price ?? $gift->price,
                    'quantity' => 1,
                ]);
            }
        }

        return $cartItem;
    }

    /**
     * Update an item in the cart.
     */
    public function updateItem(int $cartItemId, int $quantity): bool
    {
        $item = CartItem::findOrFail($cartItemId);
        
        if ($quantity <= 0) {
            return $item->delete();
        }

        return $item->update(['quantity' => $quantity]);
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(int $cartItemId): bool
    {
        return CartItem::destroy($cartItemId) > 0;
    }

    /**
     * Apply coupon code to the cart.
     */
    public function applyCoupon(Cart $cart, string $couponCode): array
    {
        $coupon = Coupon::where('code', $couponCode)
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Geçersiz kupon kodu.'];
        }

        $totals = $this->getTotals($cart);
        
        if (!$coupon->isValidForAmount($totals['subtotal'])) {
            return ['success' => false, 'message' => 'Kupon kodunu kullanabilmek için sepet tutarınız yetersizdir. Min: ₺' . number_format($coupon->min_order_amount, 2)];
        }

        $cart->update(['coupon_code' => $coupon->code]);

        return ['success' => true, 'message' => 'Kupon kodu uygulandı.'];
    }

    /**
     * Remove coupon code from the cart.
     */
    public function removeCoupon(Cart $cart): void
    {
        $cart->update(['coupon_code' => null]);
    }

    /**
     * Get cart totals (subtotal, delivery fee, discount, and final total).
     */
    public function getTotals(Cart $cart): array
    {
        $subtotal = 0.00;
        $deliveryFee = 0.00;
        $discountAmount = 0.00;
        $deliveryFeeDetails = [
            'base_fee' => 0.00,
            'fee' => 0.00,
            'discount_amount' => 0.00,
            'campaign_applied' => false,
            'campaign_name' => null,
            'customer_message' => null,
            'remaining_amount_for_campaign' => 0.00
        ];

        // Iterate through items to calculate subtotal
        foreach ($cart->items as $item) {
            $productPrice = (float) ($item->product->discount_price ?? $item->product->price);
            
            // Calculate sum of selected option modifiers
            $optionsModifier = 0.00;
            if (is_array($item->options)) {
                foreach ($item->options as $opt) {
                    $optionsModifier += (float) ($opt['price_modifier'] ?? 0);
                }
            }

            // Include extra gift prices
            $giftsTotal = 0.00;
            if ($item->extraGifts) {
                foreach ($item->extraGifts as $gift) {
                    $giftsTotal += (float) $gift->price_snapshot * $gift->quantity;
                }
            }

            $itemTotal = (($productPrice + $optionsModifier) * $item->quantity) + $giftsTotal;
            $subtotal += $itemTotal;
        }

        // Calculate delivery fee if neighborhood is selected in the cart items
        // Since it's a single checkout, we can check the first item's neighborhood
        $firstItem = $cart->items->first();
        if ($firstItem && $firstItem->delivery_neighborhood) {
            $neighborhood = DeliveryNeighborhood::where('name', $firstItem->delivery_neighborhood)
                ->where('is_active', true)
                ->first();

            if ($neighborhood) {
                $deliveryFeeDetails = $this->deliveryService->calculateDeliveryFee($neighborhood->id, $subtotal);
                $deliveryFee = $deliveryFeeDetails['fee'];
            }
        }

        // Calculate coupon discount
        if ($cart->coupon_code) {
            $coupon = Coupon::where('code', $cart->coupon_code)->first();
            if ($coupon && $coupon->isValidForAmount($subtotal)) {
                $discountAmount = $coupon->calculateDiscount($subtotal);
            } else {
                // If coupon became invalid, clear it
                $cart->update(['coupon_code' => null]);
            }
        }

        $total = max(0.00, $subtotal + $deliveryFee - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'delivery_fee' => $deliveryFee,
            'delivery_fee_details' => $deliveryFeeDetails,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'coupon_code' => $cart->coupon_code
        ];
    }
}
