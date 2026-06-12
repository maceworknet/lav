<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $heading = 'Son Siparişler';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()->latest()->limit(8)
            )
            ->columns([
                TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->url(fn (Order $record): string => "/admin/orders/{$record->id}/edit")
                    ->weight('bold'),
                TextColumn::make('recipient_name')
                    ->label('Alıcı'),
                TextColumn::make('delivery_date')
                    ->label('Teslimat')
                    ->date('d.m.Y'),
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
                    ->label('Toplam')
                    ->money('TRY'),
                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->since(),
            ])
            ->paginated(false);
    }
}
