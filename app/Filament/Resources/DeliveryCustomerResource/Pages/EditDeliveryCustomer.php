<?php

namespace App\Filament\Resources\DeliveryCustomerResource\Pages;

use App\Filament\Resources\DeliveryCustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryCustomer extends EditRecord
{
    protected static string $resource = DeliveryCustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
