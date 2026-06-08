<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';
    protected static ?string $title = 'Teslimat Adresleri';
    protected static ?string $recordTitleAttribute = 'title';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Adres Bilgileri')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('title')
                                    ->label('Adres Başlığı (Örn: Ev, İş)')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label('Alıcı Telefon')
                                    ->tel()
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('first_name')
                                    ->label('Alıcı Adı')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('last_name')
                                    ->label('Alıcı Soyadı')
                                    ->required()
                                    ->maxLength(50),
                                TextInput::make('city')
                                    ->label('Şehir')
                                    ->default('Diyarbakır')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('district')
                                    ->label('İlçe')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('neighborhood')
                                    ->label('Mahalle')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('company')
                                    ->label('Firma Adı (İsteğe bağlı)')
                                    ->maxLength(255),
                                Textarea::make('address_line')
                                    ->label('Açık Adres')
                                    ->required()
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Adres Başlığı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Alıcı Adı')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Alıcı Soyadı')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Telefon'),
                TextColumn::make('district')
                    ->label('İlçe')
                    ->sortable(),
                TextColumn::make('neighborhood')
                    ->label('Mahalle')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Şehir'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
