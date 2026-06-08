<?php

namespace App\Filament\Resources\DeliverySlots\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DeliverySlotsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Saat Aralığı Adı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Başlangıç')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Bitiş')
                    ->sortable(),
                TextColumn::make('cutoff_time')
                    ->label('Cutoff')
                    ->sortable()
                    ->placeholder('Yok'),
                TextColumn::make('capacity')
                    ->label('Kapasite')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
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
