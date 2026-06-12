<?php

namespace App\Services;

use App\Models\Order;
use App\Models\AdminOrderNotification;

class OrderStatusService
{
    /**
     * Sipariş durumunu merkezi olarak günceller.
     *
     * Asıl iş Order modelindeki "updated" observer'ında yapılır:
     * durum geçmişi kaydı, admin bildirimi, e-posta/SMS/web push ve stok yönetimi.
     * Bu servis durum değişikliğinin tek giriş noktasıdır; not ve değiştiren
     * bilgisini observer'a taşır ve aynı duruma tekrar geçişi engeller.
     */
    public function updateStatus(Order $order, string $newStatus, string $changedBy = 'System', ?string $note = null): void
    {
        if ($order->status === $newStatus) {
            return;
        }

        $order->statusChangedBy = $changedBy;
        $order->statusChangeNote = $note;

        $order->update(['status' => $newStatus]);
    }

    /**
     * Admin "yeni sipariş" bildirimini oluşturur.
     * Aynı sipariş için tekrar bildirim oluşturulmasını engeller.
     */
    public function notifyAdminNewOrder(Order $order, ?string $message = null): void
    {
        $alreadyNotified = AdminOrderNotification::where('order_id', $order->id)
            ->where('type', 'new_order')
            ->exists();

        if ($alreadyNotified) {
            return;
        }

        AdminOrderNotification::create([
            'order_id' => $order->id,
            'type' => 'new_order',
            'title' => 'Yeni Sipariş Alındı!',
            'message' => $message ?? "{$order->order_number} numaralı yeni sipariş oluşturuldu.",
            'is_seen' => false,
        ]);
    }
}
