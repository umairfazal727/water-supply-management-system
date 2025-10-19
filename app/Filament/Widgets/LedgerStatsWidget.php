<?php

namespace App\Filament\Widgets;

use App\Models\Ledger;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LedgerStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected function getStats(): array
    {
        $currency_symbol = config('settings.currency_symbol', 'AED');
        
        // Calculate total debit (money received from customers)
        $totalDebit = Ledger::sum('debit_amount');
        
        // Calculate total credit (money paid to/owed by customers)
        $totalCredit = Ledger::sum('credit_amount');
        
        // Net balance
        $netBalance = $totalDebit - $totalCredit;
        
        return [
            Stat::make('Total Customer Debit', $currency_symbol . ' ' . number_format($totalDebit, 2))
                ->description('Total amount debited from customers')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 8, 6, 9, 10, 8, 12]),
            
            Stat::make('Total Customer Credit', $currency_symbol . ' ' . number_format($totalCredit, 2))
                ->description('Total amount credited to customers')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger')
                ->chart([3, 4, 5, 3, 6, 4, 5]),
            
            Stat::make('Net Balance', $currency_symbol . ' ' . number_format($netBalance, 2))
                ->description($netBalance >= 0 ? 'Positive balance' : 'Negative balance')
                ->descriptionIcon($netBalance >= 0 ? 'heroicon-m-arrow-up' : 'heroicon-m-arrow-down')
                ->color($netBalance >= 0 ? 'success' : 'warning')
                ->chart([2, 4, 6, 5, 8, 7, 9]),
        ];
    }
}

