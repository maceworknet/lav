## Özet

Bu PR iki iş paketini içeriyor: (1) agentnew.md kontrollü geliştirme planının doğrulama/düzeltme turu, (2) admin panel modernizasyonu — shadcn tarzı tema, WordPress tarzı medya kütüphanesi ve SMTP + mail şablonları. Panel verisine dokunan hiçbir değişiklik yok; tek migration yeni `mail_templates` tablosunu ekliyor (additive).

## Commit 1 — Kontrollü geliştirme düzeltmeleri

- **Pasif bloklar frontend'de gizleniyor:** `FrontendController` artık sadece `is_active` blokları yüklüyor.
- **Blok render ortak partial'a taşındı:** Statik sayfalar da panelden eklenen blokları render ediyor.
- **Çift kayıt üreten ikili bildirim mimarisi giderildi:** `OrderStatusService` tek giriş noktası; asıl işi `Order` observer'ı yapıyor.
- **Durum değişiklikleri merkezileştirildi:** iyzico, Filament tablo aksiyonu ve sipariş düzenleme formu merkezi servisi kullanıyor.
- **`admin_notification_condition` işlevsel:** paid_only / all_orders; mükerrer bildirim engellendi.
- **Varsayılan zil sesi** (`public/assets/audio/bell.wav`) ve dosya yoksa otomatik yedeğe düşme.
- **"Kapalı Günler" ayarı:** kapalı günlerde teslimat slotu dönmüyor; `validateDelivery` panel timezone'unu kullanıyor.

## Commit 2 — Admin panel modernizasyonu

- **shadcn/ui tarzı tema:** zinc paleti, Inter font, ince kenarlıklı kartlar, yumuşak köşeler; render hook ile CSS (tema build gerekmez), koyu mod destekli.
- **İşlevsellik:** dashboard'a Son Siparişler widget'ı, Siparişler menüsüne işlem bekleyen rozeti.
- **WordPress tarzı Medya Kütüphanesi:** grid görünüm, sürükle-bırak çoklu yükleme (ilerleme çubuğuyla), toplu seçim/silme, detay paneli (yeniden adlandırma, URL kopyalama), arama + sayfalama. MediaPicker modal'ına da sürükle-bırak eklendi; ürün/kategori/blog/sayfa bloğu/logo alanları zaten bu seçiciyi kullanıyor.
- **SMTP entegrasyonu:** panelden host/port/kullanıcı/şifre/şifreleme/gönderen ayarları; `MailService` çalışma anında mailer'ı yapılandırıyor.
- **Mail şablonları:** panelden düzenlenebilir 7 varsayılan şablon (5 müşteri durum maili + kuryeye verildi + site sahibine yeni sipariş), `{degisken}` yer tutucuları, pasif şablon gönderilmez. Şablon silinmişse eski gömülü metinlerle geriye dönük uyumlu.
- **Canlı ortam düzeltmesi:** `User` modeline `FilamentUser` arayüzü eklendi — bu olmadan panel local dışındaki ortamlarda 403 veriyordu.

## Test

- Toplam **41/41 test geçiyor** (sqlite :memory:, panel verisine dokunmuyor).
- Yeni testler: `ControlledImprovementsTest` (6), `AdminImprovementsTest` (6 — mail şablonu render/gönderim, medya yükleme/toplu silme/yeniden adlandırma/arama), `AdminPanelSmokeTest` (6 — dashboard, medya kütüphanesi, mail şablonları, ayarlar sayfaları).
- Gerçek MySQL ile smoke test: vitrin sayfaları ve admin login HTTP 200, tema CSS enjeksiyonu doğrulandı.

## Gözden geçirenin bilmesi gerekenler

- SMTP'nin gerçekten mail göndermesi için panelden sunucu bilgileri girilip "SMTP Aktif" açılmalı; kapalıyken mailler log'a yazılır.
- VAPID anahtarları hâlâ boş; müşteri web push için üretilip girilmeli (HTTPS gerekli). SMS stub olarak duruyor.
- Tema, Filament sınıf adlarına CSS override ile uygulanıyor; majör Filament güncellemesinde küçük ayar gerekebilir.

🤖 Generated with [Claude Code](https://claude.com/claude-code)
