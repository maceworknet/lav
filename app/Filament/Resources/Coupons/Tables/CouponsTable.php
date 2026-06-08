<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kupon Kodu')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tip')
                    ->formatStateUsing(fn ($state) => $state === 'fixed' ? 'Sabit Tutar' : 'Yüzde Oran')
                    ->sortable(),
                TextColumn::make('value')
                    ->label('Değer')
                    ->formatStateUsing(fn ($record, $state) => $record->type === 'fixed' ? '₺' . number_format($state, 2) : '%' . intval($state))
                    ->sortable(),
                TextColumn::make('min_order_amount')
                    ->label('Min. Sepet')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('used_count')
                    ->label('Kullanım')
                    ->formatStateUsing(fn ($record) => $record->used_count . ' / ' . ($record->usage_limit ?? '∞'))
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('end_date')
                    ->label('Bitiş Tarihi')
                    ->date('d.m.Y')
                    ->sortable()
                    ->placeholder('Süresiz'),
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
