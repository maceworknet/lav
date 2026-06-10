<?php

namespace App\Filament\Resources\DeliveryFeeCampaigns;

use App\Filament\Resources\DeliveryFeeCampaigns\Pages\CreateDeliveryFeeCampaign;
use App\Filament\Resources\DeliveryFeeCampaigns\Pages\EditDeliveryFeeCampaign;
use App\Filament\Resources\DeliveryFeeCampaigns\Pages\ListDeliveryFeeCampaigns;
use App\Filament\Resources\DeliveryFeeCampaigns\Schemas\DeliveryFeeCampaignForm;
use App\Filament\Resources\DeliveryFeeCampaigns\Tables\DeliveryFeeCampaignsTable;
use App\Models\DeliveryFeeCampaign;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class DeliveryFeeCampaignResource extends Resource
{
    protected static ?string $model = DeliveryFeeCampaign::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-gift';
    protected static ?string $modelLabel = 'Teslimat Kampanyası';
    protected static ?string $pluralModelLabel = 'Teslimat Kampanyaları';
    protected static ?string $navigationLabel = 'Kampanyalar';
    protected static \UnitEnum|string|null $navigationGroup = 'Teslimat';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DeliveryFeeCampaignForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryFeeCampaignsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryFeeCampaigns::route('/'),
            'create' => CreateDeliveryFeeCampaign::route('/create'),
            'edit' => EditDeliveryFeeCampaign::route('/{record}/edit'),
        ];
    }
}
