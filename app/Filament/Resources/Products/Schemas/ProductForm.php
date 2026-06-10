<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Schemas\Components\Section;
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
                Section::make('Ürün Temel Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ürün Adı')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label('Kalıcı Bağlantı (Slug)')
                                    ->required()
                                    ->unique('products', 'slug', ignoreRecord: true),
                                TextInput::make('sku')
                                    ->label('Stok Kodu (SKU)')
                                    ->required()
                                    ->unique('products', 'sku', ignoreRecord: true)
                                    ->default(fn () => 'LAV-' . strtoupper(Str::random(6))),
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
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Detaylar ve Açıklamalar')
                    ->schema([
                        Textarea::make('short_description')
                            ->label('Kısa Açıklama')
                            ->rows(2)
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

                Section::make('Ürün Görselleri')
                    ->schema([
                        Repeater::make('images')
                            ->relationship('images')
                            ->schema([
                                \App\Forms\Components\MediaPicker::make('image_path')
                                    ->label('Görsel Seç / Yükle')
                                    ->required(),
                                Toggle::make('is_main')
                                    ->label('Ana Görsel Yap')
                                    ->default(false),
                                TextInput::make('order')
                                    ->label('Sıralama')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['is_main'] ?? false ? 'Ana Görsel' : 'Ek Görsel'),
                    ]),

                Section::make('Ürün Seçenekleri (Boyutlar ve Ekstralar)')
                    ->schema([
                        Repeater::make('options')
                            ->relationship('options')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Seçenek Grubu Adı')
                                    ->required()
                                    ->placeholder('Örn: Boyut Seçimi, Ekstra Hediye'),
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
                                    ->default(false),
                                
                                Repeater::make('values')
                                    ->relationship('values')
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('Seçenek Değeri')
                                            ->required()
                                            ->placeholder('Örn: Büyük Boy, Ayıcık Ekle'),
                                        TextInput::make('price_modifier')
                                            ->label('Ek Fiyat Farkı (TL)')
                                            ->numeric()
                                            ->default(0.00)
                                            ->prefix('₺')
                                            ->required(),
                                        TextInput::make('order')
                                            ->label('Sıralama')
                                            ->numeric()
                                            ->default(0),
                                    ])
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->itemLabel(fn (array $state): ?string => $state['label'] ?? 'Yeni Değer'),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Yeni Seçenek'),
                    ]),

                Section::make('Ekstra Hediye İlişkileri')
                    ->schema([
                        Select::make('extraGifts')
                            ->label('Bu Ürünle Beraber Sunulacak Ekstra Hediyeler')
                            ->relationship('extraGifts', 'name', modifyQueryUsing: fn ($query) => $query->whereHas('categories', fn ($q) => $q->where('slug', 'ekstra-hediyeler')))
                            ->multiple()
                            ->preload(),
                    ]),

                Section::make('Stok, Etiketler ve Teslimat Kuralları')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Toggle::make('stock_status')
                                    ->label('Stokta Var (Aktif)')
                                    ->default(true),
                                TextInput::make('stock')
                                    ->label('Stok Adedi')
                                    ->numeric()
                                    ->nullable(),
                                Toggle::make('supports_card_note')
                                    ->label('Kart Notu Alınabilir')
                                    ->default(true),
                                
                                Toggle::make('is_featured')
                                    ->label('Öne Çıkan Ürün')
                                    ->default(false),
                                Toggle::make('is_weekly')
                                    ->label('Haftanın Ürünü')
                                    ->default(false),
                                Toggle::make('is_best_seller')
                                    ->label('Çok Satan Ürün')
                                    ->default(false),
                                
                                Toggle::make('is_new')
                                    ->label('Yeni Ürün')
                                    ->default(true),
                                Toggle::make('same_day_delivery')
                                    ->label('Aynı Gün Teslimat Yapılabilir')
                                    ->default(true),
                                Toggle::make('free_delivery')
                                    ->label('Ücretsiz Kurye Teslimatı')
                                    ->default(false),
                            ]),
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
