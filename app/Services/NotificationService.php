<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send order notifications across active channels.
     */
    public function sendOrderNotifications(Order $order, string $status): void
    {
        $siteName = Setting::where('key', 'site_name')->value('value') ?? 'Lav Çiçekçilik';
        
        // Define message templates based on status
        $messages = [
            'paid' => [
                'subject' => "Siparişiniz Alındı - {$order->order_number}",
                'body' => "Merhaba {$order->sender_name},\n\nSiparişiniz başarıyla alındı ve ödemeniz onaylandı! Çiçeğiniz belirttiğiniz tarihte teslim edilmek üzere sıraya alınmıştır.\n\nSipariş Numarası: {$order->order_number}\nToplam Tutar: ₺{$order->total}\nTeslim Tarihi: {$order->delivery_date?->format('d.m.Y')}\nSaat Aralığı: {$order->delivery_slot}\n\nSevdiklerinizi mutlu ettiğiniz için teşekkür ederiz.\n\nSaygılarımızla,\n{$siteName}",
                'sms' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz ve ödemeniz onaylanmıştır. Teslimat tarihi: {$order->delivery_date?->format('d.m.Y')}. Bizi tercih ettiğiniz için teşekkür ederiz."
            ],
            'preparing' => [
                'subject' => "Siparişiniz Hazırlanıyor - {$order->order_number}",
                'body' => "Merhaba {$order->sender_name},\n\nSiparişiniz ({$order->order_number}) tasarım ekibimiz tarafından özenle hazırlanmaya başlandı! Tamamlandığında kuryemize teslim edilecektir.\n\nSaygılarımızla,\n{$siteName}",
                'sms' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz özenle hazırlanmaya başlanmıştır."
            ],
            'on_delivery' => [
                'subject' => "Siparişiniz Yola Çıktı - {$order->order_number}",
                'body' => "Merhaba {$order->sender_name},\n\nSiparişiniz ({$order->order_number}) kuryemize teslim edilmiş ve alıcısına ulaştırılmak üzere yola çıkmıştır.\n\nSaygılarımızla,\n{$siteName}",
                'sms' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz alıcısına ulaştırılmak üzere kuryemizle yola çıkmıştır."
            ],
            'delivered' => [
                'subject' => "Siparişiniz Teslim Edildi! - {$order->order_number}",
                'body' => "Merhaba {$order->sender_name},\n\nHarika bir haberimiz var! Siparişiniz ({$order->order_number}) alıcısı {$order->recipient_name} kişisine başarıyla teslim edilmiştir.\n\nMutlu günler dileriz.\n\nSaygılarımızla,\n{$siteName}",
                'sms' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz alıcısı {$order->recipient_name} kişisine başarıyla teslim edilmiştir. Mutlu günler dileriz!"
            ],
            'cancelled' => [
                'subject' => "Siparişiniz İptal Edildi - {$order->order_number}",
                'body' => "Merhaba {$order->sender_name},\n\nSiparişiniz ({$order->order_number}) talebiniz veya sistem onayı nedeniyle iptal edilmiştir. Eğer bir ücret ödemesi yapıldıysa iade işlemleriniz başlatılacaktır.\n\nSaygılarımızla,\n{$siteName}",
                'sms' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz iptal edilmiştir. Ücret iadeniz bankanıza yansıtılacaktır."
            ]
        ];

        if (!isset($messages[$status])) {
            return;
        }

        $template = $messages[$status];

        // 1. Send Email (via raw log/mail driver)
        try {
            Mail::raw($template['body'], function ($message) use ($order, $template, $siteName) {
                $message->to($order->sender_email)
                    ->subject($template['subject'])
                    ->from(config('mail.from.address', 'hello@example.com'), $siteName);
            });
            Log::info("Email notification sent for order {$order->order_number} status {$status}");
        } catch (\Exception $e) {
            Log::error("Failed to send email notification for order {$order->order_number}: " . $e->getMessage());
        }

        // 2. Send SMS (through ready stub structure)
        $this->sendSMS($order->sender_phone, $template['sms']);

        // 3. Send Web Push Notification
        try {
            $settings = Setting::pluck('value', 'key')->toArray();
            $messageKey = "push_msg_{$status}";
            $pushBody = $settings[$messageKey] ?? null;

            if (!$pushBody) {
                // Mapped fallbacks
                $pushBody = match ($status) {
                    'paid' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz ve ödemeniz onaylanmıştır.",
                    'preparing' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz özenle hazırlanmaya başlanmıştır.",
                    'assigned_to_courier' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz kuryemize teslim edilmiştir.",
                    'on_delivery' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz kuryemizle yola çıkmıştır.",
                    'delivered' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz başarıyla teslim edilmiştir.",
                    'cancelled' => "Sayın {$order->sender_name}, {$order->order_number} nolu siparişiniz iptal edilmiştir.",
                    default => 'Sipariş durumunuz güncellendi.'
                };
            }

            $pushTitle = match ($status) {
                'paid' => 'Siparişiniz Alındı',
                'preparing' => 'Siparişiniz Hazırlanıyor',
                'assigned_to_courier' => 'Kuryeye Verildi',
                'on_delivery' => 'Siparişiniz Dağıtımda',
                'delivered' => 'Siparişiniz Teslim Edildi',
                'cancelled' => 'Siparişiniz İptal Edildi',
                default => 'Sipariş Güncellemesi'
            };

            $trackingUrl = url("/siparis-takip?order_number=" . $order->order_number);

            app(\App\Services\PushNotificationService::class)->sendToOrderSubscriptions($order->id, $pushTitle, $pushBody, $trackingUrl);
        } catch (\Exception $e) {
            Log::error("Failed to send push notification for order {$order->order_number}: " . $e->getMessage());
        }
    }

    /**
     * Stub method representing SMS gateway integration.
     * Logs the payload so developers can easily swap this with a real provider (Netgsm, İletimerkezi, etc.).
     */
    public function sendSMS(string $phone, string $message): void
    {
        // Format phone number (Remove non-numeric characters)
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);

        // SMS Logging
        Log::channel('stack')->info("[SMS INTEGRATION GATEWAY] To: +{$cleanPhone} | Message: {$message}");
    }

    /**
     * Generate custom WhatsApp chat trigger link with pre-filled message text.
     * Useful for triggering manual updates or support conversations.
     */
    public function generateWhatsAppLink(string $phone, string $message): string
    {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        
        // If phone starts with 0 and has 10 digits, prepend country code 90
        if (str_starts_with($cleanPhone, '0') && strlen($cleanPhone) === 11) {
            $cleanPhone = '90' . substr($cleanPhone, 1);
        } elseif (strlen($cleanPhone) === 10) {
            $cleanPhone = '90' . $cleanPhone;
        }

        return 'https://api.whatsapp.com/send?phone=' . $cleanPhone . '&text=' . urlencode($message);
    }
}
