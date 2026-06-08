<?php

namespace App\Filament\Resources\DeliverySlots\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DeliverySlotForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Teslimat Saat Aralığı Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Aralık Adı')
                                    ->required()
                                    ->placeholder('Örn: Sabah (09:00 - 12:00)')
                                    ->columnSpanFull(),
                                TimePicker::make('start_time')
                                    ->label('Başlangıç Saati')
                                    ->required(),
                                TimePicker::make('end_time')
                                    ->label('Bitiş Saati')
                                    ->required(),
                                TimePicker::make('cutoff_time')
                                    ->label('Cutoff Saati (Aynı gün bu saatten sonra seçilemez)')
                                    ->placeholder('Örn: 08:30:00')
                                    ->nullable(),
                                TextInput::make('capacity')
                                    ->label('Aralık Başına Maksimum Sipariş Kapasitesi')
                                    ->numeric()
                                    ->default(15)
                                    ->required(),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
