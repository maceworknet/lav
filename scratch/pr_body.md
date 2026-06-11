## Özet

agentnew.md kontrollü geliştirme planının doğrulama ve düzeltme turu. Planın büyük bölümü önceki commit'te zaten uygulanmıştı; bu PR tespit edilen 7 gerçek açığı kapatıyor. Panel verisine dokunan hiçbir değişiklik yok — seeder'ların idempotent olduğu doğrulandı.

## Değişiklikler

- **Pasif bloklar frontend'de gizleniyor:** Panelde pasif yapılan sayfa blokları sitede görünmeye devam ediyordu. `FrontendController` artık sadece `is_active` blokları yüklüyor (veri değişmedi, sorgu filtresi).
- **Blok render ortak partial'a taşındı:** 900 satırlık blok döngüsü `home.blade.php`'den `frontend/partials/page_blocks.blade.php`'ye çıkarıldı; statik sayfalar da artık panelden eklenen blokları render ediyor.
- **Çift kayıt üreten ikili bildirim mimarisi giderildi:** Hem `Order` observer'ı hem `OrderStatusService` ayrı ayrı durum geçmişi + admin bildirimi + push üretiyordu. `OrderStatusService` artık tek giriş noktası olan ince bir API; asıl işi observer yapıyor.
- **Durum değişiklikleri merkezileştirildi:** `IyzicoPaymentService`, Filament tablo aksiyonu (`OrdersTable`) ve sipariş düzenleme formu (`EditOrder`) doğrudan `update()` yerine `OrderStatusService::updateStatus()` kullanıyor.
- **`admin_notification_condition` ayarı işlevsel:** `all_orders` seçiliyse sipariş oluşturulunca, `paid_only` ise ödeme onayında bildirim üretiliyor; aynı sipariş için mükerrer bildirim engellendi.
- **Varsayılan zil sesi eklendi:** PHP ile sentezlenmiş yüksek sesli zil (`public/assets/audio/bell.wav`); panelde kayıtlı dosya yoksa otomatik bu dosyaya düşüyor.
- **"Kapalı Günler" ayarı eklendi:** Panelden haftanın günleri seçilebiliyor; `DeliveryService` kapalı günlerde slot döndürmüyor. `validateDelivery` artık panel timezone ayarını kullanıyor.

## Test

- Yeni `ControlledImprovementsTest` (6 test): pasif blok gizleme, statik sayfada blok render, merkezi durum servisi, bildirim tekilliği, kapalı gün slot filtresi.
- Tam paket: **29/29 geçti** (sqlite :memory:, panel verisine dokunmuyor).
- Gerçek MySQL veritabanıyla smoke test: ana sayfa, ürün, sepet, statik sayfa, blog, takip, teslimat saatleri JSON endpoint'i — hepsi HTTP 200.

## Gözden geçirenin bilmesi gerekenler

- VAPID anahtarları panelde hâlâ boş; müşteri push'unun canlıda çalışması için üretilip girilmeli (HTTPS gerekli).
- SMS stub olarak kaldı (log'a yazıyor).
- Admin bildirimi polling tabanlı (panel ayarından aralık değiştirilebilir); Reverb'e geçiş sonraki faz önerisi.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
