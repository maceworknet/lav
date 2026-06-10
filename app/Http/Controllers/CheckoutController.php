<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\DeliveryService;
use App\Services\IyzicoPaymentService;
use App\Models\Setting;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected CheckoutService $checkoutService;
    protected DeliveryService $deliveryService;
    protected IyzicoPaymentService $paymentService;

    public function __construct(
        CartService $cartService,
        CheckoutService $checkoutService,
        DeliveryService $deliveryService,
        IyzicoPaymentService $paymentService
    ) {
        $this->cartService = $cartService;
        $this->checkoutService = $checkoutService;
        $this->deliveryService = $deliveryService;
        $this->paymentService = $paymentService;
    }

    /**
     * Get active cart helper.
     */
    private function getCart()
    {
        $guestToken = session('guest_token');
        $customerId = auth('customer')->id();

        if (!$guestToken && !$customerId) {
            $guestToken = 'cart_' . bin2hex(random_bytes(16));
            session(['guest_token' => $guestToken]);
        }

        return $this->cartService->getOrCreateCart($guestToken, $customerId);
    }

    /**
     * Show Checkout Page.
     */
    public function index()
    {
        $cart = $this->getCart();

        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş olduğu için ödeme sayfasına geçilemez.');
        }

        $totals = $this->cartService->getTotals($cart);
        
        // Check minimum order amount
        $minOrderAmount = (float)(Setting::where('key', 'min_order_amount')->value('value') ?? 300.00);
        if ($totals['subtotal'] < $minOrderAmount) {
            return redirect()->route('cart.index')->with('error', 'Sipariş oluşturabilmek için minimum sepet tutarı ₺' . number_format($minOrderAmount, 2) . ' olmalıdır.');
        }

        // Delivery Zones and Slots
        $deliveryZones = $this->deliveryService->getActiveZones();
        $initialSlots = $this->deliveryService->getAvailableSlotsForDate(date('Y-m-d'));

        return view('frontend.pages.checkout', compact('cart', 'totals', 'deliveryZones', 'initialSlots', 'minOrderAmount'));
    }

    /**
     * Get available delivery slots for a date (JSON endpoint).
     */
    public function getSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today'
        ]);

        $slots = $this->deliveryService->getAvailableSlotsForDate($request->input('date'));

        return response()->json($slots);
    }

    /**
     * Calculate delivery fee for the current cart (JSON endpoint).
     */
    public function calculateDeliveryFee(Request $request)
    {
        $request->validate([
            'neighborhood_id' => 'required|integer',
        ]);

        $cart = $this->getCart();
        $totals = $this->cartService->getTotals($cart);
        
        $feeDetails = $this->deliveryService->calculateDeliveryFee(
            $request->input('neighborhood_id'),
            $totals['subtotal']
        );

        return response()->json($feeDetails);
    }

    /**
     * Process order submission and iyzico payment.
     */
    public function process(Request $request)
    {
        $request->validate([
            // Sender details
            'sender_name' => 'required|string|max:100',
            'sender_phone' => 'required|string|max:20',
            'sender_email' => 'required|email|max:100',
            
            // Recipient details
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|max:20',
            'recipient_address' => 'required|string',
            'recipient_district' => 'required|string',
            'recipient_neighborhood' => 'required|string',
            
            // Delivery scheduling
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_slot' => 'required|string',
            
            // Personal message card
            'card_note' => 'nullable|string',
            'card_note_signature' => 'nullable|string',
            
            // Invoicing details
            'invoice_type' => 'required|in:personal,corporate',
            'company_name' => 'required_if:invoice_type,corporate|nullable|string|max:150',
            'tax_office' => 'required_if:invoice_type,corporate|nullable|string|max:100',
            'tax_number' => 'required_if:invoice_type,corporate|nullable|string|max:50',
            
            // Credit card details
            'card_holder_name' => 'required|string|max:150',
            'card_number' => 'required|string',
            'expire_month' => 'required|string|size:2',
            'expire_year' => 'required|string|size:2',
            'cvc' => 'required|string|between:3,4',
        ]);

        $cart = $this->getCart();

        if ($cart->items->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Sepetiniz boş.');
        }

        // Validate delivery district, neighborhood, date, and slot
        $deliveryValidation = $this->deliveryService->validateDelivery(
            $request->input('recipient_district'),
            $request->input('recipient_neighborhood'),
            $request->input('delivery_date'),
            $request->input('delivery_slot')
        );

        if (!$deliveryValidation['valid']) {
            return redirect()->back()->withErrors(['delivery_slot' => $deliveryValidation['message']])->withInput();
        }

        // Format card number cleanly (strip spaces)
        $cardNumber = str_replace(' ', '', $request->input('card_number'));
        $expireMonth = $request->input('expire_month');
        $expireYear = '20' . $request->input('expire_year'); // Map e.g. "26" to "2026"

        // Build invoice details array
        $invoiceDetails = [
            'type' => $request->input('invoice_type'),
            'company_name' => $request->input('company_name'),
            'tax_office' => $request->input('tax_office'),
            'tax_number' => $request->input('tax_number'),
        ];

        // Format order registration data payload
        $orderData = [
            'sender_name' => $request->input('sender_name'),
            'sender_phone' => $request->input('sender_phone'),
            'sender_email' => $request->input('sender_email'),
            'recipient_name' => $request->input('recipient_name'),
            'recipient_phone' => $request->input('recipient_phone'),
            'recipient_address' => $request->input('recipient_address'),
            'recipient_district' => $request->input('recipient_district'),
            'recipient_neighborhood' => $request->input('recipient_neighborhood'),
            'delivery_date' => $request->input('delivery_date'),
            'delivery_slot' => $request->input('delivery_slot'),
            'card_note' => $request->input('card_note'),
            'card_note_signature' => $request->input('card_note_signature'),
            'invoice_type' => $request->input('invoice_type'),
            'invoice_details' => $invoiceDetails,
        ];

        // Temporarily store cart items in memory to allow recovery if payment fails
        $cartItemsBackup = [];
        foreach ($cart->items as $item) {
            $cartItemsBackup[] = [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'options' => $item->options,
                'card_note' => $item->card_note,
            ];
        }
        $couponBackup = $cart->coupon_code;

        try {
            // 1. Create the Order (Database transaction saves order details and clears cart)
            $order = $this->checkoutService->createOrderFromCart($cart, $orderData, auth('customer')->id());
            
            // 2. Process Credit Card Payment via iyzico
            $cardDetails = [
                'card_holder_name' => $request->input('card_holder_name'),
                'card_number' => $cardNumber,
                'expire_month' => $expireMonth,
                'expire_year' => $expireYear,
                'cvc' => $request->input('cvc'),
            ];

            $paymentResult = $this->paymentService->processPayment($order, $cardDetails);

            if ($paymentResult['success']) {
                // Payment was successful! Redirect to the confirmation success screen.
                return redirect()->route('checkout.success', ['order_number' => $order->order_number])
                    ->with('success', 'Siparişiniz başarıyla alındı ve ödemeniz tahsil edildi.');
            }

            // Payment failed! Restore the cart so the user doesn't have to rebuild it.
            $restoredCart = $this->getCart();
            foreach ($cartItemsBackup as $backupItem) {
                $this->cartService->addItem(
                    $restoredCart,
                    $backupItem['product_id'],
                    $backupItem['quantity'],
                    $backupItem['options'] ?? [],
                    $backupItem['card_note'],
                    [
                        'delivery_district' => $orderData['recipient_district'],
                        'delivery_neighborhood' => $orderData['recipient_neighborhood'],
                        'delivery_date' => $orderData['delivery_date'],
                        'delivery_slot' => $orderData['delivery_slot']
                    ]
                );
            }
            if ($couponBackup) {
                $restoredCart->update(['coupon_code' => $couponBackup]);
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Ödeme işlemi başarısız: ' . $paymentResult['error'])
                ->withInput();

        } catch (\Exception $e) {
            Log::error("Checkout execution crash: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            // Recover Cart
            $restoredCart = $this->getCart();
            foreach ($cartItemsBackup as $backupItem) {
                $this->cartService->addItem(
                    $restoredCart,
                    $backupItem['product_id'],
                    $backupItem['quantity'],
                    $backupItem['options'] ?? [],
                    $backupItem['card_note']
                );
            }
            if ($couponBackup) {
                $restoredCart->update(['coupon_code' => $couponBackup]);
            }

            return redirect()->route('checkout.index')
                ->with('error', 'Siparişiniz oluşturulurken bir hata oluştu: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show Order Success confirmation screen.
     */
    public function success($orderNumber)
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product.images'])
            ->firstOrFail();

        return view('frontend.pages.success', compact('order'));
    }

    /**
     * Get unseen admin order notifications.
     */
    public function getAdminNewOrders(Request $request)
    {
        $notifications = \App\Models\AdminOrderNotification::where('is_seen', false)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    /**
     * Mark all unseen admin order notifications as seen.
     */
    public function markAdminNotificationsSeen(Request $request)
    {
        \App\Models\AdminOrderNotification::where('is_seen', false)->update([
            'is_seen' => true,
            'seen_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Subscribe customer/order for web push notifications.
     */
    public function subscribePush(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'order_id' => 'nullable|integer',
        ]);

        $customerId = auth('customer')->id();
        $orderId = $request->input('order_id');

        $subscription = \App\Models\PushSubscription::updateOrCreate(
            ['endpoint' => $request->input('endpoint')],
            [
                'customer_id' => $customerId,
                'order_id' => $orderId,
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'user_agent' => $request->userAgent(),
                'is_active' => true,
            ]
        );

        return response()->json(['success' => true, 'subscription_id' => $subscription->id]);
    }

    /**
     * Unsubscribe web push notifications.
     */
    public function unsubscribePush(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        \App\Models\PushSubscription::where('endpoint', $request->input('endpoint'))
            ->update(['is_active' => false]);

        return response()->json(['success' => true]);
    }
}
