<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images.image_path')
                    ->label('Görsel')
                    ->limit(1)
                    ->square(),
                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('discount_price')
                    ->label('İndirimli Fiyat')
                    ->money('TRY')
                    ->sortable()
                    ->placeholder('Yok'),
                ToggleColumn::make('stock_status')
                    ->label('Stok'),
                ToggleColumn::make('is_featured')
                    ->label('Öne Çıkan'),
                ToggleColumn::make('is_weekly')
                    ->label('Haftanın'),
                ToggleColumn::make('same_day_delivery')
                    ->label('Aynı Gün'),
                TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
