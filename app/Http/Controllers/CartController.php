<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Models\Product;
use App\Models\ProductOptionValue;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    protected CartService $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * Resolve current guest/customer cart.
     */
    private function getCart()
    {
        $guestToken = session('guest_token');
        $customerId = auth('customer')->id();

        if (!$guestToken && !$customerId) {
            $guestToken = 'cart_' . bin2hex(random_bytes(16));
            session(['guest_token' => $guestToken]);
        }

        $cart = $this->cartService->getOrCreateCart($guestToken, $customerId);

        if (!$customerId && $cart->guest_token && $cart->guest_token !== $guestToken) {
            session(['guest_token' => $cart->guest_token]);
        }

        return $cart;
    }

    /**
     * Show Cart Page.
     */
    public function index()
    {
        $cart = $this->getCart();
        $totals = $this->cartService->getTotals($cart);

        return view('frontend.pages.cart', compact('cart', 'totals'));
    }

    /**
     * Add Item to Cart.
     */
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'card_note' => 'nullable|string',
            'options' => 'nullable|array',
            'buy_now' => 'nullable|boolean',
            'delivery_district' => 'nullable|string',
            'delivery_neighborhood' => 'nullable|string',
            'delivery_date' => 'nullable|date',
            'delivery_slot' => 'nullable|string',
            'extra_gifts' => 'nullable|array',
            'extra_gifts.*' => 'exists:products,id',
        ]);

        if ($request->filled('delivery_district')) {
            session([
                'prefilled_delivery_district' => $request->input('delivery_district'),
                'prefilled_delivery_neighborhood' => $request->input('delivery_neighborhood'),
                'prefilled_delivery_date' => $request->input('delivery_date'),
                'prefilled_delivery_slot' => $request->input('delivery_slot'),
            ]);
        }

        $cart = $this->getCart();
        $productId = (int)$request->input('product_id');
        $quantity = (int)$request->input('quantity');
        $cardNote = $request->input('card_note');

        // Resolve options price modifiers and titles
        $options = [];
        if ($request->has('options')) {
            $optionInputs = $request->input('options');
            
            foreach ($optionInputs as $optionId => $valueVal) {
                if (is_array($valueVal)) {
                    foreach ($valueVal as $valId) {
                        $optionValue = ProductOptionValue::with('option')->find($valId);
                        if ($optionValue) {
                            $options[] = [
                                'option_id' => $optionValue->product_option_id,
                                'option_name' => $optionValue->option->name,
                                'value_id' => $optionValue->id,
                                'label' => $optionValue->label,
                                'price_modifier' => (float)$optionValue->price_modifier
                            ];
                        }
                    }
                } else {
                    $optionValue = ProductOptionValue::with('option')->find($valueVal);
                    if ($optionValue) {
                        $options[] = [
                            'option_id' => $optionValue->product_option_id,
                            'option_name' => $optionValue->option->name,
                            'value_id' => $optionValue->id,
                            'label' => $optionValue->label,
                            'price_modifier' => (float)$optionValue->price_modifier
                        ];
                    }
                }
            }
        }

        try {
            $deliveryDetails = [
                'delivery_district' => $request->input('delivery_district'),
                'delivery_neighborhood' => $request->input('delivery_neighborhood'),
                'delivery_date' => $request->input('delivery_date'),
                'delivery_slot' => $request->input('delivery_slot'),
            ];
            $extraGiftIds = $request->input('extra_gifts', []);
            $this->cartService->addItem($cart, $productId, $quantity, $options, $cardNote, $deliveryDetails, $extraGiftIds);
            
            if ($request->input('buy_now')) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'redirect' => route('checkout.index')
                    ]);
                }
                return redirect()->route('checkout.index');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return $this->getCartJson();
            }

            return redirect()->route('cart.index')->with('success', 'Ürün başarıyla sepetinize eklendi.');
        } catch (\Exception $e) {
            Log::error("Add to cart error: " . $e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Sepete ekleme sırasında bir hata oluştu: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Sepete ekleme sırasında bir hata oluştu: ' . $e->getMessage());
        }
    }

    /**
     * Update Quantity.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        $quantity = (int)$request->input('quantity');

        try {
            $this->cartService->updateItem($id, $quantity);
            if ($request->ajax() || $request->wantsJson()) {
                return $this->getCartJson();
            }
            return redirect()->route('cart.index')->with('success', 'Sepetiniz güncellendi.');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Güncelleme hatası: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Güncelleme hatası: ' . $e->getMessage());
        }
    }

    /**
     * Remove Item.
     */
    public function remove($id)
    {
        try {
            $this->cartService->removeItem($id);
            if (request()->ajax() || request()->wantsJson()) {
                return $this->getCartJson();
            }
            return redirect()->route('cart.index')->with('success', 'Ürün sepetten kaldırıldı.');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Kaldırma hatası: ' . $e->getMessage()
                ], 400);
            }
            return redirect()->back()->with('error', 'Kaldırma hatası: ' . $e->getMessage());
        }
    }

    /**
     * Apply Coupon.
     */
    public function applyCoupon(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50'
        ]);

        $cart = $this->getCart();
        $code = trim($request->input('code'));

        $result = $this->cartService->applyCoupon($cart, $code);

        if ($result['success']) {
            return redirect()->route('cart.index')->with('success', $result['message']);
        }

        return redirect()->route('cart.index')->with('error', $result['message']);
    }

    /**
     * Remove Coupon.
     */
    public function removeCoupon()
    {
        $cart = $this->getCart();
        $this->cartService->removeCoupon($cart);

        return redirect()->route('cart.index')->with('success', 'Kupon kodu kaldırıldı.');
    }

    /**
     * Get Cart details in JSON format for the Sidebar Cart drawer.
     */
    public function getCartJson()
    {
        $cart = $this->getCart();
        $totals = $this->cartService->getTotals($cart);
        
        $itemProductIds = $cart->items->pluck('product_id')->toArray();
        
        // Fetch up to 3 cross-sell products that are not currently in the cart
        $suggestions = Product::whereNotIn('id', $itemProductIds)
            ->where('stock_status', true)
            ->with('images')
            ->take(3)
            ->get()
            ->map(function($prod) {
                return [
                    'id' => $prod->id,
                    'name' => $prod->name,
                    'price' => (float)($prod->discount_price ?? $prod->price),
                    'image_url' => $prod->mainImage ? $prod->mainImage->url : null,
                    'slug' => $prod->slug
                ];
            });

        $freeThreshold = (float)(\App\Models\Setting::where('key', 'free_delivery_threshold')->value('value') ?? 1000.00);
        $subtotal = (float)$totals['subtotal'];
        
        $itemsData = $cart->items->map(function($item) {
            $optionsLabel = '';
            if (!empty($item->options)) {
                $opts = is_array($item->options) ? $item->options : json_decode($item->options, true);
                if (is_array($opts)) {
                    $labels = [];
                    foreach ($opts as $opt) {
                        $labels[] = $opt['label'] ?? '';
                    }
                    $optionsLabel = implode(', ', array_filter($labels));
                }
            }

            $productPrice = (float)($item->product->discount_price ?? $item->product->price);
            $optionsModifier = 0.00;
            if (is_array($item->options)) {
                foreach ($item->options as $opt) {
                    $optionsModifier += (float) ($opt['price_modifier'] ?? 0);
                }
            }
            $basePrice = $productPrice + $optionsModifier;
            $itemGiftsTotal = 0.00;
            $gifts = [];
            if ($item->extraGifts) {
                foreach ($item->extraGifts as $gift) {
                    $itemGiftsTotal += (float)$gift->price_snapshot * $gift->quantity;
                    $gifts[] = [
                        'id' => $gift->gift_product_id,
                        'name' => $gift->name_snapshot,
                        'price' => (float)$gift->price_snapshot,
                        'quantity' => $gift->quantity
                    ];
                }
            }
            $itemTotal = ($basePrice * $item->quantity) + $itemGiftsTotal;

            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name,
                'price' => $basePrice,
                'quantity' => $item->quantity,
                'total' => $itemTotal,
                'options_label' => $optionsLabel,
                'image_url' => $item->product->mainImage ? $item->product->mainImage->url : null,
                'slug' => $item->product->slug,
                'extra_gifts' => $gifts
            ];
        });

        return response()->json([
            'success' => true,
            'items' => $itemsData,
            'subtotal' => $subtotal,
            'discount' => (float)$totals['discount_amount'],
            'total' => (float)$totals['total'],
            'free_shipping_threshold' => $freeThreshold,
            'free_shipping_remaining' => max(0, $freeThreshold - $subtotal),
            'suggestions' => $suggestions,
            'cart_count' => $cart->items->sum('quantity')
        ]);
    }
}
