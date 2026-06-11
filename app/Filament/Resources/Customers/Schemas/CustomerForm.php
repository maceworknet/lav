<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Müşteri Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('first_name')
                                    ->label('Ad')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('last_name')
                                    ->label('Soyad')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('email')
                                    ->label('E-posta')
                                    ->email()
                                    ->required()
                                    ->maxLength(255)
                                    ->unique('customers', 'email', ignoreRecord: true),
                                TextInput::make('phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('password')
                                    ->label('Şifre')
                                    ->password()
                                    ->revealable()
                                    ->dehydrateStateUsing(fn ($state) => bcrypt($state))
                                    ->dehydrated(fn ($state) => filled($state))
                                    ->required(fn (string $context): bool => $context === 'create')
                                    ->maxLength(255)
                                    ->helperText('Düzenlemede boş bırakılırsa şifre değişmez.'),
                                Toggle::make('is_guest')
                                    ->label('Misafir Kullanıcı')
                                    ->inline(false)
                                    ->default(false),
                            ]),
                    ]),
            ]);
    }
}
