<?php

namespace App\Services;

use App\Mail\TemplatedMail;
use App\Models\MailTemplate;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Panelden tanımlı şablon ile mail gönderir.
     * Mail hatası akışı bozmaz; loglanır.
     */
    public function sendTemplate(string $templateKey, string $to, array $data = []): bool
    {
        $template = MailTemplate::where('key', $templateKey)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            Log::info("Mail şablonu bulunamadı veya pasif: {$templateKey}");
            return false;
        }

        if (empty($to)) {
            return false;
        }

        $data = array_merge($this->defaultData(), $data);
        $rendered = $template->render($data);

        try {
            $this->applySmtpSettings();

            Mail::to($to)->send(new TemplatedMail(
                $rendered['subject'],
                $rendered['body'],
                $templateKey
            ));

            Log::info("Mail gönderildi [{$templateKey}] -> {$to}");
            return true;
        } catch (\Exception $e) {
            Log::error("Mail gönderilemedi [{$templateKey}] -> {$to}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Site sahibine (panelde tanımlı adres) şablonlu mail gönderir.
     */
    public function sendTemplateToAdmin(string $templateKey, array $data = []): bool
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $adminEmail = $settings['admin_notification_email'] ?? ($settings['site_email'] ?? null);

        if (empty($adminEmail)) {
            return false;
        }

        return $this->sendTemplate($templateKey, $adminEmail, $data);
    }

    /**
     * Panel SMTP ayarlarını çalışma anında mailer'a uygular.
     * SMTP pasifse mevcut .env mailer'ı (log vb.) kullanılır.
     */
    public function applySmtpSettings(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        $fromAddress = $settings['mail_from_address'] ?? ($settings['site_email'] ?? config('mail.from.address'));
        $fromName = $settings['mail_from_name'] ?? ($settings['site_name'] ?? config('mail.from.name'));

        config([
            'mail.from.address' => $fromAddress,
            'mail.from.name' => $fromName,
        ]);

        $smtpActive = filter_var($settings['mail_smtp_active'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (!$smtpActive || empty($settings['mail_host'])) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $settings['mail_host'],
            'mail.mailers.smtp.port' => (int) ($settings['mail_port'] ?? 587),
            'mail.mailers.smtp.username' => $settings['mail_username'] ?? null,
            'mail.mailers.smtp.password' => $settings['mail_password'] ?? null,
            'mail.mailers.smtp.scheme' => ($settings['mail_encryption'] ?? 'tls') === 'ssl' ? 'smtps' : null,
        ]);

        // Önceden oluşturulmuş mailer örneğini temizle ki yeni ayarlar geçerli olsun
        Mail::purge('smtp');
    }

    /**
     * Her şablonda kullanılabilen ortak değişkenler.
     */
    protected function defaultData(): array
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return [
            'site_name' => $settings['site_name'] ?? 'Lav Çiçekçilik',
            'site_phone' => $settings['site_phone'] ?? '',
            'site_email' => $settings['site_email'] ?? '',
        ];
    }

    /**
     * Sipariş verisinden şablon değişkenleri üretir.
     */
    public function orderData(\App\Models\Order $order): array
    {
        return [
            'order_number' => $order->order_number,
            'sender_name' => $order->sender_name,
            'recipient_name' => $order->recipient_name,
            'total' => '₺' . number_format((float) $order->total, 2, ',', '.'),
            'delivery_date' => $order->delivery_date?->format('d.m.Y') ?? '',
            'delivery_slot' => $order->delivery_slot ?? '',
            'tracking_url' => url('/siparis-takip?order_number=' . $order->order_number),
        ];
    }
}
