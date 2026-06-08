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
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->default(true),
                            ]),
                    ]),

                Section::make('Mahalleler ve Teslimat Ücretleri')
                    ->schema([
                        Repeater::make('neighborhoods')
                            ->relationship('neighborhoods')
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
                                    ->nullable(),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? 'Yeni Mahalle'),
                    ]),
            ]);
    }
}
