<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
 

class OrderStats extends BaseWidget
{

    use InteractsWithPageTable;

    protected function getTablePage(): string
    {
        return ListOrders::class;
    }
    protected function getStats(): array
    {
        $currency_symbol = config('settings.currency_symbol');

        // Build a base 'today' filter
        $todayBase = Order::query()->where(function ($query) {
            $query->whereDate('order_date', today())
                ->orWhere(function ($sub) {
                    $sub->whereNull('order_date')
                        ->whereDate('created_at', today());
                });
        });

        // Main Branch stats
        $mainBranchQuery = (clone $todayBase)->whereHas('branch', function ($q) {
            $q->where('name', 'Main Branch');
        });
        $mainOrdersCount = (clone $mainBranchQuery)->count();
        $mainIncomeTotal = (clone $mainBranchQuery)->sum('total_price');

        // Branch 1 stats
        $branch1Query = (clone $todayBase)->whereHas('branch', function ($q) {
            $q->where('name', 'Branch 1');
        });
        $branch1OrdersCount = (clone $branch1Query)->count();
        $branch1IncomeTotal = (clone $branch1Query)->sum('total_price');

        return [
            Stat::make('Main Branch - Orders', $mainOrdersCount)
                ->description('Today')
                ->color('primary'),

            Stat::make('Main Branch - Income', $currency_symbol . number_format((float) $mainIncomeTotal, 2))
                ->description('Today')
                ->color('success'),

            Stat::make('Branch 1 - Orders', $branch1OrdersCount)
                ->description('Today')
                ->color('primary'),

            Stat::make('Branch 1 - Income', $currency_symbol . number_format((float) $branch1IncomeTotal, 2))
                ->description('Today')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 4;
    }
    // protected function getStats(): array
    // {
    //     $currency_symbol = config('settings.currency_symbol');

    //     return [
    //         Stat::make('Total orders', $this->getPageTableQuery()->count())
    //                 ->description("Total orders")
    //                 ->descriptionIcon('heroicon-o-inbox-stack', IconPosition::Before)
    //                 ->chart([1,5,10,50])
    //                 ->color('success'),
    //         Stat::make('Income', $currency_symbol.$this->getPageTableQuery()->sum('total_price'))
    //                 ->description("Total income")
    //                 ->descriptionIcon('heroicon-o-banknotes', IconPosition::Before)
    //                 ->chart([1,5,30, 50])
    //                 ->color('success'),
    //     ];
    // }
}
