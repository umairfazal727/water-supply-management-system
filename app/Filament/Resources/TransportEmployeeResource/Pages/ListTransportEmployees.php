<?php

namespace App\Filament\Resources\TransportEmployeeResource\Pages;

use App\Filament\Resources\TransportEmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportEmployees extends ListRecords
{
    protected static string $resource = TransportEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
