<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Setting;
use App\Models\AdminOrderNotification;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OrderStatusService
{
    protected PushNotificationService $pushNotificationService;

    public function __construct(PushNotificationService $pushNotificationService)
    {
        $this->pushNotificationService = $pushNotificationService;
    }

    /**
     * Update order status, log history, and trigger notifications.
     */
    public function updateStatus(Order $order, string $newStatus, string $changedBy = 'System', ?string $note = null): void
    {
        $oldStatus = $order->status;

        if ($oldStatus === $newStatus) {
            return;
        }

        // Update status
        $order->update(['status' => $newStatus]);

        // Log history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'note' => $note ?? "Sipariş durumu {$oldStatus} konumundan {$newStatus} konumuna güncellendi.",
            'changed_by' => $changedBy,
        ]);

        // If new status is paid/payment completed, create admin order notification
        if ($newStatus === 'paid') {
            AdminOrderNotification::create([
                'order_id' => $order->id,
                'type' => 'new_order',
                'title' => 'Yeni Sipariş Alındı!',
                'message' => "{$order->order_number} numaralı sipariş başarıyla ödendi ve alındı.",
                'is_seen' => false,
            ]);
        }

        // Trigger customer push notification
        $this->triggerCustomerPush($order, $newStatus);
    }

    /**
     * Helper to trigger customer push notification based on state.
     */
    protected function triggerCustomerPush(Order $order, string $status): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        
        $isEnabled = filter_var($settings['customer_push_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        if (!$isEnabled) {
            return;
        }

        $messageKey = "push_msg_{$status}";
        $messageBody = $settings[$messageKey] ?? null;

        // Fallbacks
        if (!$messageBody) {
            $messageBody = match ($status) {
                'paid' => 'Yeni siparişiniz başarıyla alındı!',
                'preparing' => 'Siparişiniz hazırlanıyor.',
                'assigned_to_courier' => 'Siparişiniz kuryemize atandı.',
                'on_delivery' => 'Siparişiniz teslim edilmek üzere yola çıktı!',
                'delivered' => 'Siparişiniz başarıyla teslim edildi!',
                'cancelled' => 'Siparişiniz maalesef iptal edildi.',
                default => null
            };
        }

        if (!$messageBody) {
            return; // No message for this status
        }

        $title = match ($status) {
            'paid' => 'Siparişiniz Alındı',
            'preparing' => 'Siparişiniz Hazırlanıyor',
            'assigned_to_courier' => 'Kuryeye Teslim Edildi',
            'on_delivery' => 'Siparişiniz Dağıtımda',
            'delivered' => 'Siparişiniz Teslim Edildi',
            'cancelled' => 'Siparişiniz İptal Edildi',
            default => 'Sipariş Güncellemesi'
        };

        // Order tracking URL
        $url = url("/siparis-takip?order_number=" . $order->order_number);

        try {
            $this->pushNotificationService->sendToOrderSubscriptions($order->id, $title, $messageBody, $url);
        } catch (\Exception $e) {
            Log::error("Failed to send status update push: " . $e->getMessage());
        }
    }
}
