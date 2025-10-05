<?php

namespace App\Filament\Widgets;

use App\Models\Delivery;
use Filament\Widgets\TableWidget;

class RecentDeliveriesWidget extends TableWidget
{
    protected static ?string $heading = 'Recent Deliveries';
    
    protected int | string | array $columnSpan = 'full';
    
    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Delivery::with(['customer', 'branch'])
            ->latest()
            ->limit(10);
    }
    
    protected function getTableColumns(): array
    {
        return [
            \Filament\Tables\Columns\TextColumn::make('delivery_number')
                ->searchable()
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('customer.first_name')
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('delivery_date')
                ->date()
                ->sortable(),
            \Filament\Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'scheduled' => 'info',
                    'in_progress' => 'warning',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                }),
            \Filament\Tables\Columns\TextColumn::make('total_amount')
                ->money('SAR')
                ->sortable(),
        ];
    }
}
