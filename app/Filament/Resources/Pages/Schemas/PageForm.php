<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\FileUpload;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Sayfa İçeriği')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Sayfa Başlığı')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label('Kalıcı Bağlantı (Slug)')
                                    ->required()
                                    ->unique('pages', 'slug', ignoreRecord: true),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                        RichEditor::make('content')
                            ->label('Statik İçerik (Blok kullanılmıyorsa)')
                            ->columnSpanFull(),
                    ]),

                Section::make('Dinamik Sayfa Blokları (Ana Sayfa vb. İçin)')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('pageBlocks')
                            ->label('Sayfa Blokları')
                            ->relationship('pageBlocks')
                            ->addActionLabel('Yeni Sayfa Bloğu Ekle')
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => 
                                 match($state['type'] ?? null) {
                                     'hero_slider' => 'Hero Slider / Banner',
                                     'category_slider' => 'Kategori Slider / Karusel',
                                     'category_grid' => 'Kategori Vitrinleri Grid',
                                     'product_carousel' => 'Ürün Karuseli / Slayt',
                                     'trust_badges' => 'Güven Rozetleri (Hızlı Kargo vb.)',
                                     'rich_text' => 'Zengin Metin Bloğu',
                                     'html_block' => 'Özel HTML Bloğu',
                                     'image_grid' => 'Banner Izgarası / Görsel Grid',
                                     'handpicked_products' => 'Sizin İçin Seçtiklerimiz (Sonsuz Kaydırma)',
                                     'seo_text' => 'SEO Tanıtım Metni & Özellikler',
                                     'blog_posts' => 'Blog Yazıları / Öneriler',
                                     default => 'Sayfa Bloğu'
                                 }
                              )
                            ->schema([
                                Select::make('type')
                                    ->label('Blok Tipi')
                                    ->options([
                                        'hero_slider' => 'Hero Slider / Banner',
                                        'category_slider' => 'Kategori Slider / Karusel',
                                        'category_grid' => 'Kategori Vitrinleri Grid',
                                        'product_carousel' => 'Ürün Karuseli / Slayt',
                                        'trust_badges' => 'Güven Rozetleri (Hızlı Kargo vb.)',
                                        'rich_text' => 'Zengin Metin Bloğu',
                                        'html_block' => 'Özel HTML Bloğu',
                                        'image_grid' => 'Banner Izgarası / Görsel Grid',
                                        'handpicked_products' => 'Sizin İçin Seçtiklerimiz (Sonsuz Kaydırma)',
                                        'seo_text' => 'SEO Tanıtım Metni & Özellikler',
                                        'blog_posts' => 'Blog Yazıları / Öneriler',
                                    ])
                                    ->required()
                                    ->live(),
                                TextInput::make('order')
                                    ->label('Sıralama')
                                    ->numeric()
                                    ->default(0),

                                Group::make()
                                    ->statePath('content')
                                    ->columnSpanFull()
                                    ->schema([
                                        // 1. Hero Slider Settings
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('desktop_height')
                                                            ->label('Masaüstü Yükseklik (Örn: 500px veya 60vh)')
                                                            ->default('500px')
                                                            ->required(),
                                                        TextInput::make('mobile_height')
                                                            ->label('Mobil Yükseklik (Örn: 300px veya 40vh)')
                                                            ->default('300px')
                                                            ->required(),
                                                    ]),
                                                Repeater::make('slides')
                                                    ->label('Slaytlar')
                                                    ->schema([
                                                        FileUpload::make('desktop_image')
                                                            ->label('Masaüstü Görseli')
                                                            ->directory('slider')
                                                            ->image()
                                                            ->required(),
                                                        FileUpload::make('mobile_image')
                                                            ->label('Mobil Görseli')
                                                            ->directory('slider')
                                                            ->image()
                                                            ->required(),
                                                        TextInput::make('button_url')
                                                            ->label('Slayt Linki / Yönlendirme Adresi (Opsiyonel)'),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->columns(2),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'hero_slider'),

                                        // 11. Category Slider Settings
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('cat_slider_title')
                                                            ->label('Başlık (Opsiyonel)')
                                                            ->default('Kategorilere Göre Keşfedin'),
                                                        TextInput::make('cat_slider_subtitle')
                                                            ->label('Alt Başlık (Opsiyonel)')
                                                            ->default('En çok tercih edilen çiçek türleri'),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'category_slider'),

                                        // 2. Category Grid Settings
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('cat_grid_title')
                                                            ->label('Kategori Grubu Başlığı'),
                                                        TextInput::make('cat_grid_subtitle')
                                                            ->label('Kategori Grubu Alt Başlığı'),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'category_grid'),

                                        // 3. Product Carousel Settings
                                        Group::make()
                                            ->schema([
                                                Grid::make(3)
                                                    ->schema([
                                                        TextInput::make('carousel_title')
                                                            ->label('Başlık'),
                                                        TextInput::make('carousel_subtitle')
                                                            ->label('Alt Başlık'),
                                                        TextInput::make('carousel_limit')
                                                            ->label('Görüntülenecek Ürün Limiti')
                                                            ->numeric()
                                                            ->default(8),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'product_carousel'),

                                        // 4. Trust Badges Settings
                                        Group::make()
                                            ->schema([
                                                Repeater::make('trust_items')
                                                    ->label('Rozetler')
                                                    ->schema([
                                                        TextInput::make('badge_title')
                                                            ->label('Başlık')
                                                            ->required(),
                                                        TextInput::make('badge_description')
                                                            ->label('Açıklama'),
                                                        Select::make('icon')
                                                            ->label('İkon')
                                                            ->options([
                                                                'truck' => 'Kamyon (Teslimat)',
                                                                'camera' => 'Kamera (Fotoğraf Onayı)',
                                                                'shield-check' => 'Kalkan (Güvenlik)',
                                                            ])
                                                            ->required(),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->columns(3),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'trust_badges'),

                                        // 5. Rich Text Settings
                                        Group::make()
                                            ->schema([
                                                RichEditor::make('body')
                                                    ->label('Metin İçeriği')
                                                    ->required(),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'rich_text'),

                                        // 6. HTML Block Settings
                                        Group::make()
                                            ->schema([
                                                Textarea::make('html')
                                                    ->label('Özel HTML Kodu')
                                                    ->rows(8)
                                                    ->required(),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'html_block'),

                                        // 7. Banner Grid Settings (Görsel Izgarası)
                                        Group::make()
                                            ->schema([
                                                Select::make('columns')
                                                    ->label('Sütun Sayısı (Masaüstü/Tablet)')
                                                    ->options([
                                                        '1' => '1 Sütunlu (Tam Genişlik)',
                                                        '2' => '2 Sütunlu Yan Yana',
                                                        '3' => '3 Sütunlu Yan Yana',
                                                        '4' => '4 Sütunlu Yan Yana',
                                                    ])
                                                    ->default('2')
                                                    ->required(),
                                                Repeater::make('banner_items')
                                                    ->label('Görseller')
                                                    ->schema([
                                                        FileUpload::make('image')
                                                            ->label('Arka Plan Görseli')
                                                            ->directory('banners')
                                                            ->image()
                                                            ->required()
                                                            ->columnSpan(2),
                                                        TextInput::make('banner_subtitle')
                                                            ->label('Küçük Üst Başlık (Örn: Premium)')
                                                            ->columnSpan(1),
                                                        TextInput::make('banner_title')
                                                            ->label('Ana Başlık (Örn: Özel Çiçekler)')
                                                            ->columnSpan(1),
                                                        TextInput::make('banner_description')
                                                            ->label('Açıklama (Örn: Özel seçim...)')
                                                            ->columnSpan(2),
                                                        TextInput::make('banner_button_text')
                                                            ->label('Buton Metni (Örn: ALIŞVERİŞE BAŞLA)')
                                                            ->columnSpan(1),
                                                        TextInput::make('banner_link')
                                                            ->label('Yönlendirme / Buton Linki')
                                                            ->columnSpan(1),
                                                    ])
                                                    ->columnSpanFull()
                                                    ->columns(2),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'image_grid'),

                                        // 8. Handpicked Products Settings (Sizin İçin Seçtiklerimiz)
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('handpicked_title')
                                                            ->label('Başlık')
                                                            ->default('Sizin İçin Seçtiklerimiz')
                                                            ->required(),
                                                        TextInput::make('handpicked_limit')
                                                            ->label('Görüntülenecek İlk Ürün Sayısı')
                                                            ->numeric()
                                                            ->default(20)
                                                            ->required(),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'handpicked_products'),

                                        // 9. SEO Text & Features Settings (Zengin Metin + Dikey Kartlar)
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        RichEditor::make('seo_content')
                                                            ->label('Sol Sütun: SEO Uyumlu Tanıtım Metni')
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        TextInput::make('right_title')
                                                            ->label('Sağ Sütun: Özellikler Grubu Başlığı')
                                                            ->default('Diyarbakır Çiçek Siparişi')
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        Textarea::make('right_description')
                                                            ->label('Sağ Sütun: Özellikler Grubu Açıklaması')
                                                            ->rows(3)
                                                            ->columnSpanFull(),
                                                        TextInput::make('badge_text')
                                                            ->label('Deneyim Rozet Metni')
                                                            ->default('15+ Yıllık Deneyim')
                                                            ->required()
                                                            ->columnSpanFull(),
                                                        Repeater::make('features')
                                                            ->label('Sağ Sütun: Güvenlik / Özellik Kartları (Maksimum 4)')
                                                            ->schema([
                                                                TextInput::make('feature_title')
                                                                    ->label('Kart Başlığı')
                                                                    ->required(),
                                                                TextInput::make('feature_description')
                                                                    ->label('Kart Açıklaması')
                                                                    ->required(),
                                                                Select::make('icon')
                                                                    ->label('İkon')
                                                                    ->options([
                                                                        'truck' => 'Kamyon (Hızlı Teslimat)',
                                                                        'smile' => 'Gülen Yüz (Mutluluk Garantisi)',
                                                                        'credit-card' => 'Kredi Kartı (Güvenli Ödeme)',
                                                                        'headphones' => 'Kulaklık (7/24 Destek)',
                                                                    ])
                                                                    ->required(),
                                                            ])
                                                            ->maxItems(4)
                                                            ->columnSpanFull()
                                                            ->columns(3),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'seo_text'),

                                        // 10. Blog Posts Settings
                                        Group::make()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('blog_title')
                                                            ->label('Başlık')
                                                            ->default('Çiçek Bakımı ve Öneriler')
                                                            ->required(),
                                                        TextInput::make('blog_limit')
                                                            ->label('Görüntülenecek Yazı Sayısı')
                                                            ->numeric()
                                                            ->default(3)
                                                            ->required(),
                                                        TextInput::make('blog_subtitle')
                                                            ->label('Alt Başlık')
                                                            ->default('Çiçeklerinizin her zaman canlı kalması için derlediğimiz pratik rehberler.')
                                                            ->required()
                                                            ->columnSpanFull(),
                                                    ]),
                                            ])
                                            ->visible(fn ($get) => $get('../type') === 'blog_posts'),

                                        // Fallback Raw Data key-value if not standard
                                        KeyValue::make('raw_content')
                                            ->label('Ham Veriler')
                                            ->visible(fn ($get) => !in_array($get('../type'), ['hero_slider', 'category_slider', 'category_grid', 'product_carousel', 'trust_badges', 'rich_text', 'html_block', 'image_grid', 'handpicked_products', 'seo_text', 'blog_posts'])),
                                    ]),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('SEO Ayarları')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->label('SEO Başlığı')
                            ->maxLength(60),
                        Textarea::make('meta_description')
                            ->label('SEO Açıklaması')
                            ->maxLength(160)
                            ->rows(3),
                    ]),
            ]);
    }
}
