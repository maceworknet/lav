<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Ürün')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        Tab::make('Genel Bilgiler')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Ürün Adı')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (string $operation, $state, callable $set) =>
                                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                            )
                                            ->columnSpan(2),
                                        TextInput::make('sku')
                                            ->label('Stok Kodu (SKU)')
                                            ->required()
                                            ->unique('products', 'sku', ignoreRecord: true)
                                            ->default(fn () => 'LAV-' . strtoupper(Str::random(6))),
                                        TextInput::make('slug')
                                            ->label('Kalıcı Bağlantı (Slug)')
                                            ->required()
                                            ->unique('products', 'slug', ignoreRecord: true)
                                            ->prefix('/urun/')
                                            ->columnSpan(2),
                                        Select::make('categories')
                                            ->label('Kategoriler')
                                            ->relationship('categories', 'name')
                                            ->multiple()
                                            ->preload()
                                            ->required(),
                                        TextInput::make('price')
                                            ->label('Satış Fiyatı')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->required(),
                                        TextInput::make('discount_price')
                                            ->label('İndirimli Satış Fiyatı')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->nullable()
                                            ->helperText('Doluysa müşteriye bu fiyat gösterilir, normal fiyat üstü çizili görünür.'),
                                    ]),

                                Section::make('Açıklamalar')
                                    ->schema([
                                        Textarea::make('short_description')
                                            ->label('Kısa Açıklama')
                                            ->rows(2)
                                            ->helperText('Ürün kartlarında ve detay sayfasının üst kısmında görünür.')
                                            ->columnSpanFull(),
                                        RichEditor::make('description')
                                            ->label('Ürün Açıklaması')
                                            ->columnSpanFull(),
                                        Grid::make(2)
                                            ->schema([
                                                Textarea::make('care_instructions')
                                                    ->label('Bakım Önerisi')
                                                    ->rows(3),
                                                Textarea::make('delivery_info')
                                                    ->label('Teslimat Bilgisi')
                                                    ->rows(3),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('Görseller')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Repeater::make('images')
                                    ->relationship('images')
                                    ->hiddenLabel()
                                    ->addActionLabel('Görsel Ekle')
                                    ->reorderable()
                                    ->orderColumn('order')
                                    ->grid(2)
                                    ->schema([
                                        \App\Forms\Components\MediaPicker::make('image_path')
                                            ->label('Görsel')
                                            ->required(),
                                        Toggle::make('is_main')
                                            ->label('Ana Görsel')
                                            ->helperText('Listelerde ve ürün detayında ilk gösterilen görsel.')
                                            ->default(false),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => ($state['is_main'] ?? false) ? '⭐ Ana Görsel' : 'Görsel')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Seçenekler')
                            ->icon('heroicon-o-adjustments-horizontal')
                            ->schema([
                                Repeater::make('options')
                                    ->relationship('options')
                                    ->hiddenLabel()
                                    ->addActionLabel('Yeni Seçenek Grubu Ekle')
                                    ->collapsible()
                                    ->cloneable()
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                TextInput::make('name')
                                                    ->label('Seçenek Grubu Adı')
                                                    ->required()
                                                    ->placeholder('Örn: Boyut Seçimi'),
                                                Select::make('type')
                                                    ->label('Giriş Tipi')
                                                    ->options([
                                                        'select' => 'Seçim Kutusu (Dropdown)',
                                                        'radio' => 'Tekli Seçim (Radio)',
                                                        'checkbox' => 'Çoklu Seçim (Checkbox)',
                                                    ])
                                                    ->default('select')
                                                    ->required(),
                                                Toggle::make('is_required')
                                                    ->label('Zorunlu mu')
                                                    ->inline(false)
                                                    ->default(false),
                                            ]),

                                        Repeater::make('values')
                                            ->relationship('values')
                                            ->label('Seçenek Değerleri')
                                            ->addActionLabel('Değer Ekle')
                                            ->reorderable()
                                            ->orderColumn('order')
                                            ->compact()
                                            ->schema([
                                                Grid::make(2)
                                                    ->schema([
                                                        TextInput::make('label')
                                                            ->label('Seçenek Değeri')
                                                            ->required()
                                                            ->placeholder('Örn: Büyük Boy'),
                                                        TextInput::make('price_modifier')
                                                            ->label('Ek Fiyat Farkı')
                                                            ->numeric()
                                                            ->default(0.00)
                                                            ->prefix('₺')
                                                            ->required(),
                                                    ]),
                                            ])
                                            ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Yeni Değer')
                                            ->columnSpanFull(),
                                    ])
                                    ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Yeni Seçenek Grubu')
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Ekstra Hediyeler')
                            ->icon('heroicon-o-gift')
                            ->schema([
                                Select::make('extraGifts')
                                    ->label('Bu Ürünle Beraber Sunulacak Ekstra Hediyeler')
                                    ->relationship('extraGifts', 'name', modifyQueryUsing: fn ($query) => $query->whereHas('categories', fn ($q) => $q->where('slug', 'ekstra-hediyeler')))
                                    ->multiple()
                                    ->preload()
                                    ->helperText('Seçim yapılmazsa "Ekstra Hediyeler" kategorisindeki tüm aktif ürünler gösterilir. Hediye ürünlerini Ürünler bölümünden "Ekstra Hediyeler" kategorisine ekleyerek oluşturabilirsiniz.'),
                            ]),

                        Tab::make('Stok ve Teslimat')
                            ->icon('heroicon-o-truck')
                            ->schema([
                                Section::make('Stok')
                                    ->schema([
                                        Grid::make(3)
                                            ->schema([
                                                Toggle::make('stock_status')
                                                    ->label('Stokta Var (Satışa Açık)')
                                                    ->inline(false)
                                                    ->default(true),
                                                TextInput::make('stock')
                                                    ->label('Stok Adedi')
                                                    ->numeric()
                                                    ->nullable()
                                                    ->helperText('Boş bırakılırsa stok takibi yapılmaz.'),
                                                Toggle::make('supports_card_note')
                                                    ->label('Kart Notu Alınabilir')
                                                    ->inline(false)
                                                    ->default(true),
                                            ]),
                                    ]),

                                Section::make('Vitrin Etiketleri')
                                    ->description('Ürünün ana sayfa ve listelerde hangi bölümlerde öne çıkacağını belirler.')
                                    ->schema([
                                        Grid::make(4)
                                            ->schema([
                                                Toggle::make('is_featured')
                                                    ->label('Öne Çıkan')
                                                    ->inline(false)
                                                    ->default(false),
                                                Toggle::make('is_weekly')
                                                    ->label('Haftanın Ürünü')
                                                    ->inline(false)
                                                    ->default(false),
                                                Toggle::make('is_best_seller')
                                                    ->label('Çok Satan')
                                                    ->inline(false)
                                                    ->default(false),
                                                Toggle::make('is_new')
                                                    ->label('Yeni Ürün')
                                                    ->inline(false)
                                                    ->default(true),
                                            ]),
                                    ]),

                                Section::make('Teslimat Kuralları')
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Toggle::make('same_day_delivery')
                                                    ->label('Aynı Gün Teslimat Yapılabilir')
                                                    ->inline(false)
                                                    ->default(true),
                                                Toggle::make('free_delivery')
                                                    ->label('Ücretsiz Kurye Teslimatı')
                                                    ->inline(false)
                                                    ->default(false),
                                            ]),
                                    ]),
                            ]),

                        Tab::make('SEO')
                            ->icon('heroicon-o-globe-alt')
                            ->schema([
                                TextInput::make('meta_title')
                                    ->label('SEO Başlığı')
                                    ->maxLength(60)
                                    ->helperText('Boş bırakılırsa ürün adı kullanılır. En fazla 60 karakter önerilir.'),
                                Textarea::make('meta_description')
                                    ->label('SEO Açıklaması')
                                    ->maxLength(160)
                                    ->rows(3)
                                    ->helperText('Boş bırakılırsa kısa açıklama kullanılır. En fazla 160 karakter önerilir.'),
                            ]),
                    ]),
            ]);
    }
}
