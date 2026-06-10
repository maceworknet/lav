<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\Setting;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    /**
     * Send Web Push notification to all active subscriptions of a customer.
     */
    public function sendToCustomer(int $customerId, string $title, string $body, ?string $url = null): void
    {
        $subscriptions = PushSubscription::where('customer_id', $customerId)
            ->where('is_active', true)
            ->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $this->sendNotifications($subscriptions, $title, $body, $url);
    }

    /**
     * Send Web Push notification to all active subscriptions of an order (for guest orders or specific tracking).
     */
    public function sendToOrderSubscriptions(int $orderId, string $title, string $body, ?string $url = null): void
    {
        $order = \App\Models\Order::find($orderId);
        if (!$order) {
            return;
        }

        $subscriptions = PushSubscription::where('order_id', $orderId)
            ->where('is_active', true)
            ->get();

        if ($order->customer_id) {
            $customerSubscriptions = PushSubscription::where('customer_id', $order->customer_id)
                ->where('is_active', true)
                ->get();
            $subscriptions = $subscriptions->merge($customerSubscriptions);
        }

        if ($subscriptions->isEmpty()) {
            return;
        }

        // Deduplicate subscriptions by endpoint
        $subscriptions = $subscriptions->unique('endpoint');

        $this->sendNotifications($subscriptions, $title, $body, $url);
    }

    /**
     * Common method to trigger Web Push sending.
     */
    protected function sendNotifications($subscriptions, string $title, string $body, ?string $url = null): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        
        $isEnabled = filter_var($settings['customer_push_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if (!$isEnabled) {
            return;
        }

        $publicKey = $settings['customer_push_vapid_public_key'] ?? '';
        $privateKey = $settings['customer_push_vapid_private_key'] ?? '';
        $email = $settings['site_email'] ?? 'info@lavcicekcilik.com';

        if (empty($publicKey) || empty($privateKey)) {
            Log::warning('Web Push VAPID keys are missing in settings.');
            return;
        }

        try {
            $auth = [
                'VAPID' => [
                    'subject' => 'mailto:' . $email,
                    'publicKey' => $publicKey,
                    'privateKey' => $privateKey,
                ],
            ];

            $webPush = new WebPush($auth);
            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'url' => $url ?? url('/'),
            ]);

            foreach ($subscriptions as $sub) {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'keys' => [
                        'p256dh' => $sub->public_key,
                        'auth' => $sub->auth_token,
                    ],
                ]);

                $webPush->queueNotification($subscription, $payload);
            }

            foreach ($webPush->flush() as $report) {
                $endpoint = $report->getEndpoint();
                if (!$report->isSuccess()) {
                    Log::warning("Web Push notification failed for endpoint: {$endpoint}. Error: {$report->getReason()}");
                    
                    if ($report->isSubscriptionExpired()) {
                        PushSubscription::where('endpoint', $endpoint)->update(['is_active' => false]);
                        Log::info("Push subscription expired and disabled for endpoint: {$endpoint}");
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Error sending Web Push notification: ' . $e->getMessage());
        }
    }
}
