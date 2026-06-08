<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menü Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('name')
                                    ->label('Menü Adı')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => 
                                        $operation === 'create' ? $set('slug', Str::slug($state)) : null
                                    ),
                                TextInput::make('slug')
                                    ->label('Menü Kodu (Slug)')
                                    ->required()
                                    ->unique('menus', 'slug', ignoreRecord: true),
                            ]),
                    ]),

                Section::make('Menü Elemanları')
                    ->schema([
                        Repeater::make('menuItems')
                            ->relationship('menuItems')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Başlık')
                                    ->required(),
                                TextInput::make('url')
                                    ->label('Bağlantı (URL)')
                                    ->required(),
                                Select::make('target')
                                    ->label('Hedef')
                                    ->options([
                                        '_self' => 'Aynı Sayfa',
                                        '_blank' => 'Yeni Sekme',
                                    ])
                                    ->default('_self')
                                    ->required(),
                                TextInput::make('order')
                                    ->label('Sıralama')
                                    ->numeric()
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columns(5)
                            ->columnSpanFull()
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Yeni Eleman'),
                    ]),
            ]);
    }
}
