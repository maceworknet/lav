<?php

namespace App\Filament\Resources\DeliveryFeeCampaigns\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryFeeCampaignForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kampanya Genel Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Kampanya Adı')
                                    ->required()
                                    ->placeholder('Örn: 5000 TL Üzeri Ücretsiz Kurye'),
                                Select::make('type')
                                    ->label('Kampanya Tipi')
                                    ->options([
                                        'free_delivery' => 'Ücretsiz Teslimat',
                                        'fixed_fee' => 'Sabit Teslimat Ücreti',
                                        'discount' => 'Teslimat Ücreti İndirimi',
                                    ])
                                    ->required()
                                    ->live(),
                                Select::make('delivery_zone_id')
                                    ->label('Geçerli İlçe (Opsiyonel - Boşsa hepsi)')
                                    ->relationship('zone', 'district')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->live(),
                                Select::make('delivery_neighborhood_id')
                                    ->label('Geçerli Mahalle (Opsiyonel - Boşsa hepsi)')
                                    ->relationship('neighborhood', 'name', fn($query, $get) => 
                                        $get('delivery_zone_id') 
                                            ? $query->where('delivery_zone_id', $get('delivery_zone_id')) 
                                            : $query
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),
                    ]),

                Section::make('Kampanya Koşulları ve Değerleri')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('min_cart_total')
                                    ->label('Minimum Sepet Tutarı (TL)')
                                    ->numeric()
                                    ->prefix('₺')
                                    ->default(0.00)
                                    ->required(),
                                
                                // Conditional fields based on type
                                Select::make('discount_type')
                                    ->label('İndirim Tipi')
                                    ->options([
                                        'fixed' => 'Sabit Tutar İndirimi',
                                        'percent' => 'Yüzdesel İndirim',
                                    ])
                                    ->visible(fn ($get) => $get('type') === 'discount')
                                    ->required(fn ($get) => $get('type') === 'discount'),
                                
                                TextInput::make('discount_value')
                                    ->label('İndirim Değeri')
                                    ->numeric()
                                    ->visible(fn ($get) => $get('type') === 'discount')
                                    ->required(fn ($get) => $get('type') === 'discount'),

                                TextInput::make('fixed_delivery_fee')
                                    ->label('Sabit Kurye Ücreti (TL)')
                                    ->numeric()
                                    ->prefix('₺')
                                    ->visible(fn ($get) => $get('type') === 'fixed_fee')
                                    ->required(fn ($get) => $get('type') === 'fixed_fee'),
                            ]),
                    ]),

                Section::make('Süreç ve İletişim Ayarları')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('starts_at')
                                    ->label('Başlangıç Tarihi')
                                    ->nullable(),
                                DateTimePicker::make('ends_at')
                                    ->label('Bitiş Tarihi')
                                    ->nullable(),
                                TextInput::make('customer_message')
                                    ->label('Müşteriye Gösterilecek Mesaj (Opsiyonel)')
                                    ->placeholder('Örn: Ücretsiz teslimat için sepetinizi 5.000 TL\'ye tamamlayın!')
                                    ->columnSpanFull(),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
