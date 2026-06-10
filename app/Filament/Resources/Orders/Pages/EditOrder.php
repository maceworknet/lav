<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Services\OrderStatusService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * Sipariş durumu form üzerinden değiştirilirse doğrudan model update yerine
     * merkezi OrderStatusService kullanılır (geçmiş kaydı + bildirimler).
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $newStatus = $data['status'] ?? null;
        unset($data['status']);

        $record->update($data);

        if ($newStatus !== null && $newStatus !== $record->status) {
            app(OrderStatusService::class)->updateStatus(
                $record,
                $newStatus,
                auth()->user()?->name ?? 'Admin'
            );
        }

        return $record;
    }
}
