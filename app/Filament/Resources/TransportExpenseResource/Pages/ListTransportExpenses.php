<?php

namespace App\Filament\Resources\TransportExpenseResource\Pages;

use App\Filament\Resources\TransportExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransportExpenses extends ListRecords
{
    protected static string $resource = TransportExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

