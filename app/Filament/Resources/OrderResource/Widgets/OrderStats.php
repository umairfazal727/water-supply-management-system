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

        // Base query respects active table filters (including Branch filter)
        $baseQuery = $this->getPageTableQuery();

        // Limit to today's orders (order_date is today OR order_date null and created today)
        $todayQuery = (clone $baseQuery)
            ->where(function ($query) {
                $query->whereDate('order_date', today())
                    ->orWhere(function ($sub) {
                        $sub->whereNull('order_date')
                            ->whereDate('created_at', today());
                    });
            });

        $todayOrdersCount = (clone $todayQuery)->count();
        $todayIncomeTotal = (clone $todayQuery)->sum('total_price');
        $todayCashTotal = (clone $todayQuery)->where('payment_type', 'cash')->sum('total_price');
        $todayCreditTotal = (clone $todayQuery)->where('payment_type', 'credit')->sum('total_price');

        return [
            Stat::make("Today's Orders", $todayOrdersCount)
                ->description('Count of orders created today')
                ->color('primary'),

            Stat::make("Today's Income", $currency_symbol . number_format((float) $todayIncomeTotal, 2))
                ->description('Total income today')
                ->color('success'),

            Stat::make('Cash (Today)', $currency_symbol . number_format((float) $todayCashTotal, 2))
                ->description('Cash-based sales today')
                ->color('success'),

            Stat::make('Credit (Today)', $currency_symbol . number_format((float) $todayCreditTotal, 2))
                ->description('Credit-based sales today')
                ->color('warning'),
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
