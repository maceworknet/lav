<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\DeliveryZone;
use App\Models\DeliveryNeighborhood;
use App\Models\DeliverySlot;
use App\Models\Coupon;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\MailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin User
        User::firstOrCreate(
            ['email' => 'admin@lav.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password123'),
            ]
        );

        // 2. Settings
        $settings = [
            // General settings
            ['key' => 'site_name', 'value' => 'Lav Çiçekçilik', 'group' => 'general'],
            ['key' => 'site_phone', 'value' => '+90 532 123 45 67', 'group' => 'general'],
            ['key' => 'site_whatsapp', 'value' => '905321234567', 'group' => 'general'],
            ['key' => 'site_email', 'value' => 'info@lavcicekcilik.com', 'group' => 'general'],
            ['key' => 'site_address', 'value' => 'Diclekent Bulvarı, No: 42 Kayapınar / Diyarbakır', 'group' => 'general'],
            ['key' => 'working_hours', 'value' => 'Her Gün: 08:30 - 22:00', 'group' => 'general'],
            
            // SEO defaults
            ['key' => 'meta_title', 'value' => 'Diyarbakır Çiçekçi | Lav Çiçekçilik | Online Çiçek Siparişi', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Diyarbakır online çiçek siparişi. En taze güller, orkideler ve aranjmanlar aynı gün teslimat garantisi ve ücretsiz kurye avantajıyla Lav Çiçekçilik\'te.', 'group' => 'seo'],
            
            // E-commerce rules
            ['key' => 'min_order_amount', 'value' => '300.00', 'group' => 'ecommerce'],
            ['key' => 'free_delivery_threshold', 'value' => '1000.00', 'group' => 'ecommerce'],
            ['key' => 'same_day_delivery_active', 'value' => '1', 'group' => 'ecommerce'],
            
            // iyzico Settings
            ['key' => 'iyzico_test_mode', 'value' => '1', 'group' => 'iyzico'],
            ['key' => 'iyzico_api_key', 'value' => 'sandbox-xxxxxxxxxxxxxxxxxxxxxxxxxx', 'group' => 'iyzico'],
            ['key' => 'iyzico_secret_key', 'value' => 'sandbox-xxxxxxxxxxxxxxxxxxxxxxxxxx', 'group' => 'iyzico'],
            ['key' => 'iyzico_base_url', 'value' => 'https://sandbox-api.iyzipay.com', 'group' => 'iyzico'],

            // Google Auth Settings
            ['key' => 'google_auth_active', 'value' => '0', 'group' => 'google_auth'],
            ['key' => 'google_client_id', 'value' => '', 'group' => 'google_auth'],
            ['key' => 'google_client_secret', 'value' => '', 'group' => 'google_auth'],

            // Header Settings
            ['key' => 'site_logo', 'value' => '', 'group' => 'header'],
            ['key' => 'header_search_active', 'value' => '1', 'group' => 'header'],
            ['key' => 'header_account_active', 'value' => '1', 'group' => 'header'],
            ['key' => 'header_favorites_active', 'value' => '1', 'group' => 'header'],
            ['key' => 'header_cart_active', 'value' => '1', 'group' => 'header'],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(['key' => $setting['key']], $setting);
        }

        // 3. Menus
        $headerMenu = Menu::firstOrCreate(['slug' => 'header-menu'], ['name' => 'Header Menü']);
        $footerMenu = Menu::firstOrCreate(['slug' => 'footer-menu'], ['name' => 'Footer Menü']);

        // Header Menu Items
        $headerItems = [
            ['title' => 'Ana Sayfa', 'url' => '/', 'order' => 1],
            ['title' => 'Güller', 'url' => '/kategori/guller', 'order' => 2],
            ['title' => 'Orkideler', 'url' => '/kategori/orkideler', 'order' => 3],
            ['title' => 'Kutuda Çiçekler', 'url' => '/kategori/kutuda-cicekler', 'order' => 4],
            ['title' => 'Hakkımızda', 'url' => '/sayfa/hakkimizda', 'order' => 5],
            ['title' => 'Blog', 'url' => '/blog', 'order' => 6],
            ['title' => 'İletişim', 'url' => '/iletisim', 'order' => 7],
        ];

        foreach ($headerItems as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $headerMenu->id, 'title' => $item['title']],
                ['url' => $item['url'], 'order' => $item['order']]
            );
        }

        $footerItems = [
            ['title' => 'Mesafeli Satış Sözleşmesi', 'url' => '/sayfa/mesafeli-satis-sozlesmesi', 'order' => 1],
            ['title' => 'Gizlilik ve Çerez Politikası', 'url' => '/sayfa/gizlilik-ve-cerez-politikasi', 'order' => 2],
            ['title' => 'KVKK Aydınlatma Metni', 'url' => '/sayfa/kvkk-aydinlatma-metni', 'order' => 3],
            ['title' => 'Teslimat ve İade Politikası', 'url' => '/sayfa/teslimat-ve-iade', 'order' => 4],
        ];

        foreach ($footerItems as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $footerMenu->id, 'title' => $item['title']],
                ['url' => $item['url'], 'order' => $item['order']]
            );
        }

        // 4. Pages and Page Blocks
        $aboutPage = Page::firstOrCreate(
            ['slug' => 'hakkimizda'],
            [
                'title' => 'Hakkımızda',
                'content' => 'Lav Çiçekçilik, Diyarbakır genelinde en taze ve özel çiçek tasarımlarını hızlı ve güvenilir şekilde ulaştırmak amacıyla kurulmuştur.',
                'meta_title' => 'Hakkımızda | Lav Çiçekçilik',
                'meta_description' => 'Lav Çiçekçilik hikayesi, vizyonu ve misyonu.',
            ]
        );

        $kvkkPage = Page::firstOrCreate(
            ['slug' => 'kvkk-aydinlatma-metni'],
            [
                'title' => 'KVKK Aydınlatma Metni',
                'content' => 'Kişisel Verilerin Korunması Kanunu kapsamında aydınlatma metnidir.',
                'meta_title' => 'KVKK Aydınlatma Metni | Lav Çiçekçilik',
                'meta_description' => 'KVKK kapsamında kişisel verilerinizin işlenmesi hakkında bilgilendirme.',
            ]
        );

        $deliveryPage = Page::firstOrCreate(
            ['slug' => 'teslimat-ve-iade'],
            [
                'title' => 'Teslimat ve İade Politikası',
                'content' => 'Siparişlerinizin teslimat süreçleri ve iade şartları ile ilgili kurallar.',
                'meta_title' => 'Teslimat ve İade Politikası | Lav Çiçekçilik',
                'meta_description' => 'Çiçek teslimatı ve sipariş iade şartları.',
            ]
        );

        $homePage = Page::firstOrCreate(
            ['slug' => 'ana-sayfa'],
            [
                'title' => 'Ana Sayfa',
                'meta_title' => 'Lav Çiçekçilik | Diyarbakır Çiçek Siparişi',
                'meta_description' => 'Diyarbakır online çiçek siparişi ana sayfası.',
            ]
        );

        // Home Page Blocks
        $homeBlocks = [
            [
                'type' => 'hero_slider',
                'content' => [
                    'desktop_height' => '500px',
                    'mobile_height' => '350px',
                    'slides' => [
                        [
                            'title' => 'Diyarbakır\'ın En Taze Çiçekleri',
                            'subtitle' => 'Sevdiklerinize unutulmaz bir sürpriz yapın.',
                            'button_text' => 'Ürünleri İncele',
                            'button_url' => '/kategori/guller',
                            'desktop_image' => '/assets/images/hero1.webp',
                            'mobile_image' => '/assets/images/hero1.webp'
                        ]
                    ]
                ],
                'order' => 1
            ],

            [
                'type' => 'product_carousel',
                'content' => [
                    'title' => 'Çok Satan Ürünler',
                    'subtitle' => 'Müşterilerimizin en çok tercih ettiği tasarımlar',
                    'limit' => 8
                ],
                'order' => 3
            ],
            [
                'type' => 'trust_badges',
                'content' => [
                    'items' => [
                        ['title' => 'Aynı Gün Teslimat', 'description' => 'Diyarbakır içi hızlı kurye', 'icon' => 'truck'],
                        ['title' => 'Görsel Onay Sistemi', 'description' => 'Hazırlanan çiçeğin fotoğrafı', 'icon' => 'camera'],
                        ['title' => 'Güvenli Ödeme', 'description' => 'iyzico güvencesiyle 3D Secure', 'icon' => 'shield-check']
                    ]
                ],
                'order' => 4
            ]
        ];

        foreach ($homeBlocks as $block) {
            PageBlock::firstOrCreate(
                ['page_id' => $homePage->id, 'type' => $block['type']],
                ['content' => $block['content'], 'order' => $block['order']]
            );
        }

        // 5. Categories
        $categoriesData = [
            ['name' => 'Güller', 'slug' => 'guller', 'description' => 'En şık ve romantik gül buketleri', 'show_on_header' => true, 'show_on_homepage' => true],
            ['name' => 'Orkideler', 'slug' => 'orkideler', 'description' => 'Zarif ve asil saksı orkideleri', 'show_on_header' => true, 'show_on_homepage' => true],
            ['name' => 'Kutuda Çiçekler', 'slug' => 'kutuda-cicekler', 'description' => 'Modern tasarımlı kutuda çiçek aranjmanları', 'show_on_header' => true, 'show_on_homepage' => true],
            ['name' => 'Papatyalar', 'slug' => 'papatyalar', 'description' => 'Masum ve samimi papatya demetleri', 'show_on_header' => true, 'show_on_homepage' => true],
        ];

        $categories = [];
        foreach ($categoriesData as $catData) {
            $categories[$catData['slug']] = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
        }

        // 6. Products
        $productsData = [
            [
                'name' => '101 Kırmızı Gül Buketi',
                'slug' => '101-kirmizi-gul-buketi',
                'sku' => 'LAV-ROSE-101',
                'short_description' => 'İthal taze kırmızı güllerden hazırlanmış dev buket.',
                'description' => 'Sevdiklerinizi büyüleyecek ihtişamlı 101 adet kırmızı gül buketi. Buketimiz kaliteli ithal güllerle, özel ambalaj ve cipsofilyalarla süslenerek hazırlanmaktadır.',
                'care_instructions' => 'Çiçekleri vazoya yerleştirmeden önce saplarını açılı kesin. Suyu gün aşırı değiştirin.',
                'delivery_info' => 'Bu ürün sadece Diyarbakır sınırlarında aynı gün teslim edilebilir.',
                'price' => 2499.00,
                'discount_price' => 2199.00,
                'is_featured' => true,
                'is_weekly' => true,
                'category_slug' => 'guller',
                'image' => '/assets/images/rose101.webp'
            ],
            [
                'name' => 'Çift Dallı Beyaz Safir Orkide',
                'slug' => 'cift-dalli-beyaz-safir-orkide',
                'sku' => 'LAV-ORCH-WHITE',
                'short_description' => 'Seramik saksıda çift dallı beyaz orkide.',
                'description' => 'Zarafetin simgesi olan çift dallı beyaz phalaenopsis orkide. Özel seramik saksıda, yosun süslemeli ve şık paketli teslim edilir.',
                'care_instructions' => 'Haftada bir kez, kökleri hafifçe yeşilden griye dönünce sulayın. Doğrudan güneş almayan aydınlık yerleri sever.',
                'price' => 899.00,
                'discount_price' => null,
                'is_featured' => true,
                'category_slug' => 'orkideler',
                'image' => '/assets/images/orchid.webp'
            ],
            [
                'name' => 'Şık Kutuda Renkli Mevsim Aranjmanı',
                'slug' => 'sik-kutuda-renkli-mevsim-aranjmani',
                'sku' => 'LAV-BOX-SEASONAL',
                'short_description' => 'Kadife kutuda mevsim çiçekleri aranjmanı.',
                'description' => 'Özel tasarım kadife silindir kutu içerisinde güller, papatyalar ve mevsim yeşilliklerinden oluşan büyüleyici aranjman.',
                'care_instructions' => 'Kutunun içerisindeki süngere 2 günde bir yarım çay bardağı su ekleyin.',
                'price' => 650.00,
                'discount_price' => 590.00,
                'is_featured' => true,
                'category_slug' => 'kutuda-cicekler',
                'image' => '/assets/images/box_flower.webp'
            ],
            [
                'name' => 'Kır Papatyaları Demeti',
                'slug' => 'kir-papatyalari-demeti',
                'sku' => 'LAV-DAISY-FIELD',
                'short_description' => 'Taptaze kokulu kır papatyaları.',
                'description' => 'Masumiyetin ve doğallığın simgesi, mis kokulu taze kır papatyalarından oluşan özel sarımlı buket.',
                'care_instructions' => 'Vazo suyunu temiz tutun, doğrudan güneş ışığı ve rüzgardan koruyun.',
                'price' => 450.00,
                'discount_price' => null,
                'category_slug' => 'papatyalar',
                'image' => '/assets/images/daisy.webp'
            ]
        ];

        foreach ($productsData as $prodData) {
            $catSlug = $prodData['category_slug'];
            $imagePath = $prodData['image'];
            unset($prodData['category_slug']);
            unset($prodData['image']);

            $product = Product::firstOrCreate(['slug' => $prodData['slug']], $prodData);
            
            // Link category
            if (isset($categories[$catSlug])) {
                $product->categories()->syncWithoutDetaching([$categories[$catSlug]->id]);
            }

            // Create image record
            ProductImage::firstOrCreate(
                ['product_id' => $product->id, 'image_path' => $imagePath],
                ['is_main' => true, 'order' => 0]
            );

            // 7. Product Options (Size, extra Teddy Bear, etc.)
            if ($catSlug === 'guller' || $catSlug === 'kutuda-cicekler') {
                $option = ProductOption::firstOrCreate(
                    ['product_id' => $product->id, 'name' => 'Boyut'],
                    ['type' => 'select', 'is_required' => true]
                );

                ProductOptionValue::firstOrCreate(
                    ['product_option_id' => $option->id, 'label' => 'Standart Boy'],
                    ['price_modifier' => 0.00, 'order' => 1]
                );

                ProductOptionValue::firstOrCreate(
                    ['product_option_id' => $option->id, 'label' => 'Büyük Boy'],
                    ['price_modifier' => 250.00, 'order' => 2]
                );

                ProductOptionValue::firstOrCreate(
                    ['product_option_id' => $option->id, 'label' => 'VIP Boy'],
                    ['price_modifier' => 500.00, 'order' => 3]
                );
            }

            // Global add-on options (e.g. teddy bear, chocolate)
            $teddyOption = ProductOption::firstOrCreate(
                ['product_id' => $product->id, 'name' => 'Ekstra Hediye'],
                ['type' => 'checkbox', 'is_required' => false]
            );

            ProductOptionValue::firstOrCreate(
                ['product_option_id' => $teddyOption->id, 'label' => 'Sevimli Peluş Ayıcık (15cm)'],
                ['price_modifier' => 120.00, 'order' => 1]
            );

            ProductOptionValue::firstOrCreate(
                ['product_option_id' => $teddyOption->id, 'label' => 'Kutu Premium Çikolata'],
                ['price_modifier' => 180.00, 'order' => 2]
            );
        }

        // 8. Delivery Zones & Neighborhoods
        $districts = [
            'Kayapınar' => [
                'neighborhoods' => ['Diclekent Mah.', 'Huzurevleri Mah.', 'Peyas Mah.', 'Fırat Mah.', 'Medya Mah.'],
                'fee' => 50.00
            ],
            'Yenişehir' => [
                'neighborhoods' => ['Ofis Mah.', 'Kooperatifler Mah.', 'Fabrika Mah.', 'Yenişehir Mah.'],
                'fee' => 60.00
            ],
            'Bağlar' => [
                'neighborhoods' => ['Bağcılar Mah.', 'Şeyh Şamil Mah.', '5 Nisan Mah.'],
                'fee' => 70.00
            ],
            'Sur' => [
                'neighborhoods' => ['Cami Kebir Mah.', 'Ziya Gökalp Mah.', 'İskenderpaşa Mah.'],
                'fee' => 80.00
            ],
            'Ergani' => [
                'neighborhoods' => ['Fatih Mah.', 'Adnan Menderes Mah.', 'Kemertaş Mah.', 'Şirinevler Mah.', 'Bagür Mah.'],
                'fee' => 120.00
            ],
            'Eğil' => [
                'neighborhoods' => ['Yeni Mah.', 'Dere Mah.', 'Gündoğdu Mah.', 'Kale Mah.'],
                'fee' => 150.00
            ],
            'Dicle' => [
                'neighborhoods' => ['27 Mayıs Mah.', 'Yeşiltepe Mah.', 'Bağlar Mah.'],
                'fee' => 180.00
            ],
            'Kulp' => [
                'neighborhoods' => ['Merkez Mah.', 'Turgut Özal Mah.', 'Yeni Mah.'],
                'fee' => 250.00
            ],
            'Hazro' => [
                'neighborhoods' => ['Hürriyet Mah.', 'Elbahçe Mah.', 'Cami Mah.'],
                'fee' => 200.00
            ],
            'Bismil' => [
                'neighborhoods' => ['Fatih Mah.', 'Dicle Mah.', 'Altıok Mah.', 'Sanayi Mah.'],
                'fee' => 130.00
            ],
            'Silvan' => [
                'neighborhoods' => ['Selahattin Mah.', 'Mescit Mah.', 'Bağlar Mah.', 'Kale Mah.'],
                'fee' => 140.00
            ],
            'Çermik' => [
                'neighborhoods' => ['Tepe Mah.', 'Çarşı Mah.', 'Kale Mah.'],
                'fee' => 160.00
            ],
            'Çınar' => [
                'neighborhoods' => ['Cumhuriyet Mah.', 'Gazi Mah.', 'Yeni Mah.'],
                'fee' => 120.00
            ],
            'Hani' => [
                'neighborhoods' => ['Merkez Mah.', 'Veziri Mah.', 'Dereli Mah.'],
                'fee' => 170.00
            ],
            'Lice' => [
                'neighborhoods' => ['Yeni Mah.', 'Mescit Mah.', 'Kale Mah.'],
                'fee' => 180.00
            ],
            'Kocaköy' => [
                'neighborhoods' => ['Kaya Mah.', 'Yeni Mah.'],
                'fee' => 160.00
            ],
            'Çüngüş' => [
                'neighborhoods' => ['Karşıyaka Mah.', 'Camikebir Mah.'],
                'fee' => 220.00
            ]
        ];

        foreach ($districts as $districtName => $data) {
            $zone = DeliveryZone::firstOrCreate(
                ['district' => $districtName],
                ['city' => 'Diyarbakır', 'is_active' => true]
            );

            foreach ($data['neighborhoods'] as $neighName) {
                DeliveryNeighborhood::firstOrCreate(
                    ['delivery_zone_id' => $zone->id, 'name' => $neighName],
                    [
                        'delivery_fee' => $data['fee'],
                        'min_order_amount' => 300.00,
                        'free_delivery_threshold' => 1000.00,
                        'is_active' => true
                    ]
                );
            }
        }

        // 9. Delivery Slots
        $slots = [
            ['name' => 'Sabah (09:00 - 12:00)', 'start_time' => '09:00:00', 'end_time' => '12:00:00', 'cutoff_time' => '08:30:00', 'capacity' => 15],
            ['name' => 'Öğle (12:00 - 15:00)', 'start_time' => '12:00:00', 'end_time' => '15:00:00', 'cutoff_time' => '11:30:00', 'capacity' => 15],
            ['name' => 'Öğleden Sonra (15:00 - 18:00)', 'start_time' => '15:00:00', 'end_time' => '18:00:00', 'cutoff_time' => '14:30:00', 'capacity' => 15],
            ['name' => 'Akşam (18:00 - 21:00)', 'start_time' => '18:00:00', 'end_time' => '21:00:00', 'cutoff_time' => '17:30:00', 'capacity' => 10],
        ];

        foreach ($slots as $slot) {
            DeliverySlot::firstOrCreate(['name' => $slot['name']], $slot);
        }

        // 10. Coupons
        Coupon::firstOrCreate(
            ['code' => 'LAV100'],
            [
                'type' => 'fixed',
                'value' => 100.00,
                'min_order_amount' => 600.00,
                'usage_limit' => 500,
                'is_active' => true
            ]
        );

        Coupon::firstOrCreate(
            ['code' => 'BAHAR15'],
            [
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 300.00,
                'usage_limit' => 1000,
                'is_active' => true
            ]
        );

        // 11. Blog Categories & Posts
        $blogCat = BlogCategory::firstOrCreate(
            ['slug' => 'cicek-bakimi'],
            ['name' => 'Çiçek Bakımı', 'description' => 'Çiçeklerinizin daha uzun ömürlü kalması için pratik bakım önerileri.']
        );

        BlogPost::firstOrCreate(
            ['slug' => 'orkide-bakimi-nasil-yapilir'],
            [
                'blog_category_id' => $blogCat->id,
                'title' => 'Orkide Bakımı Nasıl Yapılır? Püf Noktaları',
                'summary' => 'Saksı orkidelerinin sulanması, konumu ve yaprak bakımı hakkında temel bilgiler.',
                'content' => 'Orkideler zarafetleriyle evlerimizi süsleyen en asil çiçeklerdendir. Doğru bakım ile yıllarca çiçek açmaya devam edebilirler. Sulama sıklığı, saksının ışık alması ve doğru toprak seçimi orkide sağlığı için kritik önem taşır. Yapraklarının nemlendirilmesi ve solan çiçek dallarının budanması da diğer dikkat edilmesi gereken konulardır...',
                'image' => '/assets/images/blog_orchid.webp',
                'is_active' => true,
                'meta_title' => 'Orkide Bakımı Nasıl Yapılır? Adım Adım Rehber',
                'meta_description' => 'Orkide sulama, budama ve saksı değişimi hakkında merak ettiğiniz her şey bu yazımızda.'
            ]
        );

        // 12. Mail Templates (panelden düzenlenebilir; mevcut kayıtlar ezilmez)
        $mailTemplates = [
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

        foreach ($mailTemplates as $template) {
            MailTemplate::firstOrCreate(['key' => $template['key']], $template);
        }
    }
}
