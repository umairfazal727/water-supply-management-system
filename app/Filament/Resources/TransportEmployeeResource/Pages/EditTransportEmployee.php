<?php

namespace App\Filament\Resources\TransportEmployeeResource\Pages;

use App\Filament\Resources\TransportEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransportEmployee extends EditRecord
{
    protected static string $resource = TransportEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
