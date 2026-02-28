<?php

namespace App\Filament\Resources\CustomerLedgerResource\Pages;

use App\Filament\Resources\CustomerLedgerResource;
use App\Models\Ledger;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCustomerLedger extends EditRecord
{
    protected static string $resource = CustomerLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->after(function (Ledger $record) {
                    Ledger::recalculateBalancesForCustomer($record->customer_id);
                }),
        ];
    }

    protected function afterSave(): void
    {
        Ledger::recalculateBalancesForCustomer($this->record->customer_id);
    }
}
