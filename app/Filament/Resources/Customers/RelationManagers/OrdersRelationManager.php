<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\Filament\Resources\Orders\OrderResource;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Sipariş Geçmişi';
    protected static ?string $recordTitleAttribute = 'order_number';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient_name')
                    ->label('Alıcı Adı')
                    ->searchable(),
                TextColumn::make('delivery_date')
                    ->label('Teslimat Tarihi')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending_payment' => 'warning',
                        'payment_failed' => 'danger',
                        'paid' => 'success',
                        'preparing' => 'info',
                        'assigned_to_courier' => 'gray',
                        'on_delivery' => 'info',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        'refunded' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending_payment' => 'Ödeme Bekliyor',
                        'payment_failed' => 'Ödeme Başarısız',
                        'paid' => 'Ödendi / Yeni',
                        'preparing' => 'Hazırlanıyor',
                        'assigned_to_courier' => 'Kuryede',
                        'on_delivery' => 'Dağıtımda',
                        'delivered' => 'Teslim Edildi',
                        'cancelled' => 'İptal Edildi',
                        'refunded' => 'İade Edildi',
                        default => $state,
                    }),
                TextColumn::make('total')
                    ->label('Genel Toplam')
                    ->money('TRY')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Sipariş Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('view')
                    ->label('Görüntüle/Düzenle')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => OrderResource::getUrl('edit', ['record' => $record])),
            ])
            ->bulkActions([
                //
            ]);
    }
}
