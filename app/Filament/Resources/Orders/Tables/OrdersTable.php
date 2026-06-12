<?php

namespace App\Filament\Resources\Orders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\Order;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient_name')
                    ->label('Alıcı Adı')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('recipient_phone')
                    ->label('Alıcı Telefon')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('delivery_date')
                    ->label('Teslimat Tarihi')
                    ->date('d.m.Y')
                    ->sortable(),
                TextColumn::make('delivery_slot')
                    ->label('Saat Aralığı'),
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
            ->recordActions([
                EditAction::make(),
                Action::make('updateStatus')
                    ->label('Durum Güncelle')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->form([
                        Select::make('status')
                            ->label('Sipariş Durumu')
                            ->options([
                                'pending_payment' => 'Ödeme Bekliyor',
                                'payment_failed' => 'Ödeme Başarısız',
                                'paid' => 'Ödendi / Yeni',
                                'preparing' => 'Hazırlanıyor',
                                'assigned_to_courier' => 'Kuryede',
                                'on_delivery' => 'Dağıtımda',
                                'delivered' => 'Teslim Edildi',
                                'cancelled' => 'İptal Edildi',
                                'refunded' => 'İade Edildi',
                            ])
                            ->required(),
                    ])
                    ->action(function (Order $record, array $data): void {
                        // Merkezi servis: durum geçmişi + müşteri push bildirimi tetiklenir.
                        app(\App\Services\OrderStatusService::class)->updateStatus(
                            $record,
                            $data['status'],
                            auth()->user()?->name ?? 'Admin'
                        );

                        \Filament\Notifications\Notification::make()
                            ->title('Sipariş durumu başarıyla güncellendi.')
                            ->success()
                            ->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
