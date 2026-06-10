<?php

namespace App\Filament\Resources\DeliveryFeeCampaigns\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class DeliveryFeeCampaignsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Kampanya Adı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Tipi')
                    ->options([
                        'free_delivery' => 'Ücretsiz Teslimat',
                        'fixed_fee' => 'Sabit Ücret',
                        'discount' => 'İndirim',
                    ])
                    ->sortable(),
                TextColumn::make('min_cart_total')
                    ->label('Min. Sepet Tutarı')
                    ->money('try')
                    ->sortable(),
                TextColumn::make('zone.district')
                    ->label('İlçe')
                    ->placeholder('Tümü')
                    ->sortable(),
                TextColumn::make('neighborhood.name')
                    ->label('Mahalle')
                    ->placeholder('Tümü')
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Aktif'),
                TextColumn::make('starts_at')
                    ->label('Başlangıç')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Bitiş')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
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
