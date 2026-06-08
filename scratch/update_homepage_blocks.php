<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Page;
use App\Models\PageBlock;

$page = Page::where('slug', 'ana-sayfa')->first();

if (!$page) {
    echo "Hata: 'ana-sayfa' sluglı sayfa bulunamadı!\n";
    exit(1);
}

echo "Mevcut bloklar okunuyor...\n";
$existingBlocks = PageBlock::where('page_id', $page->id)->get()->keyBy('type');

$newBlocksData = [];

// 1. Hero Slider
$heroData = [
    'desktop_height' => '500px',
    'mobile_height' => '350px',
    'slides' => [
        [
            'desktop_image' => 'slider/01KTJGZT7917G4CNCZTDB6RDMN.webp',
            'mobile_image' => 'slider/01KTJGZT7MYRKAATRDWCV3EGCS.webp',
            'button_url' => '/kategori/guller',
            'button_text' => 'Ürünleri İncele'
        ]
    ]
];
if ($existingBlocks->has('hero_slider')) {
    $heroData = $existingBlocks->get('hero_slider')->content;
}
$newBlocksData['hero_slider'] = $heroData;

// 2. Category Slider
$catSliderData = [
    'cat_slider_title' => 'Kategorilere Göre Keşfedin',
    'cat_slider_subtitle' => 'En çok tercih edilen çiçek türleri'
];
$newBlocksData['category_slider'] = $catSliderData;

// 3. Image Grid
$imageGridData = [
    'columns' => '2',
    'banner_items' => [
        [
            'image' => 'banners/01KTJGZT7TGV3W4MRXH6S66SNH.webp',
            'banner_subtitle' => 'Premium',
            'banner_title' => '',
            'banner_description' => '',
            'banner_button_text' => 'ALIŞVERİŞE BAŞLA',
            'banner_link' => '/kategori/guller'
        ],
        [
            'image' => 'banners/01KTJGZT82ZB35SN0BAYJ3PM8A.webp',
            'banner_subtitle' => 'Taze',
            'banner_title' => '',
            'banner_description' => '',
            'banner_button_text' => 'ALIŞVERİŞE BAŞLA',
            'banner_link' => '/kategori/orkideler'
        ]
    ]
];
if ($existingBlocks->has('image_grid')) {
    $oldGridContent = $existingBlocks->get('image_grid')->content;
    $items = $oldGridContent['items'] ?? $oldGridContent['banner_items'] ?? [];
    $mappedItems = [];
    foreach ($items as $item) {
        $mappedItems[] = [
            'image' => $item['image'] ?? '',
            'banner_subtitle' => $item['banner_subtitle'] ?? $item['subtitle'] ?? '',
            'banner_title' => $item['banner_title'] ?? $item['title'] ?? '',
            'banner_description' => $item['banner_description'] ?? $item['description'] ?? '',
            'banner_button_text' => $item['banner_button_text'] ?? $item['button_text'] ?? '',
            'banner_link' => $item['banner_link'] ?? $item['link'] ?? '',
        ];
    }
    $imageGridData = [
        'columns' => $oldGridContent['columns'] ?? '2',
        'banner_items' => $mappedItems
    ];
}
$newBlocksData['image_grid'] = $imageGridData;

// 4. Product Carousel
$productCarouselData = [
    'carousel_title' => 'Çok Satan Ürünler',
    'carousel_subtitle' => 'Müşterilerimizin en çok tercih ettiği tasarımlar',
    'carousel_limit' => 8
];
if ($existingBlocks->has('product_carousel')) {
    $oldPCContent = $existingBlocks->get('product_carousel')->content;
    $productCarouselData = [
        'carousel_title' => $oldPCContent['carousel_title'] ?? $oldPCContent['title'] ?? 'Çok Satan Ürünler',
        'carousel_subtitle' => $oldPCContent['carousel_subtitle'] ?? $oldPCContent['subtitle'] ?? 'Müşterilerimizin en çok tercih ettiği tasarımlar',
        'carousel_limit' => $oldPCContent['carousel_limit'] ?? $oldPCContent['limit'] ?? 8,
    ];
}
$newBlocksData['product_carousel'] = $productCarouselData;

// 5. Handpicked Products
$handpickedData = [
    'handpicked_title' => 'Sizin İçin Seçtiklerimiz',
    'handpicked_limit' => 20
];
if ($existingBlocks->has('handpicked_products')) {
    $oldHPContent = $existingBlocks->get('handpicked_products')->content;
    $handpickedData = [
        'handpicked_title' => $oldHPContent['handpicked_title'] ?? $oldHPContent['title'] ?? 'Sizin İçin Seçtiklerimiz',
        'handpicked_limit' => $oldHPContent['handpicked_limit'] ?? $oldHPContent['limit'] ?? 20,
    ];
}
$newBlocksData['handpicked_products'] = $handpickedData;

// 6. Trust Badges
$trustBadgesData = [
    'trust_items' => [
        ['badge_title' => 'Aynı Gün Teslimat', 'badge_description' => 'Diyarbakır içi hızlı kurye', 'icon' => 'truck'],
        ['badge_title' => 'Görsel Onay Sistemi', 'badge_description' => 'Hazırlanan çiçeğin fotoğrafı', 'icon' => 'camera'],
        ['badge_title' => 'Güvenli Ödeme', 'badge_description' => 'iyzico güvencesiyle 3D Secure', 'icon' => 'shield-check']
    ]
];
if ($existingBlocks->has('trust_badges')) {
    $oldTBContent = $existingBlocks->get('trust_badges')->content;
    $items = $oldTBContent['items'] ?? $oldTBContent['trust_items'] ?? [];
    $mappedItems = [];
    foreach ($items as $item) {
        $mappedItems[] = [
            'badge_title' => $item['badge_title'] ?? $item['title'] ?? '',
            'badge_description' => $item['badge_description'] ?? $item['description'] ?? '',
            'icon' => $item['icon'] ?? '',
        ];
    }
    $trustBadgesData = [
        'trust_items' => $mappedItems
    ];
}
$newBlocksData['trust_badges'] = $trustBadgesData;

// 7. SEO Text
$seoTextData = [
    'seo_content' => '<h2>Diyarbakır Çiçek Siparişi</h2><p>Diyarbakır çiçekçi hizmetinde Lav Çiçekçilik kalitesi! Taze aranjmanlar ve aynı gün hızlı teslimat ile hemen Diyarbakır çiçek siparişi verin. Güvenli online ödeme.</p>',
    'right_title' => 'Diyarbakır Çiçek Siparişi',
    'right_description' => 'Diyarbakır çiçekçi hizmetinde Lav Çiçekçilik kalitesi!',
    'badge_text' => '15+ Yıllık Deneyim',
    'features' => [
        ['feature_title' => 'Hızlı Teslimat', 'feature_description' => 'Aynı gün teslimat imkanı', 'feature_icon' => 'truck'],
        ['feature_title' => 'Taze Çiçek Garantisi', 'feature_description' => 'Günlük taze temin edilen çiçekler', 'feature_icon' => 'smile'],
        ['feature_title' => 'Güvenli Ödeme', 'feature_description' => 'iyzico altyapısı ile 3D secure ödeme', 'feature_icon' => 'credit-card'],
        ['feature_title' => '7/24 Destek', 'feature_description' => 'Her an yanınızdayız', 'feature_icon' => 'headphones']
    ]
];
if ($existingBlocks->has('seo_text')) {
    $oldSEOContent = $existingBlocks->get('seo_text')->content;
    $features = $oldSEOContent['features'] ?? [];
    $mappedFeatures = [];
    foreach ($features as $f) {
        $mappedFeatures[] = [
            'feature_title' => $f['feature_title'] ?? $f['title'] ?? '',
            'feature_description' => $f['feature_description'] ?? $f['description'] ?? '',
            'feature_icon' => $f['feature_icon'] ?? $f['icon'] ?? '',
        ];
    }
    $seoTextData = [
        'seo_content' => $oldSEOContent['seo_content'] ?? $oldSEOContent['content'] ?? '',
        'right_title' => $oldSEOContent['right_title'] ?? 'Diyarbakır Çiçek Siparişi',
        'right_description' => $oldSEOContent['right_description'] ?? '',
        'badge_text' => $oldSEOContent['badge_text'] ?? '15+ Yıllık Deneyim',
        'features' => $mappedFeatures
    ];
}
$newBlocksData['seo_text'] = $seoTextData;

// 8. Blog Posts
$blogPostsData = [
    'blog_title' => 'Çiçek Bakımı ve Öneriler',
    'blog_subtitle' => 'Çiçeklerinizin her zaman canlı kalması için derlediğimiz pratik rehberler.',
    'blog_limit' => 3
];
if ($existingBlocks->has('blog_posts')) {
    $oldBlogContent = $existingBlocks->get('blog_posts')->content;
    $blogPostsData = [
        'blog_title' => $oldBlogContent['blog_title'] ?? $oldBlogContent['title'] ?? 'Çiçek Bakımı ve Öneriler',
        'blog_subtitle' => $oldBlogContent['blog_subtitle'] ?? $oldBlogContent['subtitle'] ?? 'Çiçeklerinizin her zaman canlı kalması için derlediğimiz pratik rehberler.',
        'blog_limit' => $oldBlogContent['blog_limit'] ?? $oldBlogContent['limit'] ?? 3,
    ];
}
$newBlocksData['blog_posts'] = $blogPostsData;


echo "Eski bloklar siliniyor...\n";
PageBlock::where('page_id', $page->id)->delete();

echo "Yeni sıralı bloklar oluşturuluyor...\n";
$order = 1;
foreach ($newBlocksData as $type => $content) {
    PageBlock::create([
        'page_id' => $page->id,
        'type' => $type,
        'content' => $content,
        'order' => $order++,
        'is_active' => true
    ]);
    echo "Eklendi: {$type} (Sıra: " . ($order - 1) . ")\n";
}

echo "Tamamlandı!\n";
