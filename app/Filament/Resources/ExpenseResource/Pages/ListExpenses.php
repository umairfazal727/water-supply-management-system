<?php

namespace App\Filament\Resources\ExpenseResource\Pages;

use App\Filament\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListExpenses extends ListRecords
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('viewReports')
                ->label('View & Export Expenses')
                ->icon('heroicon-o-chart-bar')
                ->color('success')
                ->url(fn () => \App\Filament\Pages\ExpenseView::getUrl())
                ->openUrlInNewTab(false),
        ];
    }
}
