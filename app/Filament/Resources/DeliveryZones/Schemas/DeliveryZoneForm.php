<?php

namespace App\Filament\Resources\DeliveryZones\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliveryZoneForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teslimat Bölgesi (İlçe)')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('city')
                                    ->label('İl')
                                    ->required()
                                    ->default('Diyarbakır'),
                                TextInput::make('district')
                                    ->label('İlçe')
                                    ->required()
                                    ->placeholder('Örn: Kayapınar'),
                                TextInput::make('base_delivery_fee')
                                    ->label('İlçe Taban Teslimat Ücreti')
                                    ->numeric()
                                    ->prefix('₺')
                                    ->default(0.00)
                                    ->helperText('Mahalleye özel ücret tanımlanmadıysa bu ücret kullanılır.'),
                                TextInput::make('sort_order')
                                    ->label('Sıralama')
                                    ->numeric()
                                    ->default(0)
                                    ->helperText('Listelerde küçükten büyüğe sıralanır.'),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->inline(false)
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Mahalleler ve Teslimat Ücretleri')
                    ->description('Mahalle ücreti, ilçe taban ücretini ezer. Ücretsiz limit dolduğunda o mahalleye teslimat ücretsiz olur.')
                    ->schema([
                        Repeater::make('neighborhoods')
                            ->relationship('neighborhoods')
                            ->hiddenLabel()
                            ->addActionLabel('Mahalle Ekle')
                            ->collapsible()
                            ->cloneable()
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Mahalle Adı')
                                            ->required()
                                            ->placeholder('Örn: Diclekent Mah.'),
                                        TextInput::make('delivery_fee')
                                            ->label('Teslimat Ücreti')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->required(),
                                        TextInput::make('min_order_amount')
                                            ->label('Min. Sipariş')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->default(300.00)
                                            ->required(),
                                        TextInput::make('free_delivery_threshold')
                                            ->label('Ücretsiz Limit')
                                            ->numeric()
                                            ->prefix('₺')
                                            ->nullable()
                                            ->placeholder('Boş = yok'),
                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->inline(false)
                                            ->default(true),
                                    ]),
                            ])
                            ->itemLabel(fn (array $state): ?string =>
                                ($state['name'] ?? 'Yeni Mahalle')
                                . (isset($state['delivery_fee']) && $state['delivery_fee'] !== null && $state['delivery_fee'] !== ''
                                    ? ' — ₺' . $state['delivery_fee']
                                    : '')
                            )
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
