# AGENT.md — Lav Çiçekçilik Kontrollü Devam, Hata Düzeltme ve Yeni Modül Talimatları

## 1. Proje Durumu ve Amaç

Bu dosya, mevcut Laravel + Filament + özel mini e-ticaret çekirdeği projesinin belirli bir aşamadan sonra kontrollü şekilde geliştirilmesi için hazırlanmıştır.

Bu aşamadan sonra amaç şudur:

- Mevcut çalışan yapıyı bozmadan ilerlemek.
- Panelden yapılan ayarların ve içeriklerin kod değişiklikleriyle eski haline dönmesini engellemek.
- Ana sayfa ve diğer sayfalardaki blok yönetimini gerçek kullanıcı gibi test etmek.
- Ürün detay sayfasındaki “Ekstra Hediye” alanını panelden yönetilebilir hale getirmek.
- İlçe/mahalle bazlı teslimat ücreti ve teslimat kampanya sistemini geliştirmek.
- Sipariş saat seçiminde Türkiye saati ve minimum hazırlık süresi kuralını uygulamak.
- Admin panel için yüksek sesli yeni sipariş bildirimi ve masaüstü bildirimi eklemek.
- Müşteri tarafı için web push / tarayıcı bildirim onayı ve sipariş durum bildirimleri eklemek.

Bu dosya, mevcut ana `agent.md` dosyasının yerine değil, bu aşamadan sonraki kontrollü geliştirme rehberi olarak kullanılmalıdır.

---

## 2. Kritik Kural: Panelden Yapılan Değişiklikler Korunacak

Ajan aşağıdaki kurallara kesinlikle uymalıdır:

- Panelden yapılan ayarları eski haline döndürme.
- Panelden girilmiş blokları, başlıkları, görselleri, menüleri, footer bilgilerini, sayfa içeriklerini veya ürün ayarlarını kod içindeki varsayılan değerlerle ezme.
- Seeder, migration, factory, demo data veya fallback kodları mevcut panel verilerini silmemeli veya güncellememeli.
- Kod tarafında statik veri varsa, sadece veritabanında kayıt yoksa kullanılmalı.
- Veritabanında kayıt varsa her zaman paneldeki kayıt esas alınmalı.
- Ana sayfa, ürün sayfası, kategori sayfası, kurumsal sayfalar, header ve footer alanları panelden gelen verilerle çalışmalı.
- Deploy, cache temizleme, optimize komutları veya yeni geliştirme sonrası paneldeki değişiklikler kaybolmamalı.
- Mevcut veriyi değiştiren migration yazılacaksa önce yedek, sonra veri korumalı migration uygulanmalı.
- Büyük refactor yerine küçük, test edilebilir ve geri alınabilir adımlar tercih edilmeli.

Her işlemden önce ajan şu soruyu sormalıdır:

```txt
Bu değişiklik panelden yapılmış mevcut bir ayarı, içeriği veya kullanıcı verisini eski haline döndürür mü?
```

Cevap evetse işlem durdurulmalı ve veri korumalı alternatif uygulanmalıdır.

---

## 3. Geliştirme Sırası

Bu dosyada yer alan işler aşağıdaki sırayla yapılacaktır:

1. Mevcut sistemi analiz et ve koruma katmanı oluştur.
2. Blok sisteminin panel verilerini koruduğunu doğrula ve düzelt.
3. Ürün detay sayfası için Ekstra Hediye sistemini ekle.
4. İlçe/mahalle bazlı teslimat ücreti ve kampanya sistemini geliştir.
5. Sipariş saat seçimi için timezone ve minimum hazırlık süresi ayarlarını ekle.
6. Admin panel için yeni sipariş sesli/masaüstü bildirim sistemini ekle.
7. Müşteri tarafı için web push bildirim izin ve sipariş durumu bildirim sistemini ekle.
8. Sipariş durum değişikliklerini merkezi bildirim tetikleme servisine bağla.
9. Tüm sistemi kullanıcı gibi test et.
10. Yapılan işleri raporla.

---

# FAZ 1 — Mevcut Sistemi İnceleme ve Veri Koruma

## Amaç

Yeni geliştirmelere başlamadan önce mevcut sistemin nasıl çalıştığı incelenecek. Panelden yapılan ayarları eski haline döndüren nedenler tespit edilecek.

## Görevler

- [ ] Mevcut Laravel sürümünü kontrol et.
- [ ] Mevcut Filament panel yapısını incele.
- [ ] Mevcut route yapısını incele.
- [ ] Mevcut migration dosyalarını incele.
- [ ] Mevcut seeder dosyalarını incele.
- [ ] Ana sayfa verilerinin hangi tablolardan geldiğini tespit et.
- [ ] Diğer sayfa bloklarının hangi tablolardan geldiğini tespit et.
- [ ] Header alanının panelden mi yoksa statik koddan mı geldiğini kontrol et.
- [ ] Footer alanının panelden mi yoksa statik koddan mı geldiğini kontrol et.
- [ ] Panelden yapılan ayarların cache yüzünden eski görünüp görünmediğini kontrol et.
- [ ] Kod içinde statik ana sayfa, statik blok, statik menü veya statik footer alanı varsa listele.
- [ ] Seeder dosyaları mevcut verileri siliyor veya güncelliyor mu kontrol et.
- [ ] Migration dosyalarında veri kaybı riski var mı kontrol et.
- [ ] Paneldeki kayıtlar için yedek alma yöntemi belirle.

## Kabul Kriterleri

- [ ] Panel verilerini bozan veya eskiye döndüren sebep tespit edilmiş olmalı.
- [ ] Mevcut panel verilerini koruma planı hazırlanmış olmalı.
- [ ] Yeni geliştirmeye geçmeden önce veri kaybı riski ortadan kaldırılmış olmalı.

---

# FAZ 2 — Panelden Yönetilen Blok Sistemini Kalıcı Hale Getirme

## Sorun

Ana sayfa ve diğer sayfalar panelden blok yapısı ile düzenlenebilir olmalıydı. Ancak panelden bazı ayarlar yapıldıktan sonra kod yazmaya devam edilince proje eski haline dönüyor.

## Amaç

Panelden yapılan tüm blok değişiklikleri kalıcı olacak. Kod yazmak, deploy almak, cache temizlemek veya optimize komutları çalıştırmak blokları eski haline döndürmeyecek.

## Zorunlu Kurallar

- Bloklar veritabanından okunmalı.
- Bloklarda sıra, aktif/pasif durum, başlık, görsel, bağlantı, içerik ve ayarlar panelden yönetilmeli.
- Ana sayfa statik Blade içeriğine bağlı kalmamalı.
- Diğer sayfalar da aynı blok render sistemiyle çalışmalı.
- Veritabanında blok varsa fallback statik içerik kullanılmamalı.
- Seed dosyaları mevcut blokları güncellememeli veya silmemeli.
- Panelde blok güncellenince ilgili cache temizlenmeli.

## Teknik Görevler

- [ ] `pages` tablosunu kontrol et.
- [ ] `page_blocks` tablosunu kontrol et.
- [ ] Ana sayfa route/controller yapısını kontrol et.
- [ ] Diğer sayfa route/controller yapısını kontrol et.
- [ ] Blok render yapısı yoksa `PageBlockRenderer` servisi oluştur.
- [ ] Her blok tipi için ayrı Blade partial kullan.
- [ ] Blok render işleminde sadece aktif blokları göster.
- [ ] Blok render işleminde `sort_order` alanını kullan.
- [ ] Blok JSON ayarlarını güvenli şekilde oku.
- [ ] Eksik alanlarda hata vermeyen fallback kullan.
- [ ] Blok kayıtlarında görsel, başlık, açıklama, buton, link gibi alanları panelden yönet.
- [ ] Filament içinde blok ekleme, silme, sıralama, aktif/pasif yapma işlemlerini düzelt.
- [ ] Panelde blok kaydedilince `PageCache`, `BlockCache`, `ViewCache` veya ilgili cache temizlensin.
- [ ] Seeder dosyalarını idempotent hale getir.
- [ ] Seeder sadece kayıt yoksa demo blok oluştursun.
- [ ] Seeder mevcut blokları silmesin veya güncellemesin.
- [ ] Kodda hard-coded ana sayfa blokları varsa kaldır.
- [ ] Kodda hard-coded footer/header varsa panel ayarına bağla.

## Kullanıcı Gibi Test

Ajan aşağıdaki testi kendisi yapmadan fazı bitmiş saymayacaktır:

- [ ] Panele gir.
- [ ] Ana sayfaya yeni bir blok ekle.
- [ ] Blok başlığını değiştir.
- [ ] Blok açıklamasını değiştir.
- [ ] Blok görselini değiştir.
- [ ] Blok sırasını değiştir.
- [ ] Bir bloğu pasif yap.
- [ ] Frontend ana sayfada değişikliklerin göründüğünü kontrol et.
- [ ] Başka bir sayfaya blok ekle ve kontrol et.
- [ ] `php artisan optimize:clear` çalıştır.
- [ ] Sayfayı tekrar aç ve değişikliklerin kaybolmadığını kontrol et.
- [ ] Gerekirse cache temizle ve tekrar kontrol et.
- [ ] Seeder çalıştırılıyorsa mevcut verilerin ezilmediğini kontrol et.
- [ ] Kod değişikliği sonrası panelden yapılan blokların eski haline dönmediğini doğrula.

## Kabul Kriterleri

- [ ] Ana sayfa panelden blok mantığıyla yönetilebilmeli.
- [ ] Diğer sayfalar panelden blok mantığıyla yönetilebilmeli.
- [ ] Panel değişiklikleri kod yazınca eski haline dönmemeli.
- [ ] Cache temizleme sonrası bloklar kaybolmamalı.
- [ ] Seeder mevcut veriyi ezmemeli.

---

# FAZ 3 — Ürün Detay Sayfası Ekstra Hediye Sistemi

## İstek

Ürün detay sayfasındaki “Ekstra Hediye” bölümüne kullanıcı panelden içerik ekleyebilmeli. Her ürün için ilgili ekstra hediye ürünleri manuel olarak seçilebilmeli.

## Amaç

Ekstra Hediye bölümü tamamen panelden yönetilebilir olacak ve seçilen hediyeler sepete/siparişe fiyatlarıyla birlikte yansıyacak.

## Özellikler

- Admin panelden ekstra hediye ürünü oluşturabilmeli.
- Ekstra hediye adı, açıklaması, görseli, fiyatı, aktif/pasif durumu ve sırası olmalı.
- Her ana ürün için gösterilecek ekstra hediyeler manuel seçilebilmeli.
- Ürüne özel seçim yoksa genel aktif hediyeler gösterilebilir.
- Müşteri ürün detay sayfasından bir veya birden fazla ekstra hediye seçebilmeli.
- Seçilen ekstra hediyeler sepette ürün altında görünmeli.
- Seçilen ekstra hediyeler checkout ekranında görünmeli.
- Seçilen ekstra hediyeler admin sipariş detayında görünmeli.
- Fiyatlar snapshot olarak saklanmalı; sonradan hediye fiyatı değişirse eski sipariş etkilenmemeli.

## Önerilen Tablolar

### `extra_gifts`

```txt
id
name
slug
description
image
price
is_active
sort_order
created_at
updated_at
```

### `extra_gift_product`

```txt
id
product_id
extra_gift_id
sort_order
created_at
updated_at
```

### `cart_item_extra_gifts`

```txt
id
cart_item_id
extra_gift_id
name_snapshot
price_snapshot
quantity
created_at
updated_at
```

### `order_item_extra_gifts`

```txt
id
order_item_id
extra_gift_id
name_snapshot
price_snapshot
quantity
created_at
updated_at
```

## Panel Görevleri

- [ ] `ExtraGift` modeli oluştur.
- [ ] `ExtraGiftResource` oluştur.
- [ ] Ekstra hediye adı alanı ekle.
- [ ] Açıklama alanı ekle.
- [ ] Görsel yükleme alanı ekle.
- [ ] Fiyat alanı ekle.
- [ ] Aktif/pasif alanı ekle.
- [ ] Sıralama alanı ekle.
- [ ] Product Resource içine ekstra hediye seçimi ekle.
- [ ] Ürün düzenleme ekranında çoklu ekstra hediye seçimi yapılabilsin.
- [ ] Seçilen ekstra hediyelerin ürün detay sayfasında sırasıyla görünmesini sağla.

## Frontend Görevleri

- [ ] Ürün detay sayfasında Ekstra Hediye bölümü oluştur.
- [ ] Ekstra hediyeleri kart şeklinde göster.
- [ ] Hediye görseli, adı, açıklaması ve fiyatı görünsün.
- [ ] Müşteri çoklu seçim yapabilsin.
- [ ] Seçilen hediyeler sepete ekleme request’i ile backend’e gönderilsin.
- [ ] Sepet sayfasında seçilen hediyeler ürün altında görünsün.
- [ ] Checkout ekranında seçilen hediyeler görünsün.
- [ ] Sipariş detayında seçilen hediyeler görünsün.

## Fiyat Hesaplama Kuralı

```txt
Ürün fiyatı + seçilen ekstra hediyeler + teslimat ücreti - indirimler = genel toplam
```

Ekstra hediye fiyatı sepete eklendiği anda snapshot olarak saklanmalıdır.

## Test Senaryosu

- [ ] Panelden 3 ekstra hediye oluştur.
- [ ] Bir ürüne 2 ekstra hediye ata.
- [ ] Ürün detay sayfasında sadece atanmış hediyelerin göründüğünü kontrol et.
- [ ] Ekstra hediye seçerek sepete ekle.
- [ ] Sepette ekstra hediye adının ve fiyatının göründüğünü kontrol et.
- [ ] Toplam fiyatın doğru hesaplandığını kontrol et.
- [ ] Sipariş oluştur ve admin detayında hediyeyi kontrol et.
- [ ] Ekstra hediye fiyatını panelden değiştir ve eski siparişin etkilenmediğini doğrula.

## Kabul Kriterleri

- [ ] Ekstra hediyeler panelden yönetiliyor olmalı.
- [ ] Ürün bazlı ekstra hediye seçilebilmeli.
- [ ] Seçilen ekstra hediyeler sepete ve siparişe yansımalı.
- [ ] Fiyat snapshot mantığı çalışmalı.

---

# FAZ 4 — İlçe/Mahalle Bazlı Teslimat Ücreti ve Kampanya Sistemi

## İstek

Kullanıcı gönderim ilçe veya ilçedeki mahallelere özel fiyatlar belirleyebilmeli. Bu gönderim fiyatlarıyla ilgili kampanyalar düzenleyebilmeli.

Örnek:

- Sur / Dokuzçeltik Mahallesi kurye ücreti 600 TL.
- Kullanıcı 5.000 TL alışveriş yaparsa gönderim ücretsiz olabilir.
- Ya da %70 indirim uygulanabilir.
- Ya da sabit 200 TL kurye ücreti uygulanabilir.
- Sepete ekleme veya ödeme adımından önce ilçe/mahalle seçilirken kampanya müşteriye gösterilmeli.
- Örnek mesaj: “Sepetinize 300 TL’lik ürün ekleyerek gönderim ücretini ücretsiz yapabilirsiniz.”

## Amaç

Teslimat ücreti ve teslimat kampanyaları panelden esnek şekilde yönetilebilir olacak.

## Özellikler

- İlçe bazlı teslimat ücreti.
- Mahalle bazlı teslimat ücreti.
- Mahalle ücreti varsa ilçe ücretini ezmeli.
- Minimum sepet tutarına göre ücretsiz teslimat.
- Minimum sepet tutarına göre yüzdelik teslimat indirimi.
- Minimum sepet tutarına göre sabit teslimat ücreti.
- Kampanya başlangıç ve bitiş tarihi.
- Aktif/pasif kampanya.
- Kampanya müşteri mesajı.
- Sepette eksik tutar bilgilendirmesi.
- Checkout öncesinde kampanya bilgisinin gösterilmesi.

## Önerilen Tablolar

### `delivery_districts`

```txt
id
city
name
base_delivery_fee
is_active
sort_order
created_at
updated_at
```

### `delivery_neighborhoods`

```txt
id
delivery_district_id
name
delivery_fee
is_active
sort_order
created_at
updated_at
```

### `delivery_fee_campaigns`

```txt
id
name
delivery_district_id nullable
delivery_neighborhood_id nullable
type
min_cart_total
discount_type nullable
discount_value nullable
fixed_delivery_fee nullable
starts_at nullable
ends_at nullable
customer_message nullable
is_active
created_at
updated_at
```

## Kampanya Tipleri

```txt
free_delivery
delivery_discount
fixed_delivery_fee
```

## İndirim Tipleri

```txt
percentage
fixed_amount
```

## Hesaplama Önceliği

Teslimat ücreti şu sırayla hesaplanmalıdır:

1. Mahalle seçilmişse mahalle özel ücreti kullan.
2. Mahalle özel ücreti yoksa ilçe ücreti kullan.
3. Aktif kampanya var mı kontrol et.
4. Kampanya minimum sepet tutarı karşılanıyorsa kampanyayı uygula.
5. Kampanya karşılanmıyorsa eksik tutarı hesapla.
6. Müşteriye anlaşılır kampanya mesajı göster.

## Örnek Servis Çıktısı

```php
[
    'base_fee' => 600,
    'final_fee' => 200,
    'discount_amount' => 400,
    'campaign_applied' => true,
    'campaign_name' => 'Dokuzçeltik Teslimat Kampanyası',
    'remaining_amount_for_campaign' => 0,
    'customer_message' => 'Bu bölge için kampanyalı kurye ücreti: 200 TL.',
]
```

## Panel Görevleri

- [ ] İlçe yönetimi oluştur veya mevcut yapıyı geliştir.
- [ ] Mahalle yönetimi oluştur veya mevcut yapıyı geliştir.
- [ ] İlçe bazlı teslimat ücreti alanı ekle.
- [ ] Mahalle bazlı teslimat ücreti alanı ekle.
- [ ] Teslimat kampanyası yönetimi oluştur.
- [ ] Kampanya türü alanı ekle.
- [ ] Minimum sepet tutarı alanı ekle.
- [ ] Ücretsiz teslimat seçeneği ekle.
- [ ] Yüzdelik indirim seçeneği ekle.
- [ ] Sabit teslimat ücreti seçeneği ekle.
- [ ] Başlangıç/bitiş tarihi ekle.
- [ ] Müşteri mesajı alanı ekle.
- [ ] Aktif/pasif alanı ekle.

## Frontend Görevleri

- [ ] Sepet veya ürün detay aşamasında ilçe/mahalle seçimi yapılabilsin.
- [ ] İlçe seçilince mahalleler dinamik listelensin.
- [ ] Mahalle seçilince teslimat ücreti hesaplanıp gösterilsin.
- [ ] Kampanya varsa müşteriye gösterilsin.
- [ ] Kampanya tutarı eksikse eksik tutar gösterilsin.
- [ ] Örnek mesajlar gösterilsin:
  - “Sepetinize 300 TL’lik ürün ekleyerek gönderim ücretini ücretsiz yapabilirsiniz.”
  - “Bu bölge için 5.000 TL üzeri siparişlerde teslimat ücretsiz.”
  - “Bu bölge için kampanyalı kurye ücreti: 200 TL.”
- [ ] Checkout ekranında teslimat ücreti net gösterilsin.
- [ ] Sipariş oluşturulurken teslimat ücreti snapshot olarak saklansın.

## Test Senaryosu

- [ ] Sur ilçesi oluştur.
- [ ] Dokuzçeltik mahallesi oluştur.
- [ ] Mahalle teslimat ücretini 600 TL yap.
- [ ] 5.000 TL üzeri ücretsiz teslimat kampanyası oluştur.
- [ ] Sepete 4.700 TL ürün ekle.
- [ ] Mahalle seç ve 300 TL eksik mesajını kontrol et.
- [ ] Sepete 5.000 TL üzeri ürün ekle.
- [ ] Teslimat ücretinin ücretsiz olduğunu kontrol et.
- [ ] %70 indirim kampanyasını test et.
- [ ] Sabit 200 TL kampanyasını test et.
- [ ] Sipariş sonrası teslimat ücreti snapshot değerini kontrol et.

## Kabul Kriterleri

- [ ] İlçe/mahalle teslimat ücreti panelden yönetilebilmeli.
- [ ] Kampanyalar panelden oluşturulabilmeli.
- [ ] Kampanya mesajları müşteriye gösterilmeli.
- [ ] Eksik tutar doğru hesaplanmalı.
- [ ] Siparişte teslimat ücreti snapshot olarak korunmalı.

---

# FAZ 5 — Sipariş Saat Seçimi, Türkiye Saati ve Minimum Hazırlık Süresi

## İstek

Kullanıcı sipariş saatini seçerken güncel saat ve dakikadan yola çıkarak, sipariş vereceği saatten en az panelde belirlenen süre kadar sonrasını seçebilmeli.

Örnek:

- Panelde minimum hazırlık süresi 2 saat.
- Müşteri saat 14:20’de sipariş veriyorsa en erken 16:20 sonrası teslimat saatlerini seçebilmeli.
- Sunucu saati ile Türkiye saati farklı olabilir.
- Panelde timezone/GMT ayarı yapılabilmeli.

## Amaç

Teslimat saatleri paneldeki zaman ayarlarına ve Türkiye saatine göre doğru filtrelenecek.

## Panel Ayarları

Panelde “Teslimat Zaman Ayarları” bölümü olmalı:

```txt
Timezone: Europe/Istanbul
GMT Offset: +03:00
Minimum hazırlık süresi birimi: dakika/saat
Minimum hazırlık süresi değeri: 120 dakika veya 2 saat
Aynı gün teslimat aktif/pasif
Aynı gün teslimat son sipariş saati
Kapalı günler
```

## Teknik Kurallar

- Carbon kullanılmalı.
- Varsayılan timezone `Europe/Istanbul` olmalı.
- Sunucu saati doğrudan baz alınmamalı.
- Hesaplama panel timezone ayarına göre yapılmalı.
- Frontend sadece uygun slotları göstermeli.
- Backend validasyonu uygunsuz slotu reddetmeli.
- Seçilen teslimat zamanı siparişte snapshot olarak saklanmalı.

## Örnek Kural

```txt
Şu an: 14:20
Minimum hazırlık süresi: 2 saat
En erken teslimat zamanı: 16:20
```

Önerilen kural:

```txt
Slot başlangıç saati, en erken teslimat zamanından sonra olmalıdır.
```

## Görevler

- [ ] Panelde teslimat zaman ayarları sayfası oluştur.
- [ ] Timezone alanı ekle.
- [ ] GMT offset alanı ekle.
- [ ] Minimum hazırlık süresi alanı ekle.
- [ ] Minimum hazırlık süresi birimi ekle.
- [ ] Aynı gün teslimat aktif/pasif alanı ekle.
- [ ] Aynı gün teslimat son sipariş saati alanı ekle.
- [ ] `DeliveryTimeService` oluştur veya mevcut servisi geliştir.
- [ ] Güncel zamanı panel ayarına göre hesapla.
- [ ] En erken teslimat zamanını hesapla.
- [ ] Teslimat slotlarını filtrele.
- [ ] Checkout sayfasında sadece uygun slotları göster.
- [ ] Backend validasyonunda uygunsuz saat seçimini reddet.
- [ ] Türkçe hata mesajı göster:
  - “Seçtiğiniz teslimat saati artık uygun değil. Lütfen daha ileri bir saat seçin.”

## Test Senaryosu

- [ ] Timezone olarak `Europe/Istanbul` seç.
- [ ] Minimum hazırlık süresini 2 saat yap.
- [ ] Güncel saate göre slotların filtrelendiğini kontrol et.
- [ ] Minimum süreyi 30 dakika yap ve tekrar test et.
- [ ] Minimum süreyi 180 dakika yap ve tekrar test et.
- [ ] Uygunsuz slotu manuel request ile gönder ve backend’in reddettiğini kontrol et.
- [ ] Sunucu timezone farklı olsa bile Türkiye saatine göre doğru çalıştığını doğrula.
- [ ] Aynı gün teslimat kapatılırsa bugünün seçilemediğini kontrol et.

## Kabul Kriterleri

- [ ] Minimum hazırlık süresi panelden yönetilebilmeli.
- [ ] Türkiye saati/timezone hesabı doğru yapılmalı.
- [ ] Uygunsuz saatler frontend’de gizlenmeli.
- [ ] Uygunsuz saatler backend’de reddedilmeli.

---

# FAZ 6 — Admin Panel Yeni Sipariş Sesli ve Masaüstü Bildirimi

## İstek

Kullanıcı paneli sürekli bilgisayarda açık bırakacak. Yeni sipariş gelirse:

- Yüksek sesli zil bildirimi çalmalı.
- Masaüstü bildirimi gönderilmeli.
- Panel içinde görsel uyarı gösterilmeli.
- Bildirime tıklayınca sipariş detayına gidilmeli.

## Önerilen Teknik Yaklaşım

İlk sürüm için polling yeterlidir. WebSocket zorunlu değildir.

- Admin panel her 10 saniyede bir yeni sipariş kontrol endpointini çağırır.
- Yeni sipariş varsa ses çalar.
- Browser Notification API ile masaüstü bildirimi gönderilir.
- Panel içinde toast/alert gösterilir.
- Kullanıcı bildirimi okundu yapabilir.

İleride Laravel Reverb, Pusher veya Soketi ile gerçek zamanlı sisteme geçilebilir.

## Panel Ayarları

```txt
Yeni sipariş sesli bildirim aktif/pasif
Masaüstü bildirimi aktif/pasif
Kontrol aralığı saniye
Ses dosyası
Ses seviyesi
Sadece ödenmiş siparişlerde bildirim gönder
Tüm yeni siparişlerde bildirim gönder
```

## Önerilen Tablolar

### `admin_order_notifications`

```txt
id
order_id
admin_user_id nullable
type
title
message
is_seen
seen_at
created_at
updated_at
```

Ayarlar mevcut `settings` tablosunda tutulabilir.

## Görevler

- [ ] Admin bildirim ayarlarını panele ekle.
- [ ] Varsayılan yüksek sesli zil dosyası ekle.
- [ ] Panel layout içine bildirim JS dosyası ekle.
- [ ] “Bildirimleri Aktifleştir” butonu ekle.
- [ ] Butona basınca ses ve masaüstü bildirim izni istenmeli.
- [ ] Yeni sipariş oluştuğunda admin bildirimi oluştur.
- [ ] Yeni sipariş kontrol endpointi oluştur.
- [ ] Polling interval panel ayarından alınmalı.
- [ ] Yeni sipariş varsa ses çalmalı.
- [ ] Yeni sipariş varsa masaüstü bildirimi gönderilmeli.
- [ ] Panel içinde toast/alert gösterilmeli.
- [ ] Bildirime tıklayınca sipariş detayına yönlendirmeli.
- [ ] Bildirim okundu olarak işaretlenebilmeli.
- [ ] Ses kapatma/açma kontrolü olmalı.

## Admin Panel Aktivasyon Metni

```txt
Yeni sipariş bildirimlerini aktifleştirin. Panel açıkken yeni sipariş geldiğinde yüksek sesli uyarı ve masaüstü bildirimi alırsınız.
```

Buton:

```txt
Bildirimleri Aktifleştir
```

## Test Senaryosu

- [ ] Panelde “Bildirimleri Aktifleştir” butonuna bas.
- [ ] Tarayıcı masaüstü bildirim iznini ver.
- [ ] Test siparişi oluştur.
- [ ] Panel açıkken ses çaldığını kontrol et.
- [ ] Masaüstü bildirimi geldiğini kontrol et.
- [ ] Bildirime tıklanınca sipariş detayına gidildiğini kontrol et.
- [ ] Ses kapatılınca yeni siparişte ses çalmadığını kontrol et.
- [ ] Polling aralığını değiştir ve çalıştığını doğrula.

## Kabul Kriterleri

- [ ] Panel açıkken yeni sipariş sesli bildirim vermeli.
- [ ] Masaüstü bildirimi çalışmalı.
- [ ] Ses yüksek ve duyulabilir olmalı.
- [ ] Bildirime tıklayınca sipariş detayına gidilmeli.

---

# FAZ 7 — Müşteri Tarafı Web Push / Tarayıcı Bildirimleri

## İstek

Müşteri üye olduktan sonra veya sipariş verdikten sonra bir popup ile bildirim onayı istensin. Kullanıcı kabul ederse sipariş durumu değiştiğinde telefonuna veya bilgisayarına tarayıcı bildirimi düşsün.

## Popup Metni

Başlık:

```txt
Sipariş Bildirimlerini Açın
```

Metin:

```txt
Siparişinizin hazırlanma süreci ve durumu ile ilgili anlık bildirimleri açarak, verdiğiniz siparişin hazırlanma, kurye ve teslimat süreçleri hakkında bildirim alabilirsiniz.
```

Butonlar:

```txt
Bildirimleri Aç
Daha Sonra
```

## Teknik Yaklaşım

- Service Worker kullanılacak.
- Browser Notification API kullanılacak.
- Web Push subscription kaydı alınacak.
- VAPID key yapısı kurulacak.
- Sipariş durumu değişince ilgili müşteriye bildirim gönderilecek.
- Bildirime tıklanınca sipariş takip sayfası açılacak.

## Önerilen Tablo

### `push_subscriptions`

```txt
id
customer_id nullable
order_id nullable
endpoint
public_key
auth_token
user_agent
device_type nullable
is_active
created_at
updated_at
```

## Popup Gösterim Kuralları

- [ ] Üye olduktan sonra gösterilebilir.
- [ ] Sipariş başarılı sayfasında gösterilebilir.
- [ ] Kullanıcı daha önce izin vermediyse gösterilmeli.
- [ ] Tarayıcı destekliyorsa gösterilmeli.
- [ ] Kullanıcı reddederse sürekli rahatsız edilmemeli.
- [ ] Önce özel açıklama popup’ı gösterilmeli, sonra tarayıcı izin kutusu açılmalı.

## Sipariş Durum Bildirim Metinleri

### Hazırlanıyor

```txt
Siparişiniz hazırlanıyor
Lav Çiçekçilik siparişinizi hazırlamaya başladı.
```

### Görsel Onay Bekliyor

```txt
Görsel onayınız bekleniyor
Siparişiniz hazırlandı. Görseli inceleyip onay verebilirsiniz.
```

### Kuryeye Verildi

```txt
Siparişiniz kuryeye verildi
Çiçeğiniz teslimat için yola çıktı.
```

### Teslim Edildi

```txt
Siparişiniz teslim edildi
Lav Çiçekçilik siparişiniz başarıyla teslim edildi.
```

## Görevler

- [ ] Service worker dosyası oluştur.
- [ ] `push_subscriptions` tablosunu oluştur.
- [ ] Push subscription kaydetme endpointi oluştur.
- [ ] Push subscription pasifleştirme endpointi oluştur.
- [ ] Müşteri veya sipariş ile subscription ilişkisi kur.
- [ ] Popup component oluştur.
- [ ] Üyelik sonrası popup göster.
- [ ] Sipariş başarılı sayfasında popup göster.
- [ ] Tarayıcı Notification API desteğini kontrol et.
- [ ] Kullanıcı izin verirse subscription kaydet.
- [ ] Kullanıcı reddederse bunu localStorage veya veritabanında işaretle.
- [ ] Sipariş durum değişikliğinde push notification gönder.
- [ ] Bildirime tıklanınca sipariş takip sayfası açılsın.
- [ ] Panelden müşteri bildirimleri aktif/pasif ayarı ekle.
- [ ] Bildirim şablonları panelden düzenlenebilir hale getir.

## Önemli Teknik Notlar

- Web push için HTTPS gereklidir.
- Mobil tarayıcı destekleri değişebilir.
- Kullanıcı izin vermeden bildirim gönderilemez.
- Bildirim izni tarayıcı tarafından yönetilir.
- Bildirim hatası sipariş durum değişikliğini bozmamalıdır; hata loglanmalıdır.

## Test Senaryosu

- [ ] Yeni müşteri hesabı oluştur.
- [ ] Popup’ın çıktığını kontrol et.
- [ ] “Bildirimleri Aç” butonuna bas.
- [ ] Tarayıcı iznini ver.
- [ ] Subscription kaydının veritabanına yazıldığını kontrol et.
- [ ] Test siparişi oluştur.
- [ ] Sipariş durumunu “Hazırlanıyor” yap.
- [ ] Müşteri cihazına bildirim geldiğini kontrol et.
- [ ] Sipariş durumunu “Kuryeye Verildi” yap.
- [ ] Bildirimin geldiğini kontrol et.
- [ ] Bildirime tıklayınca sipariş takip sayfasına gidildiğini kontrol et.

## Kabul Kriterleri

- [ ] Müşteri tarafında bildirim onay popup’ı çalışmalı.
- [ ] Kullanıcı izin verirse push subscription kaydedilmeli.
- [ ] Sipariş durumu değişince tarayıcı bildirimi gönderilmeli.
- [ ] Bildirim metinleri Türkçe olmalı.
- [ ] Bildirime tıklanınca sipariş takip sayfası açılmalı.

---

# FAZ 8 — Sipariş Durum Değişiklikleri ve Bildirim Tetikleme

## Amaç

Admin panelde sipariş durumu değiştirildiğinde müşteri bildirimleri, admin bildirimleri ve durum geçmişi doğru çalışmalıdır.

## Görevler

- [ ] `OrderStatusService` oluştur veya mevcut yapıyı merkezi hale getir.
- [ ] Sipariş durumu doğrudan model update ile değiştirilmemeli.
- [ ] Her durum değişikliği servis üzerinden yapılmalı.
- [ ] Durum değişince `order_status_histories` tablosuna kayıt atılmalı.
- [ ] Durum değişince müşteri push bildirimi tetiklenmeli.
- [ ] Durum değişince varsa e-posta/SMS/WhatsApp bildirimleri tetiklenmeli.
- [ ] Bildirim gönderilemezse sipariş durumu değişikliği başarısız olmamalı.
- [ ] Bildirim hatası loglanmalı.
- [ ] Aynı durum için tekrar tekrar bildirim gönderilmesi engellenmeli.

## Kabul Kriterleri

- [ ] Sipariş durumları merkezi servisle değişmeli.
- [ ] Durum geçmişi eksiksiz tutulmalı.
- [ ] Müşteri bildirimleri doğru tetiklenmeli.
- [ ] Bildirim hatası sipariş akışını bozmamalı.

---

# FAZ 9 — Genel Regresyon Testi

## Amaç

Yeni geliştirmeler mevcut sistemi bozmamalıdır.

## Test Listesi

### Blok Sistemi

- [ ] Ana sayfa blokları panelden değiştiriliyor mu?
- [ ] Diğer sayfa blokları panelden değiştiriliyor mu?
- [ ] Cache temizlenince bloklar kayboluyor mu?
- [ ] Kod değişikliği sonrası bloklar eski hale dönüyor mu?
- [ ] Seeder mevcut blokları eziyor mu?

### Ekstra Hediye

- [ ] Panelden ekstra hediye ekleniyor mu?
- [ ] Ürüne özel ekstra hediye seçiliyor mu?
- [ ] Ürün detayda doğru hediyeler görünüyor mu?
- [ ] Sepette ekstra hediyeler görünüyor mu?
- [ ] Siparişte ekstra hediyeler görünüyor mu?

### Teslimat Ücreti

- [ ] İlçe ücreti çalışıyor mu?
- [ ] Mahalle ücreti ilçe ücretini eziyor mu?
- [ ] Ücretsiz teslimat kampanyası çalışıyor mu?
- [ ] Yüzde indirim kampanyası çalışıyor mu?
- [ ] Sabit ücret kampanyası çalışıyor mu?
- [ ] Eksik tutar mesajı doğru mu?

### Saat Seçimi

- [ ] Minimum hazırlık süresi çalışıyor mu?
- [ ] Türkiye saati doğru mu?
- [ ] Uygunsuz saatler gizleniyor mu?
- [ ] Backend uygunsuz saatleri reddediyor mu?

### Admin Bildirimi

- [ ] Yeni siparişte ses çalıyor mu?
- [ ] Masaüstü bildirimi geliyor mu?
- [ ] Bildirime tıklayınca siparişe gidiyor mu?

### Müşteri Bildirimi

- [ ] Popup çıkıyor mu?
- [ ] Bildirim izni alınıyor mu?
- [ ] Push subscription kaydediliyor mu?
- [ ] Sipariş durumu değişince bildirim gidiyor mu?

### Genel Sistem

- [ ] Türkçe karakter hatası var mı?
- [ ] Mobil görünüm bozuldu mu?
- [ ] Sepet toplamı doğru mu?
- [ ] Checkout çalışıyor mu?
- [ ] iyzico ödeme etkilenmedi mi?
- [ ] Sipariş yönetimi çalışıyor mu?
- [ ] SEO alanları bozulmadı mı?
- [ ] Header/footer panelden yönetiliyor mu?
- [ ] Ürün/kategori sayfaları çalışıyor mu?

---

# FAZ 10 — Teslim Raporu

## Amaç

Yapılan işler net şekilde raporlanacak.

## Teslim Edilecekler

- [ ] Yapılan değişikliklerin listesi
- [ ] Eklenen migration dosyaları
- [ ] Eklenen modeller
- [ ] Eklenen servisler
- [ ] Güncellenen Filament resource dosyaları
- [ ] Güncellenen frontend dosyaları
- [ ] Eklenen JS/service worker dosyaları
- [ ] Test edilen senaryolar
- [ ] Bilinen eksikler
- [ ] Sonraki faz önerileri

---

## Final Talimat

Bu aşamada öncelik hızlıca yeni özellik eklemek değil, mevcut sistemi bozmadan kontrollü ilerlemektir.

Ajan her değişiklikte şu ilkeye bağlı kalmalıdır:

```txt
Panelden yapılan her değişiklik veri olarak değerlidir ve korunmalıdır.
```

Bu yüzden hiçbir işlem panel verilerini, müşteri verilerini, ürünleri, siparişleri, teslimat ayarlarını veya sayfa bloklarını eski haline döndürmemelidir.
