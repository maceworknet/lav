<?php

namespace App\Filament\Resources\Coupons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CouponForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kupon Genel Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('code')
                                    ->label('Kupon Kodu')
                                    ->required()
                                    ->unique('coupons', 'code', ignoreRecord: true)
                                    ->placeholder('Örn: BAHAR15')
                                    ->dehydrateStateUsing(fn ($state) => strtoupper($state)),
                                Select::make('type')
                                    ->label('Kupon Tipi')
                                    ->options([
                                        'fixed' => 'Sabit Tutar İndirimi (TL)',
                                        'percentage' => 'Yüzde Oran İndirimi (%)',
                                    ])
                                    ->required(),
                                TextInput::make('value')
                                    ->label('İndirim Değeri')
                                    ->numeric()
                                    ->required()
                                    ->helperText('Sabit tutar için TL, yüzde oran için % değeri girin.'),
                                TextInput::make('min_order_amount')
                                    ->label('Minimum Sipariş Tutarı')
                                    ->numeric()
                                    ->prefix('₺')
                                    ->default(0.00)
                                    ->required(),
                            ]),
                    ]),

                Section::make('Limitler ve Koşullar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Başlangıç Tarihi')
                                    ->nullable(),
                                DatePicker::make('end_date')
                                    ->label('Bitiş Tarihi')
                                    ->nullable(),
                                TextInput::make('usage_limit')
                                    ->label('Toplam Kullanım Limiti')
                                    ->numeric()
                                    ->placeholder('Sınırsız için boş bırakın')
                                    ->nullable(),
                                TextInput::make('used_count')
                                    ->label('Kullanım Adedi')
                                    ->numeric()
                                    ->disabled()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Aktif mi')
                                    ->inline(false)
                                    ->default(true)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }
}
