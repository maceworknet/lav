<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Setting;
use Iyzipay\Options;
use Iyzipay\Model\Locale;
use Iyzipay\Model\Currency;
use Iyzipay\Model\PaymentChannel;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\Address;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\PaymentCard;
use Iyzipay\Model\Payment;
use Iyzipay\Request\CreatePaymentRequest;
use Illuminate\Support\Facades\Log;

class IyzicoPaymentService
{
    protected OrderStatusService $orderStatusService;

    public function __construct(OrderStatusService $orderStatusService)
    {
        $this->orderStatusService = $orderStatusService;
    }

    /**
     * Get iyzico Options from database settings.
     */
    private function getOptions(): Options
    {
        $apiKey = Setting::where('key', 'iyzico_api_key')->value('value') ?? '';
        $secretKey = Setting::where('key', 'iyzico_secret_key')->value('value') ?? '';
        $baseUrl = Setting::where('key', 'iyzico_base_url')->value('value') ?? 'https://sandbox-api.iyzipay.com';

        $options = new Options();
        $options->setApiKey($apiKey);
        $options->setSecretKey($secretKey);
        $options->setBaseUrl($baseUrl);

        return $options;
    }

    /**
     * Retrieve installment info for a given price.
     */
    public function getInstallments(float $price): ?array
    {
        $options = $this->getOptions();
        $request = new \Iyzipay\Request\RetrieveInstallmentInfoRequest();
        $request->setLocale(Locale::TR);
        $request->setConversationId('inst_' . uniqid());
        $request->setPrice((string)$price);

        try {
            $response = \Iyzipay\Model\InstallmentInfo::retrieve($request, $options);
            if ($response->getStatus() === 'success') {
                return json_decode($response->getRawResult(), true);
            }
        } catch (\Exception $e) {
            Log::error("iyzico fetch installments error: " . $e->getMessage());
        }
        return null;
    }

    /**
     * Process a direct card payment.
     */
    public function processPayment(Order $order, array $cardDetails): array
    {
        $options = $this->getOptions();

        // 1. Create Payment Request
        $request = new CreatePaymentRequest();
        $request->setLocale(Locale::TR);
        $request->setConversationId($order->order_number);
        $request->setPrice((string)$order->subtotal);
        $request->setPaidPrice((string)$order->total);
        $request->setCurrency(Currency::TL);
        $request->setInstallment(1);
        $request->setBasketId($order->order_number);
        $request->setPaymentChannel(PaymentChannel::WEB);
        $request->setPaymentGroup(PaymentGroup::PRODUCT);

        // 2. Set Payment Card Details
        $paymentCard = new PaymentCard();
        $paymentCard->setCardHolderName($cardDetails['card_holder_name']);
        $paymentCard->setCardNumber($cardDetails['card_number']);
        $paymentCard->setExpireMonth($cardDetails['expire_month']);
        $paymentCard->setExpireYear($cardDetails['expire_year']);
        $paymentCard->setCvc($cardDetails['cvc']);
        $paymentCard->setRegisterCard(0);
        $request->setPaymentCard($paymentCard);

        // 3. Set Buyer Details
        $buyer = new Buyer();
        $buyer->setId("BYR-" . $order->customer_id ?? 'GUEST');
        
        // Split names safely
        $nameParts = explode(' ', $order->sender_name, 2);
        $firstName = $nameParts[0] ?? 'Lav';
        $lastName = $nameParts[1] ?? 'Müşterisi';

        $buyer->setName($firstName);
        $buyer->setSurname($lastName);
        $buyer->setGsmNumber($order->sender_phone);
        $buyer->setEmail($order->sender_email);
        $buyer->setIdentityNumber("11111111111"); // Static for guest/mock identity
        $buyer->setRegistrationAddress($order->recipient_address); // Fallback to shipping address
        $buyer->setIp(request()->ip() ?? '127.0.0.1');
        $buyer->setCity('Diyarbakır');
        $buyer->setCountry('Turkey');
        $request->setBuyer($buyer);

        // 4. Set Shipping Address (Recipient)
        $shippingAddress = new Address();
        $shippingAddress->setContactName($order->recipient_name);
        $shippingAddress->setCity($order->recipient_city);
        $shippingAddress->setCountry('Turkey');
        $shippingAddress->setAddress($order->recipient_address . ' ' . $order->recipient_neighborhood . ' ' . $order->recipient_district);
        $request->setShippingAddress($shippingAddress);

        // 5. Set Billing Address (Default to recipient or sender)
        $billingAddress = new Address();
        $billingAddress->setContactName($order->sender_name);
        $billingAddress->setCity('Diyarbakır');
        $billingAddress->setCountry('Turkey');
        $billingAddress->setAddress($order->recipient_address);
        $request->setBillingAddress($billingAddress);

        // 6. Set Basket Items
        $basketItems = [];
        foreach ($order->items as $item) {
            $basketItem = new BasketItem();
            $basketItem->setId("PRD-" . $item->product_id);
            $basketItem->setName($item->product_name);
            $basketItem->setCategory1("Çiçek");
            $basketItem->setItemType(BasketItemType::PHYSICAL);
            $basketItem->setPrice((string)$item->total); // Total price for this basket item (qty * price)
            $basketItems[] = $basketItem;
        }
        $request->setBasketItems($basketItems);

        try {
            // 7. Call iyzico API
            $paymentResponse = Payment::create($request, $options);

            // Log details
            $status = $paymentResponse->getStatus(); // success or failure
            $paymentStatus = $paymentResponse->getPaymentStatus(); // SUCCESS or null
            $isSuccess = ($status === 'success' && $paymentStatus === 'SUCCESS');

            // Save transaction record
            $transaction = PaymentTransaction::create([
                'order_id' => $order->id,
                'payment_id' => $paymentResponse->getPaymentId(),
                'conversation_id' => $paymentResponse->getConversationId(),
                'status' => $isSuccess ? 'SUCCESS' : 'FAILURE',
                'amount' => $order->total,
                'card_type' => $paymentResponse->getCardType(),
                'card_association' => $paymentResponse->getCardAssociation(),
                'card_family' => $paymentResponse->getCardFamily(),
                'installment' => $paymentResponse->getInstallment() ?? 1,
                'error_code' => $paymentResponse->getErrorCode(),
                'error_message' => $paymentResponse->getErrorMessage(),
                'raw_response' => json_decode($paymentResponse->getRawResult(), true),
            ]);

            if ($isSuccess) {
                // Durum değişikliği merkezi servis üzerinden: geçmiş kaydı,
                // admin bildirimi ve müşteri push bildirimi tetiklenir.
                $this->orderStatusService->updateStatus(
                    $order,
                    'paid',
                    'System',
                    'iyzico ödemesi başarıyla tahsil edildi. Sipariş onaylandı.'
                );

                return [
                    'success' => true,
                    'payment_id' => $paymentResponse->getPaymentId(),
                    'transaction_id' => $transaction->id
                ];
            } else {
                $this->orderStatusService->updateStatus(
                    $order,
                    'payment_failed',
                    'System',
                    'iyzico ödemesi başarısız oldu: ' . $paymentResponse->getErrorMessage()
                );

                return [
                    'success' => false,
                    'error' => $paymentResponse->getErrorMessage() ?? 'Ödeme işlemi başarısız oldu.',
                    'transaction_id' => $transaction->id
                ];
            }

        } catch (\Exception $e) {
            Log::error("iyzico payment error for Order #{$order->order_number}: " . $e->getMessage());
            
            $this->orderStatusService->updateStatus(
                $order,
                'payment_failed',
                'System',
                'Ödeme işlemi sırasında sistem hatası oluştu: ' . $e->getMessage()
            );

            return [
                'success' => false,
                'error' => 'Ödeme altyapısı ile bağlantı kurulurken bir sorun oluştu.'
            ];
        }
    }
}
