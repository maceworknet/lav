<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('subject');
            $table->text('body');
            $table->string('recipient_type')->default('customer'); // customer, admin
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Varsayılan şablonlar (tablo yeni oluştuğu için mevcut veri ezilmez).
        // Panelden yapılan düzenlemeler her zaman korunur.
        $now = now();
        $templates = [
            [
                'key' => 'order_paid',
                'name' => 'Sipariş Alındı (Müşteri)',
                'subject' => 'Siparişiniz Alındı - {order_number}',
                'body' => "Merhaba {sender_name},\n\nSiparişiniz başarıyla alındı ve ödemeniz onaylandı! Çiçeğiniz belirttiğiniz tarihte teslim edilmek üzere sıraya alınmıştır.\n\nSipariş Numarası: {order_number}\nToplam Tutar: {total}\nTeslim Tarihi: {delivery_date}\nSaat Aralığı: {delivery_slot}\n\nSiparişinizi takip etmek için: {tracking_url}\n\nSevdiklerinizi mutlu ettiğiniz için teşekkür ederiz.\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'order_preparing',
                'name' => 'Sipariş Hazırlanıyor (Müşteri)',
                'subject' => 'Siparişiniz Hazırlanıyor - {order_number}',
                'body' => "Merhaba {sender_name},\n\nSiparişiniz ({order_number}) tasarım ekibimiz tarafından özenle hazırlanmaya başlandı! Tamamlandığında kuryemize teslim edilecektir.\n\nSiparişinizi takip etmek için: {tracking_url}\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'order_assigned_to_courier',
                'name' => 'Sipariş Kuryeye Verildi (Müşteri)',
                'subject' => 'Siparişiniz Kuryeye Verildi - {order_number}',
                'body' => "Merhaba {sender_name},\n\nSiparişiniz ({order_number}) kuryemize teslim edilmiştir. Kısa süre içinde dağıtıma çıkacaktır.\n\nSiparişinizi takip etmek için: {tracking_url}\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'order_on_delivery',
                'name' => 'Sipariş Yola Çıktı (Müşteri)',
                'subject' => 'Siparişiniz Yola Çıktı - {order_number}',
                'body' => "Merhaba {sender_name},\n\nSiparişiniz ({order_number}) kuryemize teslim edilmiş ve alıcısına ulaştırılmak üzere yola çıkmıştır.\n\nSiparişinizi takip etmek için: {tracking_url}\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'order_delivered',
                'name' => 'Sipariş Teslim Edildi (Müşteri)',
                'subject' => 'Siparişiniz Teslim Edildi! - {order_number}',
                'body' => "Merhaba {sender_name},\n\nHarika bir haberimiz var! Siparişiniz ({order_number}) alıcısı {recipient_name} kişisine başarıyla teslim edilmiştir.\n\nMutlu günler dileriz.\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'order_cancelled',
                'name' => 'Sipariş İptal Edildi (Müşteri)',
                'subject' => 'Siparişiniz İptal Edildi - {order_number}',
                'body' => "Merhaba {sender_name},\n\nSiparişiniz ({order_number}) talebiniz veya sistem onayı nedeniyle iptal edilmiştir. Eğer bir ücret ödemesi yapıldıysa iade işlemleriniz başlatılacaktır.\n\nSaygılarımızla,\n{site_name}",
                'recipient_type' => 'customer',
            ],
            [
                'key' => 'admin_new_order',
                'name' => 'Yeni Sipariş Bildirimi (Site Sahibi)',
                'subject' => 'Yeni Sipariş Alındı! - {order_number}',
                'body' => "Yeni bir sipariş alındı ve ödemesi onaylandı.\n\nSipariş Numarası: {order_number}\nGönderici: {sender_name}\nAlıcı: {recipient_name}\nToplam Tutar: {total}\nTeslim Tarihi: {delivery_date}\nSaat Aralığı: {delivery_slot}\n\nSiparişi görüntülemek için yönetim paneline giriş yapın.",
                'recipient_type' => 'admin',
            ],
        ];

        foreach ($templates as $template) {
            \Illuminate\Support\Facades\DB::table('mail_templates')->insert(array_merge($template, [
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_templates');
    }
};
