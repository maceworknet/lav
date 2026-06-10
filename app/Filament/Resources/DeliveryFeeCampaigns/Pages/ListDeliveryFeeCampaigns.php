<?php

namespace App\Filament\Resources\DeliveryFeeCampaigns\Pages;

use App\Filament\Resources\DeliveryFeeCampaigns\DeliveryFeeCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryFeeCampaigns extends ListRecords
{
    protected static string $resource = DeliveryFeeCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
