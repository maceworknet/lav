# AGENT.md — Lav Çiçekçilik Laravel + Filament Özel Mini E-Ticaret Çekirdeği

## 1. Proje Tanımı

Bu proje, Lav Çiçekçilik için **Laravel + Filament + özel mini e-ticaret çekirdeği** ile geliştirilecek hızlı, SEO uyumlu, tam yönetim panelli ve çiçekçilik sektörüne özel bir e-ticaret sistemidir.

Bu projede **WooCommerce, OpenCart, PrestaShop, Bagisto, LunarPHP veya hazır e-ticaret çekirdeği kullanılmayacaktır.** Ürün, kategori, sepet, sipariş, ödeme, teslimat, kampanya, SEO, blog, kurye ve içerik blokları tamamen özel veritabanı yapısı ile geliştirilecektir.

Ana hedefler:

- Çok hızlı açılan, mobil öncelikli ve SEO uyumlu frontend oluşturmak.
- Header’dan footer’a kadar tüm alanları Filament panelden yönetilebilir yapmak.
- Ürün, kategori, sayfa, blog, kampanya, teslimat, sipariş ve ödeme süreçlerini panelden yönetmek.
- Çiçekçilik sektörüne özel teslimat tarihi, saat aralığı, ilçe/mahalle bazlı ücret, kart notu, görsel onay ve kurye süreçlerini sisteme dahil etmek.
- iyzico ödeme altyapısını entegre etmek.
- Türkçe karakter sorunu olmayan, tamamen Türkçe panel ve frontend geliştirmek.
- Proje sonunda çalışır kod, migration, seeder, panel, frontend, test ve deploy dokümanları teslim etmek.

---

## 2. Temel Teknoloji Kararları

### 2.1 Backend

- Laravel 11 veya güncel stabil Laravel sürümü kullanılacak.
- PHP 8.2 veya üzeri kullanılacak.
- MySQL veya MariaDB kullanılacak.
- Migration, seeder ve factory yapıları düzenli oluşturulacak.
- İş mantıkları Controller içinde şişirilmeyecek; Service, Action, DTO, Enum, Job, Observer yapıları gerektiği yerde kullanılacak.
- Kritik işlemler transaction içinde yapılacak.
- Sipariş, ödeme ve teslimat süreçleri loglanacak.

### 2.2 Admin Panel

- Filament kullanılacak.
- Panel tamamen Türkçe olacak.
- Panelde Türkçe karakter hatası olmayacak.
- Dashboard, kaynak yönetimleri, raporlar ve sistem ayarları Filament üzerinden yapılacak.
- Roller: Süper Admin, Sipariş Sorumlusu, İçerik Editörü, Kurye, Muhasebe opsiyonel olarak desteklenecek.

### 2.3 Frontend

- Blade + Tailwind CSS kullanılacak.
- Gereksiz React, Vue, Inertia kullanılmayacak.
- Semantic HTML ve SEO dostu yapı kurulacak.
- Görseller WebP destekleyecek ve lazy-load kullanılacak.
- Font sayısı düşük tutulacak.
- Tüm bloklar panelden yönetilebilir olacak.

### 2.4 Ödeme

- iyzico API entegrasyonu yapılacak.
- Sandbox ve production modu panelden yönetilecek.
- Payment transaction kayıtları tutulacak.
- Başarılı/başarısız ödeme dönüşleri sipariş durumuna işlenecek.

### 2.5 Performans

Hedef performans:

- Mobil PageSpeed: 85+
- Desktop PageSpeed: 95+
- Ana sayfa açılışı: 1–2 saniye
- Ürün sayfası açılışı: 1–2 saniye
- Kategori sayfası açılışı: 1–2 saniye

Kullanılacak optimizasyonlar:

- Cache
- Eager loading
- WebP görsel
- Lazy-load
- Minify CSS/JS
- Menü, ayar, blok cache
- N+1 sorgu kontrolü

---

## 3. Türkçe Karakter ve Dil Kuralları

Bu proje Türkçe bir projedir. Hiçbir alanda Türkçe karakter hatası oluşmamalıdır.

Zorunlu kurallar:

- Veritabanı karakter seti: `utf8mb4`
- Veritabanı collation: `utf8mb4_unicode_ci`
- HTML meta: `<meta charset="UTF-8">`
- Laravel locale: `tr`
- Timezone: `Europe/Istanbul`
- Panel etiketleri Türkçe olacak.
- Hata mesajları Türkçe olacak.
- Bildirim şablonları Türkçe olacak.
- Slug üretimi Türkçe karakterleri temiz dönüştürecek.

Örnek slug dönüşümü:

```txt
Çiçek Sepeti → cicek-sepeti
Gül Aranjmanı → gul-aranjmani
Doğum Günü Çiçekleri → dogum-gunu-cicekleri
Diyarbakır Çiçekçi → diyarbakir-cicekci
```

---

## 4. Proje Fazları ve Tasklist

# FAZ 0 — Analiz ve Proje Planlama

Amaç: Projenin kapsamını, veri yapısını ve yapılacak modülleri netleştirmek.

- [ ] Mevcut Lav Çiçekçilik sitesini analiz et.
- [ ] Referans alınacak çiçekçilik sitelerindeki fonksiyonları çıkar.
- [ ] Ürün kategori yapısını belirle.
- [ ] Sipariş akışını uçtan uca çıkar.
- [ ] Teslimat ilçesi, mahalle, tarih ve saat ihtiyaçlarını netleştir.
- [ ] Panelden yönetilecek tüm alanları listele.
- [ ] Ana sayfa blok listesini oluştur.
- [ ] SEO sayfa türlerini belirle.
- [ ] MVP ve gelişmiş sürüm ayrımını yap.
- [ ] Veritabanı taslak şemasını çıkar.

Çıktı:

- Proje kapsam dokümanı
- Veritabanı taslağı
- Modül listesi
- MVP planı

---

# FAZ 1 — Laravel Kurulumu

Amaç: Projenin temiz Laravel iskeletini kurmak.

- [ ] Yeni Laravel projesi oluştur.
- [ ] `.env` ayarlarını yap.
- [ ] MySQL/MariaDB bağlantısını ayarla.
- [ ] Locale değerini `tr` yap.
- [ ] Timezone değerini `Europe/Istanbul` yap.
- [ ] Charset/collation ayarlarını `utf8mb4` yap.
- [ ] Storage link oluştur.
- [ ] Auth altyapısını kur.
- [ ] Admin kullanıcı seed dosyası oluştur.
- [ ] İlk migration testini çalıştır.
- [ ] İlk commit al.

Çıktı:

- Çalışan Laravel projesi
- Veritabanı bağlantısı
- Türkçe uyumlu temel yapı

---

# FAZ 2 — Filament Admin Panel Kurulumu

Amaç: Yönetim panelini kurmak ve Türkçeleştirmek.

- [ ] Filament kurulumu yap.
- [ ] Admin giriş ekranını oluştur.
- [ ] Panel dilini Türkçe yap.
- [ ] Panel renklerini Lav Çiçekçilik marka kimliğine göre ayarla.
- [ ] Dashboard sayfası oluştur.
- [ ] Panel menü gruplarını oluştur.
- [ ] Rol/yetki altyapısını planla.
- [ ] Süper admin kullanıcısı oluştur.
- [ ] Admin profil ekranı ekle.
- [ ] Panelde Türkçe karakter testi yap.

Çıktı:

- Çalışan Türkçe Filament panel
- Admin girişi
- Temel dashboard

---

# FAZ 3 — Özel Mini E-Ticaret Veritabanı Çekirdeği

Amaç: Hazır e-ticaret paketi kullanmadan temel tabloları oluşturmak.

Ana tablolar:

```txt
users
settings
media
menus
menu_items
pages
page_blocks
categories
products
product_images
product_options
product_option_values
customers
customer_addresses
favorites
carts
cart_items
orders
order_items
order_status_histories
payment_transactions
delivery_zones
delivery_neighborhoods
delivery_slots
coupons
coupon_usages
campaigns
couriers
order_delivery_assignments
order_approval_images
seo_pages
blog_categories
blog_posts
notification_templates
activity_logs
```

- [ ] Tüm migration dosyalarını oluştur.
- [ ] Model ilişkilerini tanımla.
- [ ] Enum sınıflarını oluştur.
- [ ] Soft delete gereken modelleri belirle.
- [ ] Slug alanlarına unique index ekle.
- [ ] Para alanlarını decimal olarak tut.
- [ ] Sipariş numarası için unique yapı kur.
- [ ] SEO alanları için ortak trait oluştur.
- [ ] Görsel alanları için media mantığı oluştur.
- [ ] Test seed verilerini oluştur.

Çıktı:

- Çalışan özel e-ticaret çekirdeği
- Model ilişkileri
- Temel seed verileri

---

# FAZ 4 — Site Ayarları Modülü

Amaç: Sitenin genel ayarlarını panelden yönetmek.

Yönetilecek alanlar:

- Site adı
- Logo
- Mobil logo
- Favicon
- Telefon
- WhatsApp
- E-posta
- Adres
- Çalışma saatleri
- Sosyal medya linkleri
- Google Analytics
- Meta Pixel
- Search Console doğrulama kodu
- Varsayılan SEO başlığı
- Varsayılan SEO açıklaması
- Bakım modu
- Sipariş alma durumu
- Minimum sipariş tutarı
- Ücretsiz teslimat limiti
- Aynı gün teslimat aktif/pasif

- [ ] `settings` tablosunu oluştur.
- [ ] Key-value veya grouped JSON yapı tasarla.
- [ ] Filament ayar sayfası oluştur.
- [ ] Logo ve favicon yükleme alanlarını ekle.
- [ ] İletişim bilgilerini ekle.
- [ ] Sosyal medya alanlarını ekle.
- [ ] SEO varsayılan alanlarını ekle.
- [ ] Kod ekleme alanlarını güvenli hale getir.
- [ ] Ayarları cache’le.
- [ ] Frontend layout içinde ayarları kullan.

Çıktı:

- Panelden yönetilebilir genel ayarlar

---

# FAZ 5 — Header, Menü ve Mega Menü Yönetimi

Amaç: Header alanını tamamen panelden yönetilebilir yapmak.

Header özellikleri:

- Kampanya üst barı
- Logo
- Arama alanı
- Telefon/WhatsApp
- Kullanıcı hesabı
- Favoriler
- Sepet
- Ana menü
- Kategori menüsü
- Mega menü
- Mobil menü

- [ ] `menus` ve `menu_items` tablolarını oluştur.
- [ ] Menü öğelerinde sıra, aktiflik, ikon, URL, hedef alanlarını ekle.
- [ ] Header üst bar ayarlarını panele ekle.
- [ ] Logo alanını dinamik yap.
- [ ] Kategori menüsünü dinamik oluştur.
- [ ] Mega menü desteği ekle.
- [ ] Mobil menü oluştur.
- [ ] Header cache sistemi kur.
- [ ] Header’daki tüm metinleri panelden yönetilebilir yap.

Çıktı:

- Dinamik header
- Dinamik menü
- Mobil menü

---

# FAZ 6 — Footer Yönetimi

Amaç: Footer alanını panelden yönetmek.

Footer alanları:

- Logo
- Kısa açıklama
- Telefon
- WhatsApp
- E-posta
- Adres
- Hızlı linkler
- Kategoriler
- Kurumsal sayfalar
- Sosyal medya ikonları
- KVKK / Gizlilik / Mesafeli Satış / İade linkleri
- Copyright metni

- [ ] Footer ayarlarını panele ekle.
- [ ] Footer menü alanlarını oluştur.
- [ ] Footer kategori listesini dinamik yap.
- [ ] Sosyal medya ikonlarını dinamik yap.
- [ ] Footer metinlerini panelden düzenlenebilir yap.
- [ ] Mobil footer görünümünü optimize et.

Çıktı:

- Dinamik footer

---

# FAZ 7 — Sayfa ve Blok Yönetim Sistemi

Amaç: Ana sayfa ve kurumsal sayfaları panelden blok mantığıyla yönetmek.

Blok tipleri:

```txt
hero_slider
category_grid
product_carousel
featured_product
campaign_banner
special_day_banner
seo_text
faq
testimonials
trust_badges
delivery_info
blog_posts
whatsapp_cta
image_text
rich_text
html_block
```

- [ ] `pages` tablosunu oluştur.
- [ ] `page_blocks` tablosunu oluştur.
- [ ] Blok tipleri için enum oluştur.
- [ ] Bloklarda sıra, aktiflik, başlık, içerik, görsel ve ayar alanlarını oluştur.
- [ ] JSON settings yapısını tasarla.
- [ ] Filament Page Resource oluştur.
- [ ] Blok ekle/sil/sırala sistemi oluştur.
- [ ] Blok önizleme alanı ekle.
- [ ] Frontend blok render sistemi oluştur.
- [ ] Her blok için Blade partial dosyası oluştur.
- [ ] Blok cache yapısı kur.

Çıktı:

- Panelden yönetilebilir blok tabanlı sayfa sistemi

---

# FAZ 8 — Kategori Yönetimi

Amaç: Ürün kategorilerini SEO uyumlu şekilde yönetmek.

Kategori alanları:

- Başlık
- Slug
- Üst kategori
- Kısa açıklama
- Uzun açıklama
- Görsel
- Banner
- İkon
- Sıra
- Aktiflik
- Menüde göster
- Ana sayfada göster
- SEO başlığı
- SEO açıklaması
- Schema ayarları

- [ ] Category modelini oluştur.
- [ ] Parent-child kategori desteği ekle.
- [ ] Filament Category Resource oluştur.
- [ ] Görsel yükleme alanı ekle.
- [ ] Slug otomatik üretimini yap.
- [ ] SEO alanlarını ekle.
- [ ] Kategori listeleme sayfasını oluştur.
- [ ] Kategori detay sayfasını oluştur.
- [ ] Breadcrumb yapısını ekle.
- [ ] Kategori schema markup ekle.

Çıktı:

- SEO uyumlu kategori yönetimi

---

# FAZ 9 — Ürün Yönetimi

Amaç: Çiçekçilik ürünlerini detaylı yönetmek.

Ürün alanları:

- Ürün adı
- Slug
- SKU
- Kategori
- Kısa açıklama
- Uzun açıklama
- Ürün içeriği
- Bakım önerisi
- Teslimat bilgisi
- Ana fiyat
- İndirimli fiyat
- Stok durumu
- Stok adedi
- Ürün görselleri
- Öne çıkan ürün
- Haftanın ürünü
- Çok satan
- Yeni ürün
- Aynı gün teslimat
- Ücretsiz teslimat
- Kişiye özel not destekler
- SEO başlığı
- SEO açıklaması
- Schema verileri

- [ ] Product modelini oluştur.
- [ ] ProductImage modelini oluştur.
- [ ] Filament Product Resource oluştur.
- [ ] Çoklu görsel yükleme sistemi ekle.
- [ ] Ürün fiyat alanlarını oluştur.
- [ ] Ürün etiket sistemi ekle.
- [ ] Ürün durum enumlarını oluştur.
- [ ] Stok alanlarını ekle.
- [ ] SEO alanlarını ekle.
- [ ] Ürün detay frontend sayfasını oluştur.
- [ ] Ürün kart componentini oluştur.
- [ ] Ürün listeleme componentini oluştur.
- [ ] Ürün arama sistemini oluştur.

Çıktı:

- Yönetilebilir ürün sistemi
- SEO uyumlu ürün detay sayfası

---

# FAZ 10 — Ürün Seçenekleri

Amaç: Ürünlere ek seçenekler tanımlamak.

Ek seçenek örnekleri:

- Küçük / Orta / Büyük boy
- Ekstra çikolata
- Ekstra ayıcık
- Ekstra vazo
- Ekstra balon
- Görsel onay istiyorum
- VIP teslimat
- Hızlı teslimat

- [ ] `product_options` tablosunu oluştur.
- [ ] `product_option_values` tablosunu oluştur.
- [ ] Fiyat etkisi alanı ekle.
- [ ] Zorunlu/opsiyonel alan desteği ekle.
- [ ] Filament option yönetimi oluştur.
- [ ] Ürün detayında seçenek seçim arayüzü yap.
- [ ] Sepete seçeneklerle ekleme yapısını kur.
- [ ] Sipariş kalemlerinde seçilen seçenekleri sakla.

Çıktı:

- Ürün ek seçenek sistemi

---

# FAZ 11 — Müşteri ve Üyelik Sistemi

Amaç: Üyelik, misafir sipariş ve müşteri paneli kurmak.

- [ ] Customer modelini oluştur.
- [ ] Müşteri auth yapısını kur.
- [ ] Misafir sipariş desteği ekle.
- [ ] Adres defteri tablosunu oluştur.
- [ ] Favoriler tablosunu oluştur.
- [ ] Müşteri paneli sayfalarını oluştur.
- [ ] Sipariş takip sayfası oluştur.
- [ ] Şifremi unuttum akışını oluştur.
- [ ] KVKK onay kayıtlarını sakla.
- [ ] Filament müşteri yönetimi oluştur.

Çıktı:

- Üyelik sistemi
- Misafir sipariş
- Sipariş takip

---

# FAZ 12 — Sepet Sistemi

Amaç: Ürün, seçenek, teslimat ve kupon destekli sepet sistemi kurmak.

Sepette tutulacak bilgiler:

- Ürün
- Adet
- Seçenekler
- Kart notu
- Alıcı adı
- Alıcı telefonu
- Teslimat adresi
- Teslimat ilçesi
- Teslimat mahallesi
- Teslimat tarihi
- Teslimat saat aralığı
- Teslimat ücreti
- Kupon
- Ara toplam
- Genel toplam

- [ ] Cart modelini oluştur.
- [ ] CartItem modelini oluştur.
- [ ] Session tabanlı sepet yapısını kur.
- [ ] Üye kullanıcı sepet ilişkisini destekle.
- [ ] Sepete ekleme endpointi oluştur.
- [ ] Sepetten silme endpointi oluştur.
- [ ] Sepet güncelleme endpointi oluştur.
- [ ] Mini sepet oluştur.
- [ ] Sepet sayfasını oluştur.
- [ ] CartService yaz.
- [ ] Teslimat ücretini sepete dahil et.
- [ ] Kupon hesaplamasını sepete dahil et.

Çıktı:

- Çalışan sepet sistemi

---

# FAZ 13 — Teslimat Bölgesi ve Saat Yönetimi

Amaç: İlçe/mahalle bazlı teslimat sistemi oluşturmak.

Alanlar:

- İl
- İlçe
- Mahalle
- Teslimat ücreti
- Ücretsiz teslimat limiti
- Aynı gün teslimat aktif/pasif
- Cutoff saati
- Minimum sipariş tutarı
- Teslimat saat aralıkları
- Saat aralığı kapasitesi
- Kapalı günler
- Özel gün yoğunluk ayarları

- [ ] `delivery_zones` tablosunu oluştur.
- [ ] `delivery_neighborhoods` tablosunu oluştur.
- [ ] `delivery_slots` tablosunu oluştur.
- [ ] Filament teslimat bölgesi yönetimi oluştur.
- [ ] Mahalle yönetimi oluştur.
- [ ] Saat aralığı yönetimi oluştur.
- [ ] DeliveryService yaz.
- [ ] Aynı gün teslimat cutoff kontrolü ekle.
- [ ] Kapalı gün kontrolü ekle.
- [ ] Saat kapasite kontrolü ekle.
- [ ] Checkout ekranında teslimat seçimi oluştur.

Çıktı:

- Dinamik teslimat sistemi

---

# FAZ 14 — Checkout Sistemi

Amaç: Hızlı ve hatasız ödeme öncesi sipariş akışı oluşturmak.

Checkout adımları:

1. Sepet özeti
2. Gönderen bilgileri
3. Alıcı bilgileri
4. Teslimat adresi
5. Teslimat tarihi ve saati
6. Kart notu
7. Ek seçenekler
8. Fatura bilgileri
9. Ödeme yöntemi
10. Sipariş onayı

- [ ] Checkout controller oluştur.
- [ ] Checkout request validasyonları oluştur.
- [ ] Tek sayfa veya çok adımlı yapı oluştur.
- [ ] Gönderen bilgisi formu oluştur.
- [ ] Alıcı bilgisi formu oluştur.
- [ ] Teslimat adres formu oluştur.
- [ ] Teslimat tarih/saat formu oluştur.
- [ ] Kart notu alanı oluştur.
- [ ] Fatura bilgileri alanı oluştur.
- [ ] KVKK ve mesafeli satış onayı ekle.
- [ ] CheckoutService yaz.
- [ ] Sipariş özeti oluştur.

Çıktı:

- Çalışan checkout akışı

---

# FAZ 15 — Sipariş Sistemi

Amaç: Sipariş yaşam döngüsünü yönetmek.

Sipariş durumları:

```txt
pending_payment
payment_failed
paid
preparing
approval_waiting
approved
assigned_to_courier
on_delivery
delivered
cancelled
refunded
```

- [ ] Order modelini oluştur.
- [ ] OrderItem modelini oluştur.
- [ ] OrderStatusHistory modelini oluştur.
- [ ] Sipariş numarası üretim servisi yaz.
- [ ] Sipariş toplam hesaplama servisi yaz.
- [ ] Sipariş durum enumlarını oluştur.
- [ ] Filament Order Resource oluştur.
- [ ] Sipariş detay ekranı oluştur.
- [ ] Sipariş durum değiştirme aksiyonları ekle.
- [ ] Sipariş geçmişi kayıt sistemi ekle.
- [ ] Siparişe admin notu ekleme sistemi oluştur.
- [ ] Müşteri sipariş takip sayfası oluştur.

Çıktı:

- Sipariş yönetimi
- Durum geçmişi

---

# FAZ 16 — iyzico Ödeme Entegrasyonu

Amaç: Güvenli ödeme alma altyapısı kurmak.

- [ ] iyzico ayarlarını panele ekle.
- [ ] Sandbox/production modu ekle.
- [ ] API key, secret key ve base URL ayarlarını güvenli sakla.
- [ ] IyzicoPaymentService oluştur.
- [ ] Ödeme başlatma endpointi oluştur.
- [ ] Başarılı ödeme callback endpointi oluştur.
- [ ] Başarısız ödeme callback endpointi oluştur.
- [ ] Ödeme sonucunu `payment_transactions` tablosuna yaz.
- [ ] Başarılı ödemede siparişi `paid` yap.
- [ ] Başarısız ödemede siparişi `payment_failed` yap.
- [ ] Ödeme loglarını sakla.
- [ ] Sandbox test kartları ile ödeme testi yap.
- [ ] Production geçiş dokümanı hazırla.

Çıktı:

- Çalışan iyzico ödeme sistemi

---

# FAZ 17 — Görsel Onay Sistemi

Amaç: Hazırlanan çiçek görselini müşteriye onaylatmak.

Akış:

1. Sipariş hazırlanır.
2. Admin/personel ürün görseli yükler.
3. Müşteriye onay linki gönderilir.
4. Müşteri onaylar veya revize ister.
5. Sipariş durumu güncellenir.

- [ ] `order_approval_images` tablosunu oluştur.
- [ ] Sipariş detayına görsel yükleme alanı ekle.
- [ ] Token bazlı onay linki üret.
- [ ] Müşteri onay sayfası oluştur.
- [ ] Onayla butonu ekle.
- [ ] Revize istiyorum alanı ekle.
- [ ] Onay/revize durumlarını siparişe işle.
- [ ] Admin panelde onay geçmişini göster.

Çıktı:

- Görsel onay modülü

---

# FAZ 18 — Kurye ve QR Teslimat Sistemi

Amaç: Siparişleri kuryeye atamak ve QR ile teslimat doğrulamak.

- [ ] Courier modelini oluştur.
- [ ] `order_delivery_assignments` tablosunu oluştur.
- [ ] Filament kurye yönetimi oluştur.
- [ ] Sipariş detayına kurye atama aksiyonu ekle.
- [ ] Basit kurye mobil ekranı oluştur.
- [ ] QR kod üretim servisi oluştur.
- [ ] QR doğrulama endpointi oluştur.
- [ ] Teslim edildi butonu oluştur.
- [ ] Teslimat zamanını kaydet.
- [ ] Teslimat notu alanı ekle.
- [ ] Sipariş durumunu `delivered` yap.

Çıktı:

- Kurye atama
- QR teslimat doğrulama

---

# FAZ 19 — Bildirim Sistemi

Amaç: Sipariş durumlarına göre bildirim göndermek.

Kanallar:

- E-posta
- SMS altyapısına hazır yapı
- WhatsApp link/metin üretimi
- Panel içi bildirim
- Telegram opsiyonel

- [ ] NotificationService oluştur.
- [ ] Bildirim şablonları tablosu oluştur.
- [ ] Filament bildirim şablonu yönetimi oluştur.
- [ ] E-posta gönderimini yapılandır.
- [ ] WhatsApp mesaj linki üretme sistemi kur.
- [ ] SMS sağlayıcı için soyut yapı kur.
- [ ] Sipariş durumlarına bildirim tetikleyici ekle.
- [ ] Bildirim loglarını sakla.

Çıktı:

- Bildirim altyapısı

---

# FAZ 20 — Kupon ve Kampanya Sistemi

Amaç: İndirim ve kampanya yönetimi kurmak.

Kupon tipleri:

- Sabit tutar indirim
- Yüzde indirim
- Kategori bazlı indirim
- Ürün bazlı indirim
- Minimum sepet tutarı
- Tek kullanımlık kupon
- Müşteriye özel kupon

- [ ] Coupon modelini oluştur.
- [ ] Campaign modelini oluştur.
- [ ] Filament kupon yönetimi oluştur.
- [ ] Filament kampanya yönetimi oluştur.
- [ ] Kupon validasyon servisi yaz.
- [ ] Kupon kullanım geçmişini sakla.
- [ ] Sepette kupon uygulama alanı yap.
- [ ] Kampanya banner bloklarını bağla.
- [ ] Ürün kartlarında indirim rozetlerini göster.

Çıktı:

- Kupon ve kampanya sistemi

---

# FAZ 21 — SEO Sistemi

Amaç: Tüm sayfa türlerini SEO uyumlu yapmak.

SEO özellikleri:

- SEO başlığı
- SEO açıklaması
- Canonical URL
- Open Graph
- Twitter Card
- Schema markup
- Sitemap
- Robots.txt
- Breadcrumb
- Bölgesel SEO sayfaları
- Özel gün SEO sayfaları

- [ ] SEO trait oluştur.
- [ ] Ürün SEO alanlarını bağla.
- [ ] Kategori SEO alanlarını bağla.
- [ ] Sayfa SEO alanlarını bağla.
- [ ] Blog SEO alanlarını bağla.
- [ ] Bölgesel SEO sayfa modelini oluştur.
- [ ] Sitemap generator oluştur.
- [ ] Robots.txt endpointi oluştur.
- [ ] Breadcrumb componenti oluştur.
- [ ] Product schema ekle.
- [ ] LocalBusiness schema ekle.
- [ ] Organization schema ekle.
- [ ] FAQ schema ekle.
- [ ] Meta component oluştur.

Bölgesel SEO örnekleri:

```txt
Diyarbakır çiçekçi
Diyarbakır online çiçek siparişi
Kayapınar çiçekçi
Sur çiçekçi
Bağlar çiçekçi
Yenişehir çiçekçi
Diyarbakır aynı gün çiçek teslimatı
Diyarbakır doğum günü çiçeği
Diyarbakır sevgiliye çiçek
Diyarbakır açılış çelengi
```

Çıktı:

- SEO uyumlu site altyapısı

---

# FAZ 22 — Blog ve İçerik Sistemi

Amaç: SEO için blog ve içerik yönetimi kurmak.

- [ ] BlogCategory modelini oluştur.
- [ ] BlogPost modelini oluştur.
- [ ] Filament blog kategori yönetimi oluştur.
- [ ] Filament blog yazı yönetimi oluştur.
- [ ] WYSIWYG editör ekle.
- [ ] Blog görsel yükleme alanı ekle.
- [ ] Blog SEO alanlarını ekle.
- [ ] Blog liste sayfası oluştur.
- [ ] Blog detay sayfası oluştur.
- [ ] Blogları ana sayfa bloğuna bağla.
- [ ] Article schema ekle.

Çıktı:

- Blog sistemi

---

# FAZ 23 — Arama ve Filtreleme Sistemi

Amaç: Kullanıcıların ürünleri hızlı bulmasını sağlamak.

- [ ] Ürün arama endpointi oluştur.
- [ ] Header canlı arama alanı oluştur.
- [ ] Kategori sayfasında filtreleme yap.
- [ ] Fiyat aralığı filtresi ekle.
- [ ] Kategori filtresi ekle.
- [ ] Ürün etiketi filtresi ekle.
- [ ] Sıralama seçenekleri ekle.
- [ ] Arama sonuç sayfası oluştur.
- [ ] Boş sonuç ekranı tasarla.

Çıktı:

- Arama ve filtreleme sistemi

---

# FAZ 24 — Frontend Tasarım ve Sayfalar

Amaç: Lav Çiçekçilik’e özel hızlı, modern ve mobil uyumlu frontend oluşturmak.

Sayfalar:

- Ana sayfa
- Kategori sayfası
- Ürün detay sayfası
- Sepet sayfası
- Checkout sayfası
- Sipariş başarılı sayfası
- Sipariş takip sayfası
- Müşteri hesabı
- Kurumsal sayfalar
- Blog liste
- Blog detay
- Bölgesel SEO sayfaları
- İletişim sayfası
- 404 sayfası

- [ ] Ana layout oluştur.
- [ ] Header component oluştur.
- [ ] Footer component oluştur.
- [ ] Ürün kart componenti oluştur.
- [ ] Kategori kart componenti oluştur.
- [ ] Breadcrumb componenti oluştur.
- [ ] Blok componentleri oluştur.
- [ ] Mobil menü oluştur.
- [ ] Sepet drawer oluştur.
- [ ] WhatsApp floating button oluştur.
- [ ] Responsive grid sistemi kur.
- [ ] Tailwind config dosyasını marka renklerine göre ayarla.
- [ ] Font optimizasyonu yap.
- [ ] Görsel lazy-load uygula.

Çıktı:

- Tam frontend tema

---

# FAZ 25 — Dashboard ve Raporlama

Amaç: Yönetici için özet ve raporlama ekranları oluşturmak.

Dashboard kartları:

- Bugünkü sipariş sayısı
- Bugünkü ciro
- Bekleyen siparişler
- Hazırlanan siparişler
- Görsel onay bekleyenler
- Kuryedeki siparişler
- Teslim edilen siparişler
- En çok satan ürünler
- Stok uyarıları
- Ödeme hataları

- [ ] Dashboard widgetları oluştur.
- [ ] Sipariş istatistiklerini hesapla.
- [ ] Ciro istatistiklerini hesapla.
- [ ] Ürün satış raporu oluştur.
- [ ] Teslimat raporu oluştur.
- [ ] Ödeme raporu oluştur.
- [ ] Tarih aralığı filtresi ekle.
- [ ] CSV/Excel export opsiyonu ekle.

Çıktı:

- Yönetici dashboard
- Temel raporlama

---

# FAZ 26 — Veri Aktarımı ve Demo İçerikler

Amaç: Mevcut site içeriklerini yeni sisteme aktarmak.

- [ ] Mevcut kategori listesini çıkar.
- [ ] Mevcut ürün listesini çıkar.
- [ ] Ürün adlarını düzenle.
- [ ] Ürün açıklamalarını SEO uyumlu hale getir.
- [ ] Ürün görsellerini optimize et.
- [ ] Görselleri WebP formatına dönüştür.
- [ ] Kategori açıklamalarını oluştur.
- [ ] Ana sayfa bloklarını demo içerikle doldur.
- [ ] Kurumsal sayfaları oluştur.
- [ ] SEO sayfaları için demo içerikler ekle.
- [ ] Test siparişleri oluştur.

Çıktı:

- Demo içerikleri hazır sistem

---

# FAZ 27 — Test Süreci

Amaç: Yayın öncesi tüm akışları test etmek.

- [ ] Ana sayfa açılıyor mu?
- [ ] Header/footer dinamik çalışıyor mu?
- [ ] Kategori sayfası açılıyor mu?
- [ ] Ürün detay sayfası açılıyor mu?
- [ ] Ürün sepete ekleniyor mu?
- [ ] Ürün seçenekleri sepete yansıyor mu?
- [ ] Teslimat ilçesi seçiliyor mu?
- [ ] Teslimat mahallesi seçiliyor mu?
- [ ] Teslimat saati seçiliyor mu?
- [ ] Teslimat ücreti doğru hesaplanıyor mu?
- [ ] Kupon çalışıyor mu?
- [ ] Sipariş oluşturuluyor mu?
- [ ] iyzico ödeme çalışıyor mu?
- [ ] Başarılı ödeme siparişi güncelliyor mu?
- [ ] Başarısız ödeme doğru yönetiliyor mu?
- [ ] Admin siparişi görüyor mu?
- [ ] Görsel onay linki çalışıyor mu?
- [ ] Kurye atama çalışıyor mu?
- [ ] QR teslimat doğrulama çalışıyor mu?
- [ ] E-posta bildirimi gidiyor mu?
- [ ] WhatsApp mesaj metni doğru mu?
- [ ] SEO meta alanları doğru mu?
- [ ] Sitemap üretiliyor mu?
- [ ] Mobil uyumluluk doğru mu?
- [ ] Türkçe karakter hatası var mı?
- [ ] Form validasyonları Türkçe mi?
- [ ] PageSpeed hedefleri yakalanıyor mu?

Çıktı:

- Test raporu
- Hata düzeltmeleri

---

# FAZ 28 — Yayına Alma

Amaç: Projeyi canlı sunucuya almak.

- [ ] Production `.env` dosyasını oluştur.
- [ ] `APP_DEBUG=false` yap.
- [ ] Veritabanı production ayarlarını yap.
- [ ] Storage link oluştur.
- [ ] Cache temizle.
- [ ] Config cache oluştur.
- [ ] Route cache oluştur.
- [ ] View cache oluştur.
- [ ] Queue ayarlarını yap.
- [ ] Cron ayarlarını yap.
- [ ] SSL kontrolü yap.
- [ ] Domain yönlendirmesini yap.
- [ ] iyzico production bilgilerini gir.
- [ ] Test ödeme yap.
- [ ] Sitemap gönder.
- [ ] Search Console ayarlarını yap.
- [ ] Analytics/Pixel kontrol et.
- [ ] Final yedek al.

Çıktı:

- Canlı çalışan web sitesi

---

# FAZ 29 — Teslim Dokümanları

Amaç: Proje sonunda teknik ve kullanım dokümanlarını teslim etmek.

- [ ] Kaynak kodu teslim et.
- [ ] Migration dosyalarını teslim et.
- [ ] Seeder dosyalarını teslim et.
- [ ] Admin panel giriş bilgilerini hazırla.
- [ ] Kurulum dokümanı yaz.
- [ ] Yayına alma dokümanı yaz.
- [ ] Panel kullanım dokümanı yaz.
- [ ] Ürün ekleme dokümanı yaz.
- [ ] Sipariş yönetimi dokümanı yaz.
- [ ] Teslimat bölgesi yönetimi dokümanı yaz.
- [ ] iyzico ayar dokümanı yaz.
- [ ] SEO yönetimi dokümanı yaz.
- [ ] Backup alma dokümanı yaz.

Çıktı:

- Final teslim paketi

---

## 5. MVP Kapsamı

İlk yayına alınacak sürümde mutlaka olacaklar:

- [ ] Laravel kurulumu
- [ ] Filament panel
- [ ] Site ayarları
- [ ] Header/footer yönetimi
- [ ] Sayfa blok sistemi
- [ ] Kategori yönetimi
- [ ] Ürün yönetimi
- [ ] Ürün görselleri
- [ ] Ürün seçenekleri
- [ ] Sepet
- [ ] Checkout
- [ ] Teslimat ilçe/mahalle/tarih/saat seçimi
- [ ] Sipariş oluşturma
- [ ] iyzico ödeme
- [ ] Sipariş yönetimi
- [ ] Temel bildirimler
- [ ] SEO meta alanları
- [ ] Sitemap
- [ ] Blog
- [ ] Mobil uyumlu frontend
- [ ] Temel hız optimizasyonu

İkinci faza bırakılabilecekler:

- [ ] Gelişmiş kurye paneli
- [ ] QR teslimat doğrulama
- [ ] Görsel onay sistemi
- [ ] SMS entegrasyonu
- [ ] Telegram bot
- [ ] Gelişmiş raporlama
- [ ] Bölgesel SEO otomasyonları
- [ ] Yapay zekâ ürün açıklaması
- [ ] E-fatura entegrasyonu
- [ ] Çoklu alıcıya tek ödeme

---

## 6. Önerilen Klasör Yapısı

```txt
app/
├── Actions/
│   ├── Cart/
│   ├── Checkout/
│   ├── Orders/
│   ├── Payments/
│   └── Delivery/
├── Enums/
├── Filament/
│   ├── Resources/
│   ├── Pages/
│   └── Widgets/
├── Http/
│   ├── Controllers/
│   │   ├── Frontend/
│   │   ├── Checkout/
│   │   └── Webhooks/
│   └── Requests/
├── Models/
├── Observers/
├── Services/
│   ├── CartService.php
│   ├── CheckoutService.php
│   ├── DeliveryService.php
│   ├── IyzicoPaymentService.php
│   ├── SeoService.php
│   └── NotificationService.php
└── Support/

resources/
├── views/
│   ├── frontend/
│   │   ├── layouts/
│   │   ├── components/
│   │   ├── blocks/
│   │   ├── pages/
│   │   ├── product/
│   │   ├── category/
│   │   ├── cart/
│   │   └── checkout/
│   └── emails/
├── css/
└── js/

database/
├── migrations/
├── seeders/
└── factories/
```

---

## 7. Kritik Servisler

### CartService

- Sepet oluşturma
- Sepete ürün ekleme
- Sepetten ürün silme
- Sepet güncelleme
- Ürün seçenek fiyatlarını hesaplama
- Kupon hesaplama
- Teslimat ücreti hesaplama
- Genel toplam döndürme

### CheckoutService

- Checkout validasyonu
- Sipariş oluşturma
- OrderItem kayıtları
- Teslimat bilgilerini siparişe yazma
- Ödeme başlatma
- Sipariş durumunu ayarlama

### DeliveryService

- İlçe/mahalle kontrolü
- Teslimat ücreti hesaplama
- Teslimat saat aralığı kontrolü
- Aynı gün teslimat kontrolü
- Kapalı gün kontrolü
- Kapasite kontrolü

### IyzicoPaymentService

- Ödeme isteği oluşturma
- Callback sonucunu doğrulama
- Transaction kaydı oluşturma
- Sipariş durumunu güncelleme
- Hata loglama

### SeoService

- Meta başlık üretme
- Meta açıklama üretme
- Canonical URL üretme
- Schema markup üretme
- Sitemap üretme

### NotificationService

- Sipariş durumuna göre bildirim gönderme
- E-posta şablonu kullanma
- WhatsApp mesaj metni üretme
- SMS sağlayıcı entegrasyonuna hazır yapı oluşturma
- Bildirim loglama

---

## 8. Admin Panel Menü Yapısı

```txt
Dashboard

Site Yönetimi
- Genel Ayarlar
- Header Yönetimi
- Footer Yönetimi
- Menü Yönetimi
- Sayfalar
- Ana Sayfa Blokları

Katalog
- Kategoriler
- Ürünler
- Ürün Seçenekleri
- Ürün Etiketleri
- Toplu Ürün İşlemleri

Siparişler
- Tüm Siparişler
- Ödeme Bekleyenler
- Hazırlananlar
- Görsel Onay Bekleyenler
- Kuryedekiler
- Teslim Edilenler
- İptal Edilenler

Teslimat
- Teslimat Bölgeleri
- Mahalleler
- Teslimat Saatleri
- Kuryeler
- Kurye Atamaları

Pazarlama
- Kuponlar
- Kampanyalar
- Bannerlar
- Özel Günler

SEO & İçerik
- SEO Sayfaları
- Blog Kategorileri
- Blog Yazıları
- Sitemap Yönetimi

Müşteriler
- Üyeler
- Adresler
- Favoriler

Sistem
- Bildirim Şablonları
- Ödeme Ayarları
- iyzico Ayarları
- SMS/WhatsApp Ayarları
- Kullanıcılar
- Roller
- Loglar
```

---

## 9. Frontend Sayfa Akışı

### Ana Sayfa

- Kampanya üst bar
- Header
- Hero slider
- Kategori vitrinleri
- Öne çıkan ürünler
- Haftanın ürünü
- Özel gün bannerı
- Çok satanlar
- Aynı gün teslimat vurgusu
- SEO açıklama alanı
- Blog yazıları
- SSS
- Footer

### Ürün Detay

- Ürün görsel galerisi
- Ürün adı
- Fiyat
- İndirimli fiyat
- Ürün açıklaması
- Ürün içeriği
- Teslimat bilgisi
- Ek seçenekler
- Adet seçimi
- Sepete ekle
- Hemen al
- Kart notu bilgisi
- Aynı gün teslimat rozeti
- SEO metni
- Benzer ürünler

### Checkout

- Sepet özeti
- Gönderen bilgileri
- Alıcı bilgileri
- Teslimat adresi
- Teslimat tarihi
- Teslimat saat aralığı
- Kart notu
- Fatura bilgileri
- KVKK / Mesafeli satış onayı
- iyzico ödeme

---

## 10. Hız Optimizasyonu Kuralları

- [ ] Gereksiz JS kullanılmayacak.
- [ ] Ağır slider yapılarından kaçınılacak.
- [ ] Ana sayfada çok fazla ürün aynı anda yüklenmeyecek.
- [ ] Ürün görselleri WebP olacak.
- [ ] Lazy-load uygulanacak.
- [ ] Font sayısı maksimum 2 olacak.
- [ ] CSS minify edilecek.
- [ ] JS minify edilecek.
- [ ] Menü ve ayarlar cache’lenecek.
- [ ] Blok çıktıları cache’lenecek.
- [ ] Sitemap cache üretilecek.
- [ ] Gereksiz composer/npm paketi eklenmeyecek.
- [ ] Veritabanı sorgularında eager loading kullanılacak.
- [ ] N+1 sorgu problemleri giderilecek.
- [ ] Görsel yükleme sırasında otomatik resize yapılacak.

---

## 11. SEO Kuralları

- [ ] Her ürünün benzersiz SEO başlığı olacak.
- [ ] Her ürünün benzersiz meta açıklaması olacak.
- [ ] Her kategori SEO açıklaması içerecek.
- [ ] Her sayfa canonical URL içerecek.
- [ ] Sitemap otomatik üretilecek.
- [ ] Robots.txt oluşturulacak.
- [ ] Ürünlerde Product schema olacak.
- [ ] İşletme için LocalBusiness schema olacak.
- [ ] Breadcrumb schema olacak.
- [ ] Blog yazılarında Article schema olacak.
- [ ] SSS bloklarında FAQ schema olacak.
- [ ] URL yapıları sade olacak.
- [ ] Türkçe karakterli başlıklardan temiz slug üretilecek.

---

## 12. Kabul Kriterleri

Proje bitmiş sayılması için şu maddeler sağlanmalıdır:

- [ ] Site ana sayfası sorunsuz açılıyor.
- [ ] Header panelden düzenlenebiliyor.
- [ ] Footer panelden düzenlenebiliyor.
- [ ] Ana sayfa blokları panelden eklenip kaldırılabiliyor.
- [ ] Ürün ekleme/düzenleme çalışıyor.
- [ ] Kategori ekleme/düzenleme çalışıyor.
- [ ] Ürün sepete ekleniyor.
- [ ] Sepet toplamı doğru hesaplanıyor.
- [ ] Teslimat ücreti doğru hesaplanıyor.
- [ ] Teslimat tarihi ve saat aralığı seçiliyor.
- [ ] Sipariş oluşturuluyor.
- [ ] iyzico ödeme çalışıyor.
- [ ] Başarılı ödeme sipariş durumunu güncelliyor.
- [ ] Admin panelde sipariş görüntüleniyor.
- [ ] Müşteri sipariş takip yapabiliyor.
- [ ] SEO meta alanları çalışıyor.
- [ ] Sitemap üretiliyor.
- [ ] Mobil görünüm sorunsuz.
- [ ] Türkçe karakter hatası yok.
- [ ] PageSpeed hedeflerine yakın sonuç alınıyor.
- [ ] Tüm kritik formlarda validasyon var.
- [ ] Yayına alma dokümanı var.
- [ ] Panel kullanım dokümanı var.

---

## 13. Ajan İçin Çalışma Talimatı

Ajan aşağıdaki kurallara uymalıdır:

1. Her fazı sırayla tamamla.
2. Bir faz bitmeden sonraki faza geçme.
3. Her faz sonunda çalışır kod bırak.
4. Her faz sonunda kısa test yap.
5. Türkçe karakter uyumunu sürekli kontrol et.
6. Gereksiz paket kurma.
7. Hazır e-ticaret sistemi kullanma.
8. Kodları sade, okunabilir ve Laravel standartlarına uygun yaz.
9. Panelde tüm etiketleri Türkçe yaz.
10. Frontend’de SEO ve hız öncelikli davran.
11. Ödeme, sipariş ve teslimat gibi kritik işlemleri transaction ve log ile güvenceye al.
12. Her önemli işlem için hata senaryosu düşün.
13. Her modül için migration, model, resource ve frontend bağlantısını tamamla.
14. Sadece görüntü değil, çalışan iş mantığı teslim et.
15. İş sonunda kullanıcıya kurulum ve kullanım dokümanı ver.

---

## 14. Önerilen Commit Akışı

```txt
init: Laravel project setup
setup: Filament admin panel
db: core ecommerce migrations
feat: settings module
feat: dynamic header and footer
feat: page block system
feat: category management
feat: product management
feat: product options
feat: cart system
feat: delivery zones and slots
feat: checkout flow
feat: order management
feat: iyzico payment integration
feat: visual approval module
feat: courier and qr delivery
feat: notification system
feat: coupons and campaigns
feat: seo system
feat: blog module
ui: frontend theme
perf: speed optimization
test: checkout and payment tests
deploy: production configuration
docs: final delivery documentation
```

---

## 15. Son Not

Bu proje klasik bir e-ticaret sitesi değildir. Lav Çiçekçilik için özel tasarlanmış, çiçekçilik operasyonlarını karşılayan hızlı ve yönetilebilir bir satış sistemidir.

En önemli öncelikler:

1. Hız
2. SEO
3. Panelden tam yönetim
4. Çiçekçilik özel teslimat akışı
5. Güvenli ödeme
6. Türkçe karakter sorunsuzluğu
7. Sade ve sürdürülebilir kod
8. Mobil uyumlu kullanıcı deneyimi

Ajan bu dosyayı ana talimat kabul etmeli ve tüm geliştirme sürecini bu plana göre tamamlamalıdır.
