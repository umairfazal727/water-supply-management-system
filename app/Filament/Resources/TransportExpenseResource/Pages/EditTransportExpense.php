<?php

namespace App\Filament\Resources\TransportExpenseResource\Pages;

use App\Filament\Resources\TransportExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransportExpense extends EditRecord
{
    protected static string $resource = TransportExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['expense_type'] = 'transport';
        return $data;
    }
}

