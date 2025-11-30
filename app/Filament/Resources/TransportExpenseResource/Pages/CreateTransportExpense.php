<?php

namespace App\Filament\Resources\TransportExpenseResource\Pages;

use App\Filament\Resources\TransportExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTransportExpense extends CreateRecord
{
    protected static string $resource = TransportExpenseResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['expense_type'] = 'transport';
        return $data;
    }
}

